<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use App\Models\User;
use App\Services\ImageOptimizer;
use App\Services\PricingCalculator;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingAdminController extends Controller
{
    public function dashboard(): View
    {
        $today = Carbon::today();

        $pendingCount = Booking::where('status', 'pending')->count();
        $approvedTodayCount = Booking::where('status', 'approved')
            ->whereDate('start_time', $today)
            ->count();
        $activeRentalsCount = Booking::whereIn('status', ['delivering', 'active', 'returning'])->count();
        $totalMotorsCount = Motorcycle::count();
        $availableMotorsCount = Motorcycle::where('status', 'available')->count();

        $recentBookings = Booking::with(['category', 'motorcycle', 'deliveryStaff'])
            ->latest()
            ->take(8)
            ->get();

        $upcomingDeliveries = Booking::with(['category', 'motorcycle', 'deliveryStaff', 'deliveryZone'])
            ->whereIn('status', ['approved', 'delivering'])
            ->orderBy('start_time')
            ->take(6)
            ->get();

        $activeRentals = Booking::with(['category', 'motorcycle', 'deliveryStaff', 'returnZone'])
            ->where('status', 'active')
            ->orderBy('end_time')
            ->take(6)
            ->get();

        return view('admin.dashboard', compact(
            'pendingCount',
            'approvedTodayCount',
            'activeRentalsCount',
            'totalMotorsCount',
            'availableMotorsCount',
            'recentBookings',
            'upcomingDeliveries',
            'activeRentals'
        ));
    }

    public function index(Request $request): View
    {
        $status = $request->input('status');
        $search = $request->input('q');

        $query = Booking::with(['category', 'motorcycle', 'deliveryStaff', 'deliveryZone']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($search) {
            $cleanSearch = str_replace('-', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('booking_code', 'like', "%{$search}%")
                    ->orWhereRaw("REPLACE(booking_code, '-', '') LIKE ?", ["%{$cleanSearch}%"])
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        $statusCounts = Booking::toBase()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'all' => (int) $statusCounts->sum(),
            'pending' => (int) ($statusCounts['pending'] ?? 0),
            'approved' => (int) ($statusCounts['approved'] ?? 0),
            'delivering' => (int) ($statusCounts['delivering'] ?? 0),
            'active' => (int) ($statusCounts['active'] ?? 0),
            'completed' => (int) ($statusCounts['completed'] ?? 0),
            'rejected' => (int) ($statusCounts['rejected'] ?? 0),
        ];

        return view('admin.bookings.index', compact('bookings', 'status', 'counts'));
    }

    public function show(int $id): View
    {
        $booking = Booking::with([
            'category.motorcycles',
            'motorcycle',
            'deliveryZone',
            'returnZone',
            'deliveryStaff',
            'returnStaff',
            'handoverInspection.staff',
            'returnInspection.staff',
        ])->findOrFail($id);

        // Cari unit motor kategori ini yang statusnya bukan maintenance
        // Dan tandai apakah unit tersebut sedang konflik jadwal sewa lain atau tidak
        $motorcycles = Motorcycle::where('category_id', $booking->category_id)
            ->where('status', '!=', 'maintenance')
            ->get()
            ->map(function ($m) use ($booking) {
                $conflict = $m->getConflictingBooking($booking->start_time, $booking->end_time, $booking->id);
                $m->has_conflict = $conflict !== null;
                $m->conflict_booking = $conflict;

                return $m;
            });

        $availableUnitsCount = $motorcycles->where('has_conflict', false)->count();

        $categories = MotorcycleCategory::where('is_active', true)->get();

        $allMotorcycles = Motorcycle::with('category')
            ->where('status', '!=', 'maintenance')
            ->get();

        $categoriesJson = $categories->map(fn ($c) => [
            'id' => $c->id,
            'name' => $c->name,
            'daily_price' => (float) $c->daily_price,
            'hourly_overtime_price' => (float) $c->hourly_overtime_price,
        ]);

        $staffList = User::where('role', 'staff')
            ->where('is_active', true)
            ->get();

        return view('admin.bookings.show', compact('booking', 'motorcycles', 'availableUnitsCount', 'categories', 'allMotorcycles', 'categoriesJson', 'staffList'));
    }

    public function approve(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'motorcycle_id' => 'required|exists:motorcycles,id',
            'down_payment_amount' => 'nullable|numeric|min:0|max:'.$booking->total_amount,
            'dp_correction_note' => 'nullable|string|max:500',
            'delivery_staff_id' => 'nullable|exists:users,id',
            'return_staff_id' => 'nullable|exists:users,id',
            'payment_status' => 'nullable|in:dp_approved,paid_full',
        ]);

        $motorcycle = Motorcycle::findOrFail($validated['motorcycle_id']);

        // Validasi Pencegahan Bentrok Unit Motor
        $conflictBooking = $motorcycle->getConflictingBooking($booking->start_time, $booking->end_time, $booking->id);
        if ($conflictBooking) {
            return back()->withInput()->with('error', "Gagal menyetujui: Unit {$motorcycle->plate_number} sudah memiliki jadwal sewa aktif ({$conflictBooking->booking_code}) pada rentang tanggal/jam tersebut ({$conflictBooking->start_time->format('d M H:i')} - {$conflictBooking->end_time->format('d M H:i')}). Silakan pilih unit lain atau tolak reservasi ini.");
        }

        // Pintu 1: Konfirmasi pembayaran DP oleh admin
        $downPayment = array_key_exists('down_payment_amount', $validated) && $validated['down_payment_amount'] !== null
            ? (float) $validated['down_payment_amount']
            : (float) $booking->down_payment_amount;

        $notes = $booking->notes;
        if (abs($downPayment - (float) $booking->down_payment_amount) > 0.01) {
            $logText = '[Koreksi DP '.Carbon::now()->format('d/m/Y H:i').'] Admin menyesuaikan nominal DP masuk dari Rp '.number_format($booking->down_payment_amount, 0, ',', '.').' menjadi Rp '.number_format($downPayment, 0, ',', '.').'.';
            if (! empty($validated['dp_correction_note'])) {
                $logText .= ' Catatan: '.$validated['dp_correction_note'];
            }
            $notes = $notes ? $logText."\n".$notes : $logText;
        }

        $paymentStatus = $validated['payment_status'] ?? 'dp_approved';
        $remainingAmount = $paymentStatus === 'paid_full' ? 0 : max(0, (float) $booking->total_amount - $downPayment);

        $booking->update([
            'motorcycle_id' => $motorcycle->id,
            'down_payment_amount' => $downPayment,
            'remaining_amount' => $remainingAmount,
            'delivery_staff_id' => $validated['delivery_staff_id'] ?? null,
            'return_staff_id' => $validated['return_staff_id'] ?? null,
            'payment_status' => $paymentStatus,
            'remaining_amount' => $remainingAmount,
            'status' => 'approved',
            'rejection_reason' => null,
            'notes' => $notes,
        ]);

        $staffNotice = ! empty($validated['delivery_staff_id'])
            ? 'Unit dan petugas kurir telah dialokasikan.'
            : 'Unit telah dialokasikan dan siap diantar.';

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Pesanan {$booking->booking_code} berhasil disetujui! Unit {$motorcycle->plate_number} siap diantar. Pembayaran DP Rp ".number_format($downPayment, 0, ',', '.').' terkonfirmasi (Sisa tagihan Rp '.number_format($remainingAmount, 0, ',', '.')."). {$staffNotice}");
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        $booking->update([
            'status' => 'rejected',
            'payment_status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('error', "Pesanan {$booking->booking_code} telah ditolak.");
    }

    public function reschedule(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'category_id' => 'required|exists:motorcycle_categories,id',
            'motorcycle_id' => 'nullable|exists:motorcycles,id',
            'target_status' => 'required|in:approved,pending',
            'reschedule_note' => 'nullable|string|max:500',
        ]);

        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);
        $category = MotorcycleCategory::findOrFail($validated['category_id']);

        // Perhitungan durasi dan tarif (termasuk weekend & seasonal peak season)
        $pricing = PricingCalculator::calculateRentalPrice($category, $start, $end);
        $days = $pricing['days'];
        $overtimeHours = $pricing['overtime_hours'];
        $dailyPrice = (float) $category->daily_price;
        $rentalSubtotal = (float) $pricing['rental_subtotal'];
        $overtimeFee = (float) $pricing['overtime_subtotal'];
        $deliveryFee = (float) $booking->delivery_fee;
        $returnFee = (float) $booking->return_fee;
        $totalAmount = $rentalSubtotal + $overtimeFee + $deliveryFee + $returnFee;

        $targetStatus = $validated['target_status'];
        $paymentStatus = $booking->payment_status;

        if ($targetStatus === 'approved') {
            if ($paymentStatus === 'rejected' || $paymentStatus === 'dp_pending_verification') {
                $paymentStatus = 'dp_approved';
            }

            if (! empty($validated['motorcycle_id'])) {
                $motorcycle = Motorcycle::findOrFail($validated['motorcycle_id']);
                $conflictBooking = $motorcycle->getConflictingBooking($start, $end, $booking->id);
                if ($conflictBooking) {
                    return back()->withInput()->with('error', "Gagal revisi jadwal: Unit {$motorcycle->plate_number} sudah memiliki jadwal sewa aktif ({$conflictBooking->booking_code}) pada rentang jadwal baru tersebut ({$conflictBooking->start_time->format('d M H:i')} - {$conflictBooking->end_time->format('d M H:i')}).");
                }
            }
        }

        if ($paymentStatus === 'paid_full') {
            $remainingAmount = max(0, $totalAmount - (float) $booking->total_amount);
            if ($remainingAmount > 0) {
                $paymentStatus = 'dp_approved';
            }
        } else {
            $remainingAmount = max(0, $totalAmount - (float) $booking->down_payment_amount);
        }

        $notes = $booking->notes;
        $logText = '[Revisi Jadwal '.Carbon::now()->format('d/m/Y H:i').'] Jadwal diubah ke '.$start->format('d/m/Y H:i').' s/d '.$end->format('d/m/Y H:i')." ({$days} hari, {$overtimeHours} jam overtime). Kategori: {$category->name}.";
        if (! empty($validated['reschedule_note'])) {
            $logText .= ' Catatan: '.$validated['reschedule_note'];
        }
        $notes = $notes ? $logText."\n".$notes : $logText;

        $booking->update([
            'start_time' => $start,
            'end_time' => $end,
            'category_id' => $category->id,
            'motorcycle_id' => $validated['motorcycle_id'] ?? null,
            'daily_price' => $dailyPrice,
            'total_days' => $days,
            'overtime_hours' => $overtimeHours,
            'rental_subtotal' => $rentalSubtotal,
            'overtime_fee' => $overtimeFee,
            'total_amount' => $totalAmount,
            'remaining_amount' => $remainingAmount,
            'pricing_breakdown' => $pricing['daily_breakdown'],
            'status' => $targetStatus,
            'payment_status' => $paymentStatus,
            'rejection_reason' => null,
            'notes' => $notes,
        ]);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Reservasi {$booking->booking_code} berhasil direvisi dan status diperbarui menjadi {$booking->formatted_status}!");
    }

    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,approved,delivering,active,returning,completed,rejected,cancelled',
            'motorcycle_id' => 'nullable|exists:motorcycles,id',
            'delivery_staff_id' => 'nullable|exists:users,id',
            'return_staff_id' => 'nullable|exists:users,id',
            'payment_status' => 'nullable|in:dp_pending_verification,dp_approved,paid_full,rejected',
            'rejection_reason' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $updateData = [
            'status' => $validated['status'],
        ];

        if ($request->has('notes')) {
            $updateData['notes'] = $validated['notes'];
        }

        if (in_array($validated['status'], ['approved', 'delivering', 'active', 'returning'])) {
            $checkMotorId = $validated['motorcycle_id'] ?? $booking->motorcycle_id;
            if ($checkMotorId) {
                $motorcycle = Motorcycle::find($checkMotorId);
                if ($motorcycle) {
                    $conflictBooking = $motorcycle->getConflictingBooking($booking->start_time, $booking->end_time, $booking->id);
                    if ($conflictBooking) {
                        return back()->withInput()->with('error', "Gagal update status: Unit {$motorcycle->plate_number} sudah memiliki jadwal sewa aktif ({$conflictBooking->booking_code}) pada rentang tanggal/jam tersebut.");
                    }
                }
            }
        }

        if (! empty($validated['motorcycle_id'])) {
            $updateData['motorcycle_id'] = $validated['motorcycle_id'];
        } elseif ($validated['status'] === 'pending' || $validated['status'] === 'rejected' || $validated['status'] === 'cancelled') {
            // Optional: keep or unassign unit
        }

        if (! empty($validated['delivery_staff_id'])) {
            $updateData['delivery_staff_id'] = $validated['delivery_staff_id'];
            $updateData['return_staff_id'] = $validated['return_staff_id'] ?? $validated['delivery_staff_id'];
        }

        // Penyesuaian status pembayaran & sisa tagihan
        $finalPaymentStatus = $validated['payment_status'] ?? null;

        if (empty($finalPaymentStatus)) {
            if ($validated['status'] === 'approved') {
                $finalPaymentStatus = $booking->payment_status === 'paid_full' ? 'paid_full' : 'dp_approved';
            } elseif ($validated['status'] === 'pending') {
                $finalPaymentStatus = 'dp_pending_verification';
            } elseif (in_array($validated['status'], ['rejected', 'cancelled'], true)) {
                $finalPaymentStatus = 'rejected';
            }
        }

        if ($finalPaymentStatus) {
            $updateData['payment_status'] = $finalPaymentStatus;
            if ($finalPaymentStatus === 'paid_full') {
                $updateData['remaining_amount'] = 0;
            } elseif ($finalPaymentStatus === 'dp_approved') {
                $updateData['remaining_amount'] = max(0, $booking->total_amount - $booking->down_payment_amount);
            }
        }

        if ($validated['status'] === 'rejected') {
            if (! empty($validated['rejection_reason'])) {
                $updateData['rejection_reason'] = $validated['rejection_reason'];
            }
        } elseif (in_array($validated['status'], ['approved', 'pending'], true)) {
            $updateData['rejection_reason'] = null;
        }

        $booking->update($updateData);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Status pesanan {$booking->booking_code} berhasil diperbarui menjadi {$booking->formatted_status}.");
    }

    public function updateDownPayment(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'down_payment_amount' => 'required|numeric|min:0|max:'.$booking->total_amount,
            'dp_correction_note' => 'nullable|string|max:500',
        ]);

        $oldDp = (float) $booking->down_payment_amount;
        $newDp = (float) $validated['down_payment_amount'];
        $newRemaining = max(0, (float) $booking->total_amount - $newDp);

        $logText = '[Koreksi DP '.Carbon::now()->format('d/m/Y H:i').'] Admin menyesuaikan nominal DP masuk dari Rp '.number_format($oldDp, 0, ',', '.').' menjadi Rp '.number_format($newDp, 0, ',', '.').'.';
        if (! empty($validated['dp_correction_note'])) {
            $logText .= ' Catatan: '.$validated['dp_correction_note'];
        }
        $notes = $booking->notes ? $logText."\n".$booking->notes : $logText;

        $updateData = [
            'down_payment_amount' => $newDp,
            'remaining_amount' => $newRemaining,
            'notes' => $notes,
        ];

        // Jika status sewa approved dan pembayaran belum diverifikasi
        if ($booking->payment_status === 'dp_pending_verification' && in_array($booking->status, ['approved', 'delivering', 'active'], true)) {
            $updateData['payment_status'] = 'dp_approved';
        }

        $booking->update($updateData);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', 'Nominal DP berhasil disesuaikan menjadi Rp '.number_format($newDp, 0, ',', '.').'. Sisa tagihan untuk petugas lapangan telah diperbarui menjadi Rp '.number_format($newRemaining, 0, ',', '.').'.');
    }

    public function updateDocuments(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $messages = [
            'id_card_photo.image' => 'Berkas KTP harus berupa foto (JPG, PNG, atau WEBP).',
            'id_card_photo.mimes' => 'Format foto KTP harus berupa JPG, JPEG, PNG, atau WEBP.',
            'id_card_photo.max' => 'Ukuran foto KTP tidak boleh lebih dari 5 MB.',
            'sim_c_photo.image' => 'Berkas SIM C harus berupa foto (JPG, PNG, atau WEBP).',
            'sim_c_photo.mimes' => 'Format foto SIM C harus berupa JPG, JPEG, PNG, atau WEBP.',
            'sim_c_photo.max' => 'Ukuran foto SIM C tidak boleh lebih dari 5 MB.',
            'payment_proof.image' => 'Berkas bukti transfer harus berupa foto (JPG, PNG, atau WEBP).',
            'payment_proof.mimes' => 'Format bukti transfer harus berupa JPG, JPEG, PNG, atau WEBP.',
            'payment_proof.max' => 'Ukuran bukti transfer tidak boleh lebih dari 5 MB.',
        ];

        $request->validate([
            'id_card_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sim_c_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'payment_proof' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], $messages);

        $folderMap = [
            'payment_proof' => 'bookings/payments',
            'id_card_photo' => 'bookings/id_cards',
            'sim_c_photo' => 'bookings/sim',
        ];

        $updateData = [];

        foreach ($folderMap as $field => $folder) {
            if ($request->hasFile($field)) {
                $path = ImageOptimizer::compressAndStore($request->file($field), $folder);
                $updateData[$field] = $path;
            }
        }

        if (empty($updateData)) {
            return redirect()->route('admin.bookings.show', $booking->id)
                ->with('error', 'Tidak ada berkas yang dipilih untuk diperbarui.');
        }

        $booking->update($updateData);

        return redirect()->route('admin.bookings.show', $booking->id)
            ->with('success', "Berkas utama reservasi {$booking->booking_code} berhasil diperbarui.");
    }
}
