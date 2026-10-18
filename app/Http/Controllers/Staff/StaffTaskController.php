<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RentalInspection;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StaffTaskController extends Controller
{
    public function index(Request $request): View
    {
        $userId = Auth::id();

        // 1. Tugas Antar (Deliveries: approved, delivering)
        // Diurutkan: Tugas milik staf login di atas, tugas belum diambil di tengah, tugas staf lain di bawah
        $deliveries = Booking::with(['category', 'motorcycle', 'deliveryZone', 'returnZone', 'deliveryStaff', 'returnStaff'])
            ->whereIn('status', ['approved', 'delivering'])
            ->orderByRaw('CASE WHEN delivery_staff_id = ? THEN 0 WHEN delivery_staff_id IS NULL THEN 1 ELSE 2 END', [$userId])
            ->orderBy('start_time', 'asc')
            ->get();

        // 2. Unit Sedang Aktif Disewa (Setelah serah terima)
        $activeRentals = Booking::with(['category', 'motorcycle', 'deliveryZone', 'returnZone', 'deliveryStaff', 'returnStaff', 'handoverInspection'])
            ->where('status', 'active')
            ->orderBy('end_time', 'asc')
            ->get();

        // 3. Tugas Jemput (Returns: active, returning)
        // Diurutkan: Tugas jemput milik staf login di atas, tugas belum diambil di tengah, tugas staf lain di bawah
        $returns = Booking::with(['category', 'motorcycle', 'returnZone', 'deliveryZone', 'deliveryStaff', 'returnStaff'])
            ->whereIn('status', ['active', 'returning'])
            ->orderByRaw('CASE WHEN return_staff_id = ? THEN 0 WHEN return_staff_id IS NULL THEN 1 ELSE 2 END', [$userId])
            ->orderBy('end_time', 'asc')
            ->get();

        // Rekap tugas selesai hari ini
        $today = Carbon::today();
        $completedTodayCount = Booking::where('status', 'completed')
            ->whereDate('updated_at', $today)
            ->count();

        return view('staff.tasks', compact('deliveries', 'activeRentals', 'returns', 'completedTodayCount'));
    }

    public function claimDelivery(int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        if (! in_array($booking->status, ['approved', 'delivering'], true)) {
            return back()->with('error', 'Status pesanan tidak valid untuk diambil pengantarannya.');
        }

        $booking->update([
            'delivery_staff_id' => Auth::id(),
        ]);

        session()->flash('active_tab', 'deliveries');

        return back()->with('success', "Tugas pengantaran unit ({$booking->booking_code}) untuk {$booking->customer_name} berhasil Anda ambil!");
    }

    public function startDelivery(int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        if (! in_array($booking->status, ['approved', 'delivering'], true)) {
            return back()->with('error', 'Status pesanan tidak valid untuk mulai pengantaran.');
        }

        $updateData = ['status' => 'delivering'];
        if (! $booking->delivery_staff_id) {
            $updateData['delivery_staff_id'] = Auth::id();
        }

        $booking->update($updateData);

        session()->flash('active_tab', 'deliveries');

        return back()->with('success', "Status diperbarui: Anda sedang dalam perjalanan mengantar unit ke {$booking->customer_name}!");
    }

    public function claimReturn(int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        if (! in_array($booking->status, ['active', 'returning'], true)) {
            return back()->with('error', 'Status pesanan tidak valid untuk diambil penjemputannya.');
        }

        $booking->update([
            'return_staff_id' => Auth::id(),
        ]);

        session()->flash('active_tab', 'returns');

        return back()->with('success', "Tugas penjemputan unit ({$booking->booking_code}) untuk {$booking->customer_name} berhasil Anda ambil!");
    }

    public function startReturn(int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        if (! in_array($booking->status, ['active', 'returning'], true)) {
            return back()->with('error', 'Status pesanan tidak valid untuk mulai penjemputan.');
        }

        $updateData = ['status' => 'returning'];
        if (! $booking->return_staff_id) {
            $updateData['return_staff_id'] = Auth::id();
        }

        $booking->update($updateData);

        session()->flash('active_tab', 'returns');

        return back()->with('success', "Status diperbarui: Anda sedang dalam perjalanan menjemput unit dari {$booking->customer_name}!");
    }

    public function showHandoverForm(int $id): View
    {
        $booking = Booking::with(['category', 'motorcycle', 'deliveryZone', 'returnZone'])->findOrFail($id);

        $defaultHelmets = $booking->motorcycle?->default_helmets_count ?? 2;
        $defaultRaincoats = $booking->motorcycle?->default_raincoats_count ?? 1;
        $defaultKeyStnk = $booking->motorcycle?->default_key_and_stnk ?? true;

        return view('staff.handover', compact('booking', 'defaultHelmets', 'defaultRaincoats', 'defaultKeyStnk'));
    }

    public function submitHandover(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'fuel_level' => 'required|string',
            'helmets_count' => 'required|integer|min:0|max:4',
            'raincoats_count' => 'required|integer|min:0|max:4',
            'key_and_stnk_included' => 'required|boolean',
            'physical_id_collected' => 'required|boolean',
            'emergency_contact_verified' => 'required|boolean',
            'emergency_verification_note' => 'nullable|string|max:500',
            'remaining_payment_collected' => 'required|boolean',
            'payment_collected_amount' => 'nullable|numeric|min:0',
            'payment_collected_method' => 'nullable|string',
            'damage_notes' => 'nullable|string|max:1000',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections/handover', 'public');
            }
        }

        $inspection = RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => Auth::id(),
            'type' => 'handover',
            'fuel_level' => $validated['fuel_level'],
            'helmets_count' => (int) $validated['helmets_count'],
            'raincoats_count' => (int) $validated['raincoats_count'],
            'key_and_stnk_included' => (bool) $validated['key_and_stnk_included'],
            'physical_id_collected' => (bool) $validated['physical_id_collected'],
            'emergency_contact_verified' => (bool) $validated['emergency_contact_verified'],
            'emergency_verification_note' => $validated['emergency_verification_note'] ?? null,
            'remaining_payment_collected' => (bool) $validated['remaining_payment_collected'],
            'payment_collected_amount' => (float) ($validated['payment_collected_amount'] ?? $booking->remaining_amount),
            'payment_collected_method' => $validated['payment_collected_method'] ?? 'cash',
            'damage_notes' => $validated['damage_notes'] ?? null,
            'photo_paths' => $photoPaths,
            'inspected_at' => Carbon::now(),
        ]);

        // Update status booking dan motor
        $bookingUpdateData = [
            'status' => 'active',
        ];

        if (! $booking->delivery_staff_id) {
            $bookingUpdateData['delivery_staff_id'] = Auth::id();
        }

        if ($validated['remaining_payment_collected']) {
            $bookingUpdateData['payment_status'] = 'paid_full';
            $bookingUpdateData['remaining_amount'] = 0;
        }

        $booking->update($bookingUpdateData);

        if ($booking->motorcycle) {
            $booking->motorcycle->update(['status' => 'rented']);
        }

        session()->flash('active_tab', 'active');

        return redirect()->route('staff.tasks')
            ->with('success', "Serah terima unit ({$booking->booking_code}) untuk {$booking->customer_name} berhasil! Motor telah berpindah ke tab 'Aktif' (Sedang Disewa).");
    }

    public function showReturnForm(int $id): View
    {
        $booking = Booking::with(['category', 'motorcycle', 'returnZone', 'handoverInspection.staff'])->findOrFail($id);

        $handover = $booking->handoverInspection;
        $expectedHelmets = $handover ? $handover->helmets_count : ($booking->motorcycle?->default_helmets_count ?? 2);
        $expectedRaincoats = $handover ? $handover->raincoats_count : ($booking->motorcycle?->default_raincoats_count ?? 1);
        $expectedKeyStnk = $handover ? $handover->key_and_stnk_included : ($booking->motorcycle?->default_key_and_stnk ?? true);
        $expectedFuel = $handover?->fuel_level ?? 'Full';

        return view('staff.return', compact('booking', 'expectedHelmets', 'expectedRaincoats', 'expectedKeyStnk', 'expectedFuel', 'handover'));
    }

    public function submitReturn(Request $request, int $id): RedirectResponse
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'fuel_level' => 'required|string',
            'helmets_count' => 'required|integer|min:0|max:4',
            'raincoats_count' => 'required|integer|min:0|max:4',
            'key_and_stnk_included' => 'required|boolean',
            'physical_id_returned' => 'required|boolean',
            'damage_notes' => 'nullable|string|max:1000',
            'photos.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        $photoPaths = [];
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $photoPaths[] = $photo->store('inspections/return', 'public');
            }
        }

        $inspection = RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => Auth::id(),
            'type' => 'return',
            'fuel_level' => $validated['fuel_level'],
            'helmets_count' => (int) $validated['helmets_count'],
            'raincoats_count' => (int) $validated['raincoats_count'],
            'key_and_stnk_included' => (bool) $validated['key_and_stnk_included'],
            'physical_id_collected' => ! $validated['physical_id_returned'], // jika sudah dikembalikan, bernilai false
            'remaining_payment_collected' => true,
            'payment_collected_amount' => 0,
            'damage_notes' => $validated['damage_notes'] ?? null,
            'photo_paths' => $photoPaths,
            'inspected_at' => Carbon::now(),
        ]);

        // Selesaikan sewa dan kembalikan unit motor menjadi "available"
        $bookingUpdateData = [
            'status' => 'completed',
        ];

        if (! $booking->return_staff_id) {
            $bookingUpdateData['return_staff_id'] = Auth::id();
        }

        $booking->update($bookingUpdateData);

        if ($booking->motorcycle) {
            $booking->motorcycle->update(['status' => 'available']);
        }

        session()->flash('active_tab', 'returns');

        return redirect()->route('staff.tasks')
            ->with('success', "Penjemputan unit untuk {$booking->customer_name} berhasil tuntas! Unit {$booking->motorcycle?->plate_number} otomatis kembali berstatus Tersedia.");
    }
}
