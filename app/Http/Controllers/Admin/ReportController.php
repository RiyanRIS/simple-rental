<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\DeliveryZone;
use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use App\Models\RentalInspection;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->input('status', 'all');
        $categoryId = $request->input('category_id');
        $motorcycleId = $request->input('motorcycle_id');
        $staffId = $request->input('staff_id');
        $activeTab = $request->input('tab', 'revenue');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Query dasar booking berdasar rentang waktu jadwal mulai sewa
        $baseQuery = Booking::with(['category', 'motorcycle', 'deliveryZone', 'returnZone', 'deliveryStaff', 'returnStaff', 'handoverInspection'])
            ->whereBetween('start_time', [$startDateTime, $endDateTime]);

        if ($status !== 'all') {
            $baseQuery->where('status', $status);
        }

        if (! empty($categoryId)) {
            $baseQuery->where('category_id', $categoryId);
        }

        if (! empty($motorcycleId)) {
            $baseQuery->where('motorcycle_id', $motorcycleId);
        }

        if (! empty($staffId)) {
            $baseQuery->where(function ($q) use ($staffId) {
                $q->where('delivery_staff_id', $staffId)
                    ->orWhere('return_staff_id', $staffId);
            });
        }

        $bookings = (clone $baseQuery)->orderBy('start_time', 'desc')->get();

        // 1. Metrik Keuangan (exclude rejected jika filter 'all' untuk hitung omset riil)
        $validBookings = $bookings->filter(fn ($b) => $b->status !== 'rejected');

        $metrics = [
            'total_bookings' => $bookings->count(),
            'valid_bookings_count' => $validBookings->count(),
            'completed_count' => $bookings->where('status', 'completed')->count(),
            'active_count' => $bookings->whereIn('status', ['delivering', 'active', 'returning'])->count(),
            'pending_count' => $bookings->where('status', 'pending')->count(),
            'rejected_count' => $bookings->where('status', 'rejected')->count(),

            'total_gross_revenue' => $validBookings->sum('total_amount'),
            'total_rental_subtotal' => $validBookings->sum('rental_subtotal'),
            'total_overtime_fee' => $validBookings->sum('overtime_fee'),
            'total_delivery_fee' => $validBookings->sum(fn ($b) => $b->delivery_fee + $b->return_fee),
            'total_dp_collected' => $validBookings->sum('down_payment_amount'),

            // Pelunasan yang terkumpul (dari handover inspection atau booking lunas)
            'total_remaining_collected' => $validBookings->sum(function ($b) {
                if ($b->handoverInspection && $b->handoverInspection->payment_collected_amount > 0) {
                    return (float) $b->handoverInspection->payment_collected_amount;
                }
                if ($b->payment_status === 'paid_full' || $b->status === 'completed') {
                    return max(0, (float) $b->total_amount - (float) $b->down_payment_amount);
                }

                return 0;
            }),

            // Piutang yang masih belum lunas pada booking berjalan
            'total_unpaid_remaining' => $validBookings->whereIn('status', ['approved', 'delivering', 'active', 'returning'])
                ->where('payment_status', '!=', 'paid_full')
                ->sum('remaining_amount'),
        ];

        // 2. Utilisasi Armada Unit Motor
        $motorcycles = Motorcycle::with('category')->get()->map(function ($motor) use ($validBookings) {
            $motorBookings = $validBookings->where('motorcycle_id', $motor->id);
            $totalDays = $motorBookings->sum('total_days');
            $totalRevenue = $motorBookings->sum('rental_subtotal');

            return [
                'id' => $motor->id,
                'plate_number' => $motor->plate_number,
                'category_name' => $motor->category?->name ?? '-',
                'color' => $motor->color ?? '-',
                'status' => $motor->status,
                'booking_count' => $motorBookings->count(),
                'total_days' => $totalDays,
                'total_revenue' => $totalRevenue,
            ];
        })->sortByDesc('booking_count')->values();

        // 3. Kinerja & Setoran Staf Lapangan
        $staffMembers = User::whereIn('role', ['staff', 'admin'])->get()->map(function ($staff) use ($validBookings) {
            $deliveries = $validBookings->where('delivery_staff_id', $staff->id);
            $returns = $validBookings->where('return_staff_id', $staff->id);

            $cashCollected = RentalInspection::where('staff_id', $staff->id)
                ->where('type', 'handover')
                ->whereIn('booking_id', $validBookings->pluck('id'))
                ->sum('payment_collected_amount');

            return [
                'id' => $staff->id,
                'name' => $staff->name,
                'phone' => $staff->phone ?? '-',
                'role' => $staff->role,
                'delivery_count' => $deliveries->count(),
                'return_count' => $returns->count(),
                'total_tasks' => $deliveries->count() + $returns->count(),
                'cash_collected' => (float) $cashCollected,
            ];
        })->sortByDesc('total_tasks')->values();

        // 4. Sebaran Zona Antar & Jemput
        $zones = DeliveryZone::all()->map(function ($zone) use ($validBookings) {
            $deliveryCount = $validBookings->where('delivery_zone_id', $zone->id)->count();
            $returnCount = $validBookings->where('return_zone_id', $zone->id)->count();
            $totalZoneRevenue = ($deliveryCount * (float) $zone->fee) + ($returnCount * (float) $zone->fee);

            return [
                'id' => $zone->id,
                'name' => $zone->name,
                'fee' => (float) $zone->fee,
                'delivery_count' => $deliveryCount,
                'return_count' => $returnCount,
                'total_trips' => $deliveryCount + $returnCount,
                'revenue_generated' => $totalZoneRevenue,
            ];
        })->sortByDesc('total_trips')->values();

        $categories = MotorcycleCategory::orderBy('name')->get();
        $allMotorcycles = Motorcycle::orderBy('plate_number')->get();
        $allStaff = User::whereIn('role', ['staff', 'admin'])->orderBy('name')->get();

        return view('admin.reports.index', compact(
            'bookings',
            'metrics',
            'motorcycles',
            'staffMembers',
            'zones',
            'categories',
            'allMotorcycles',
            'allStaff',
            'startDate',
            'endDate',
            'status',
            'categoryId',
            'motorcycleId',
            'staffId',
            'activeTab'
        ));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->input('status', 'all');
        $categoryId = $request->input('category_id');
        $motorcycleId = $request->input('motorcycle_id');
        $staffId = $request->input('staff_id');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $query = Booking::with(['category', 'motorcycle', 'deliveryZone', 'returnZone', 'deliveryStaff', 'returnStaff', 'handoverInspection'])
            ->whereBetween('start_time', [$startDateTime, $endDateTime]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }
        if (! empty($motorcycleId)) {
            $query->where('motorcycle_id', $motorcycleId);
        }
        if (! empty($staffId)) {
            $query->where(function ($q) use ($staffId) {
                $q->where('delivery_staff_id', $staffId)
                    ->orWhere('return_staff_id', $staffId);
            });
        }

        $bookings = $query->orderBy('start_time', 'desc')->get();
        $fileName = 'laporan_sewamotor_'.$startDate.'_sd_'.$endDate.'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fwrite($file, "\xEF\xBB\xBF");

            // Header kolom
            fputcsv($file, [
                'Kode Booking',
                'Tanggal Booking',
                'Nama Pelanggan',
                'No. HP',
                'Tipe Motor',
                'Plat Nomor',
                'Jadwal Mulai',
                'Jadwal Selesai',
                'Durasi (Hari)',
                'Overtime (Jam)',
                'Tarif Harian (Rp)',
                'Subtotal Sewa (Rp)',
                'Biaya Overtime (Rp)',
                'Biaya Antar (Rp)',
                'Biaya Jemput (Rp)',
                'Total Biaya (Rp)',
                'DP Masuk (Rp)',
                'Sisa Tagihan (Rp)',
                'Status Reservasi',
                'Status Pembayaran',
                'Titik Antar',
                'Titik Jemput',
                'Kurir Pengantar',
                'Kurir Penjemput',
                'Catatan',
            ]);

            foreach ($bookings as $b) {
                fputcsv($file, [
                    $b->booking_code,
                    $b->created_at ? $b->created_at->format('d/m/Y H:i') : '-',
                    $b->customer_name,
                    $b->customer_phone,
                    $b->category?->name ?? '-',
                    $b->motorcycle?->plate_number ?? 'Belum dialokasikan',
                    $b->start_time->format('d/m/Y H:i'),
                    $b->end_time->format('d/m/Y H:i'),
                    $b->total_days,
                    $b->overtime_hours,
                    (float) $b->daily_price,
                    (float) $b->rental_subtotal,
                    (float) $b->overtime_fee,
                    (float) $b->delivery_fee,
                    (float) $b->return_fee,
                    (float) $b->total_amount,
                    (float) $b->down_payment_amount,
                    (float) $b->remaining_amount,
                    $b->formatted_status,
                    $b->payment_status === 'paid_full' ? 'Lunas' : ($b->payment_status === 'dp_approved' ? 'DP Terkonfirmasi' : ucfirst($b->payment_status)),
                    $b->deliveryZone?->name ?? '-',
                    $b->returnZone?->name ?? '-',
                    $b->deliveryStaff?->name ?? '-',
                    $b->returnStaff?->name ?? '-',
                    $b->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request): View
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $status = $request->input('status', 'all');
        $categoryId = $request->input('category_id');
        $motorcycleId = $request->input('motorcycle_id');
        $staffId = $request->input('staff_id');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $query = Booking::with(['category', 'motorcycle', 'deliveryZone', 'returnZone', 'deliveryStaff', 'returnStaff', 'handoverInspection'])
            ->whereBetween('start_time', [$startDateTime, $endDateTime]);

        if ($status !== 'all') {
            $query->where('status', $status);
        }
        if (! empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }
        if (! empty($motorcycleId)) {
            $query->where('motorcycle_id', $motorcycleId);
        }
        if (! empty($staffId)) {
            $query->where(function ($q) use ($staffId) {
                $q->where('delivery_staff_id', $staffId)
                    ->orWhere('return_staff_id', $staffId);
            });
        }

        $bookings = $query->orderBy('start_time', 'asc')->get();
        $validBookings = $bookings->filter(fn ($b) => $b->status !== 'rejected');

        $metrics = [
            'total_bookings' => $bookings->count(),
            'valid_count' => $validBookings->count(),
            'total_gross' => $validBookings->sum('total_amount'),
            'total_rental' => $validBookings->sum('rental_subtotal'),
            'total_overtime' => $validBookings->sum('overtime_fee'),
            'total_delivery' => $validBookings->sum(fn ($b) => $b->delivery_fee + $b->return_fee),
            'total_dp' => $validBookings->sum('down_payment_amount'),
            'total_paid' => $validBookings->sum(function ($b) {
                if ($b->handoverInspection && $b->handoverInspection->payment_collected_amount > 0) {
                    return (float) $b->handoverInspection->payment_collected_amount;
                }
                if ($b->payment_status === 'paid_full' || $b->status === 'completed') {
                    return max(0, (float) $b->total_amount - (float) $b->down_payment_amount);
                }

                return 0;
            }),
        ];

        return view('admin.reports.print', compact(
            'bookings',
            'metrics',
            'startDate',
            'endDate',
            'status'
        ));
    }
}
