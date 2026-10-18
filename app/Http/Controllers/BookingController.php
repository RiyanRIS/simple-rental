<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\DeliveryZone;
use App\Models\MotorcycleCategory;
use App\Services\ImageOptimizer;
use App\Services\PricingCalculator;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(): View
    {
        $categories = MotorcycleCategory::where('is_active', true)->with('motorcycles')->get();
        $zones = DeliveryZone::where('is_active', true)->get();

        $defaultStart = Carbon::now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d\TH:i');
        $defaultEnd = Carbon::now()->addDays(2)->setHour(9)->setMinute(0)->format('Y-m-d\TH:i');

        $initialStart = Carbon::parse($defaultStart);
        $initialEnd = Carbon::parse($defaultEnd);

        $categoriesJson = $categories->map(function ($c) use ($initialStart, $initialEnd) {
            $pricing = PricingCalculator::calculateRentalPrice($c, $initialStart, $initialEnd);

            return [
                'id' => $c->id,
                'name' => $c->name,
                'daily_price' => (float) $c->daily_price,
                'weekend_price' => $c->weekend_price ? (float) $c->weekend_price : (float) $c->daily_price,
                'hourly_overtime_price' => (float) $c->hourly_overtime_price,
                'cc_capacity' => $c->cc_capacity,
                'description' => $c->description,
                'image_url' => $c->image_url,
                'image_thumbnail_url' => $c->image_thumbnail_url,
                'available_count' => $c->getAvailableUnitsCount($initialStart, $initialEnd),
                'is_available' => $c->getAvailableUnitsCount($initialStart, $initialEnd) > 0,
                'rental_subtotal' => $pricing['rental_subtotal'],
                'overtime_subtotal' => $pricing['overtime_subtotal'],
                'estimated_subtotal' => $pricing['estimated_total'],
                'daily_breakdown' => $pricing['daily_breakdown'],
                'has_weekend' => $pricing['has_weekend'],
                'has_seasonal' => $pricing['has_seasonal'],
            ];
        });

        $initialCalculation = PricingCalculator::calculateDuration($initialStart, $initialEnd);

        return view('bookings.create', compact('categories', 'zones', 'defaultStart', 'defaultEnd', 'categoriesJson', 'initialCalculation'));
    }

    public function checkAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
        ]);

        $start = Carbon::parse($request->input('start_time'));
        $end = Carbon::parse($request->input('end_time'));

        $calculation = PricingCalculator::calculateDuration($start, $end);

        $categories = MotorcycleCategory::where('is_active', true)
            ->withCount('motorcycles')
            ->get()
            ->map(function ($cat) use ($start, $end) {
                $availableCount = $cat->getAvailableUnitsCount($start, $end);
                $pricing = PricingCalculator::calculateRentalPrice($cat, $start, $end);

                return [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'daily_price' => (float) $cat->daily_price,
                    'weekend_price' => $cat->weekend_price ? (float) $cat->weekend_price : (float) $cat->daily_price,
                    'hourly_overtime_price' => (float) $cat->hourly_overtime_price,
                    'cc_capacity' => $cat->cc_capacity,
                    'description' => $cat->description,
                    'image_url' => $cat->image_url,
                    'image_thumbnail_url' => $cat->image_thumbnail_url,
                    'available_count' => $availableCount,
                    'is_available' => $availableCount > 0,
                    'rental_subtotal' => $pricing['rental_subtotal'],
                    'overtime_subtotal' => $pricing['overtime_subtotal'],
                    'estimated_subtotal' => $pricing['estimated_total'],
                    'daily_breakdown' => $pricing['daily_breakdown'],
                    'has_weekend' => $pricing['has_weekend'],
                    'has_seasonal' => $pricing['has_seasonal'],
                ];
            });

        return response()->json([
            'success' => true,
            'calculation' => $calculation,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $messages = [
            'id_card_photo.required' => 'Foto KTP asli wajib diunggah.',
            'id_card_photo.image' => 'Berkas KTP harus berupa foto (JPG, PNG, atau WEBP).',
            'id_card_photo.mimes' => 'Format foto KTP harus berupa JPG, JPEG, PNG, atau WEBP.',
            'id_card_photo.max' => 'Ukuran foto KTP tidak boleh lebih dari 5 MB.',
            'sim_c_photo.required' => 'Foto SIM C asli wajib diunggah.',
            'sim_c_photo.image' => 'Berkas SIM C harus berupa foto (JPG, PNG, atau WEBP).',
            'sim_c_photo.mimes' => 'Format foto SIM C harus berupa JPG, JPEG, PNG, atau WEBP.',
            'sim_c_photo.max' => 'Ukuran foto SIM C tidak boleh lebih dari 5 MB.',
            'payment_proof.required' => 'Bukti transfer DP wajib diunggah.',
            'payment_proof.image' => 'Berkas bukti transfer harus berupa foto (JPG, PNG, atau WEBP).',
            'payment_proof.mimes' => 'Format bukti transfer harus berupa JPG, JPEG, PNG, atau WEBP.',
            'payment_proof.max' => 'Ukuran bukti transfer tidak boleh lebih dari 5 MB.',
            'travel_ticket_photo.image' => 'Berkas tiket perjalanan harus berupa foto.',
            'travel_ticket_photo.mimes' => 'Format tiket perjalanan harus berupa JPG, JPEG, PNG, atau WEBP.',
            'travel_ticket_photo.max' => 'Ukuran foto tiket perjalanan tidak boleh lebih dari 5 MB.',
            'hotel_booking_photo.image' => 'Berkas bukti penginapan harus berupa foto.',
            'hotel_booking_photo.mimes' => 'Format bukti penginapan harus berupa JPG, JPEG, PNG, atau WEBP.',
            'hotel_booking_photo.max' => 'Ukuran foto bukti penginapan tidak boleh lebih dari 5 MB.',
        ];

        $validated = $request->validate([
            'customer_name' => 'required|string|max:150',
            'customer_phone' => 'required|string|max:30',
            'customer_id_type' => 'required|string',
            'customer_id_number' => 'required|string|max:50',
            'emergency_contact_name' => 'required|string|max:150',
            'emergency_contact_phone' => 'required|string|max:30',
            'emergency_contact_relation' => 'required|string|max:100',
            'category_id' => 'required|exists:motorcycle_categories,id',
            'start_time' => 'required|date',
            'end_time' => 'required|date|after:start_time',
            'delivery_zone_id' => 'required|exists:delivery_zones,id',
            'delivery_address' => 'required|string|max:500',
            'return_zone_id' => 'required|exists:delivery_zones,id',
            'return_address' => 'required|string|max:500',
            'down_payment_amount' => 'required|numeric|min:50000',
            'id_card_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'sim_c_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'travel_ticket_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'hotel_booking_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'social_media_account' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:1000',
        ], $messages);

        $category = MotorcycleCategory::findOrFail($validated['category_id']);
        $start = Carbon::parse($validated['start_time']);
        $end = Carbon::parse($validated['end_time']);

        // Pastikan ketersediaan unit masih ada
        $availableUnits = $category->getAvailableUnitsCount($start, $end);
        if ($availableUnits <= 0) {
            return back()->withInput()->withErrors([
                'category_id' => 'Maaf, tipe motor ini sudah terbooking penuh pada jam tersebut. Silakan pilih tipe motor lain.',
            ]);
        }

        $pricing = PricingCalculator::calculateRentalPrice($category, $start, $end);
        $duration = [
            'days' => $pricing['days'],
            'overtime_hours' => $pricing['overtime_hours'],
        ];

        $deliveryZone = DeliveryZone::findOrFail($validated['delivery_zone_id']);
        $returnZone = DeliveryZone::findOrFail($validated['return_zone_id']);

        $rentalSubtotal = $pricing['rental_subtotal'];
        $overtimeFee = $pricing['overtime_subtotal'];
        $deliveryFee = $deliveryZone->fee;
        $returnFee = $returnZone->fee;
        $totalAmount = $rentalSubtotal + $overtimeFee + $deliveryFee + $returnFee;

        $dpAmount = (float) $validated['down_payment_amount'];
        $remainingAmount = max(0, $totalAmount - $dpAmount);

        // Upload & Kompresi Berkas Otomatis
        $idCardPath = ImageOptimizer::compressAndStore($request->file('id_card_photo'), 'bookings/id_cards');
        $simCPath = ImageOptimizer::compressAndStore($request->file('sim_c_photo'), 'bookings/sim');
        $paymentProofPath = ImageOptimizer::compressAndStore($request->file('payment_proof'), 'bookings/payments');

        $ticketPath = $request->hasFile('travel_ticket_photo')
            ? ImageOptimizer::compressAndStore($request->file('travel_ticket_photo'), 'bookings/tickets')
            : null;

        $hotelPath = $request->hasFile('hotel_booking_photo')
            ? ImageOptimizer::compressAndStore($request->file('hotel_booking_photo'), 'bookings/hotel')
            : null;

        $bookingCode = 'JOG-'.Carbon::now()->format('ymd').'-'.Str::upper(Str::random(4));

        $booking = Booking::create([
            'booking_code' => $bookingCode,
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_id_type' => $validated['customer_id_type'],
            'customer_id_number' => $validated['customer_id_number'],
            'emergency_contact_name' => $validated['emergency_contact_name'],
            'emergency_contact_phone' => $validated['emergency_contact_phone'],
            'emergency_contact_relation' => $validated['emergency_contact_relation'],
            'id_card_photo' => $idCardPath,
            'sim_c_photo' => $simCPath,
            'travel_ticket_photo' => $ticketPath,
            'hotel_booking_photo' => $hotelPath,
            'social_media_account' => $validated['social_media_account'] ?? null,
            'category_id' => $category->id,
            'motorcycle_id' => null, // akan di-assign admin saat approve
            'start_time' => $start,
            'end_time' => $end,
            'total_days' => $duration['days'],
            'overtime_hours' => $duration['overtime_hours'],
            'delivery_zone_id' => $deliveryZone->id,
            'delivery_address' => $validated['delivery_address'],
            'return_zone_id' => $returnZone->id,
            'return_address' => $validated['return_address'],
            'daily_price' => $category->daily_price,
            'rental_subtotal' => $rentalSubtotal,
            'pricing_breakdown' => $pricing['daily_breakdown'],
            'overtime_fee' => $overtimeFee,
            'delivery_fee' => $deliveryFee,
            'return_fee' => $returnFee,
            'total_amount' => $totalAmount,
            'down_payment_amount' => $dpAmount,
            'remaining_amount' => $remainingAmount,
            'payment_status' => 'dp_pending_verification',
            'payment_proof' => $paymentProofPath,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('booking.ticket', $booking->booking_code)
            ->with('success', 'Pemesanan berhasil dikirim! Silakan tunggu verifikasi bukti DP oleh tim Admin kami.');
    }

    public function track(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $bookings = collect();

        if ($search !== '') {
            $cleanCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $search));
            $cleanDigits = preg_replace('/[^0-9]/', '', $search);

            $query = Booking::with([
                'category',
                'motorcycle',
                'deliveryZone',
                'returnZone',
                'deliveryStaff',
                'returnStaff',
            ]);

            $query->where(function ($q) use ($search, $cleanCode, $cleanDigits) {
                // 1. Pencarian Kode Booking Toleran (Abaikan strip '-', spasi, dan huruf kecil)
                if (! empty($cleanCode)) {
                    $codeVariants = [$cleanCode];
                    if (! str_starts_with($cleanCode, 'JOG')) {
                        $codeVariants[] = 'JOG'.$cleanCode;
                    }

                    foreach ($codeVariants as $cv) {
                        $q->orWhereRaw("REPLACE(UPPER(booking_code), '-', '') = ?", [$cv]);
                    }
                }

                // 2. Pencarian Nomor WhatsApp Toleran (08xx vs 62xx vs format berstrip)
                if (strlen($cleanDigits) >= 8) {
                    $phoneVariants = [$cleanDigits];
                    if (str_starts_with($cleanDigits, '62')) {
                        $phoneVariants[] = '0'.substr($cleanDigits, 2);
                    } elseif (str_starts_with($cleanDigits, '0')) {
                        $phoneVariants[] = '62'.substr($cleanDigits, 1);
                    }

                    $q->orWhereIn('customer_phone', $phoneVariants);
                    $q->orWhereIn('emergency_contact_phone', $phoneVariants);
                }

                // 3. Fallback pencarian persis
                $q->orWhere('booking_code', $search)
                    ->orWhere('customer_phone', $search);
            });

            $bookings = $query->latest()->get();
        }

        $booking = $bookings->first();

        return view('bookings.track', compact('booking', 'bookings', 'search'));
    }

    public function showTicket(string $code): View
    {
        $booking = Booking::with([
            'category',
            'motorcycle',
            'deliveryZone',
            'returnZone',
            'deliveryStaff',
            'returnStaff',
            'handoverInspection.staff',
            'returnInspection.staff',
        ])
            ->where('booking_code', $code)
            ->firstOrFail();

        return view('bookings.ticket', compact('booking'));
    }

    /**
     * Hitung durasi 24 jam penuh + overtime.
     */
    private function calculateRentalDuration(Carbon $start, Carbon $end): array
    {
        $totalMinutes = $start->diffInMinutes($end);
        $totalHours = $totalMinutes / 60;

        if ($totalHours <= 24) {
            return [
                'days' => 1,
                'overtime_hours' => 0,
                'raw_hours' => round($totalHours, 1),
            ];
        }

        $days = (int) floor($totalHours / 24);
        $remainingHours = $totalHours - ($days * 24);

        // Toleransi gratis 30 menit
        if ($remainingHours <= 0.5) {
            $overtimeHours = 0;
        } elseif ($remainingHours <= 4) {
            $overtimeHours = (int) ceil($remainingHours);
        } else {
            // Lebih dari 4 jam otomatis dihitung sewa 1 hari penuh
            $days += 1;
            $overtimeHours = 0;
        }

        return [
            'days' => max(1, $days),
            'overtime_hours' => $overtimeHours,
            'raw_hours' => round($totalHours, 1),
        ];
    }
}
