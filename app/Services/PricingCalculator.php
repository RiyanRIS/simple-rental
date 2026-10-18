<?php

namespace App\Services;

use App\Models\MotorcycleCategory;
use App\Models\SeasonalPrice;
use Carbon\Carbon;

class PricingCalculator
{
    /**
     * Hitung durasi 24 jam penuh + overtime.
     * Aturan:
     * - <= 24 jam: 1 hari, 0 overtime
     * - > 24 jam: floor(jam / 24) hari
     * - sisa jam <= 0.5 jam (30 menit): toleransi gratis
     * - sisa jam <= 4 jam: dihitung jam overtime
     * - sisa jam > 4 jam: dibulatkan menjadi +1 hari penuh
     */
    public static function calculateDuration(Carbon $start, Carbon $end): array
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

        if ($remainingHours <= 0.5) {
            $overtimeHours = 0;
        } elseif ($remainingHours <= 4) {
            $overtimeHours = (int) ceil($remainingHours);
        } else {
            $days += 1;
            $overtimeHours = 0;
        }

        return [
            'days' => max(1, $days),
            'overtime_hours' => $overtimeHours,
            'raw_hours' => round($totalHours, 1),
        ];
    }

    /**
     * Hitung rincian harga sewa motor per hari kalender dengan mempertimbangkan Weekend dan Musim Libur (Seasonal).
     *
     * @return array{
     *     days: int,
     *     overtime_hours: int,
     *     rental_subtotal: float,
     *     overtime_subtotal: float,
     *     estimated_total: float,
     *     daily_breakdown: array<int, array{
     *         day_number: int,
     *         date: string,
     *         day_name: string,
     *         price: float,
     *         base_price: float,
     *         is_weekend: bool,
     *         is_seasonal: bool,
     *         reason: string
     *     }>,
     *     has_weekend: bool,
     *     has_seasonal: bool
     * }
     */
    public static function calculateRentalPrice(MotorcycleCategory $category, Carbon $start, Carbon $end): array
    {
        $duration = self::calculateDuration($start, $end);
        $daysCount = $duration['days'];
        $overtimeHours = $duration['overtime_hours'];

        // Ambil semua aturan seasonal yang aktif dan beririsan dengan rentang sewa
        $startDateStr = $start->toDateString();
        $endDateStr = $end->toDateString();

        $seasonalRules = SeasonalPrice::active()
            ->where(function ($q) use ($category) {
                $q->whereNull('category_id')->orWhere('category_id', $category->id);
            })
            ->where(function ($q) use ($startDateStr, $endDateStr) {
                $q->where('start_date', '<=', $endDateStr)
                    ->where('end_date', '>=', $startDateStr);
            })
            ->get();

        $dailyBreakdown = [];
        $rentalSubtotal = 0;
        $hasWeekend = false;
        $hasSeasonal = false;

        $baseWeekdayPrice = (float) $category->daily_price;
        $weekendPrice = (float) ($category->weekend_price ?: $baseWeekdayPrice);

        // Iterasi tiap periode 24 jam (hari ke-1 s/d hari ke-N)
        for ($i = 0; $i < $daysCount; $i++) {
            $currentDayStart = $start->copy()->addDays($i);
            $dateString = $currentDayStart->toDateString();
            $dayOfWeek = $currentDayStart->dayOfWeek; // 0 = Sunday, 5 = Friday, 6 = Saturday

            // Cek apakah hari ini termasuk Weekend (Jumat = 5, Sabtu = 6, Minggu = 0)
            $isWeekend = in_array($dayOfWeek, [0, 5, 6]);
            $dayBasePrice = $isWeekend ? $weekendPrice : $baseWeekdayPrice;
            $rateType = $isWeekend ? 'Tarif Weekend' : 'Tarif Reguler';

            // Cek apakah ada aturan Seasonal untuk tanggal ini
            $applicableSeasonal = $seasonalRules->first(function ($rule) use ($dateString) {
                return $dateString >= $rule->start_date->toDateString()
                    && $dateString <= $rule->end_date->toDateString();
            });

            $finalDayPrice = $dayBasePrice;
            $isSeasonal = false;
            $reason = $rateType;

            if ($applicableSeasonal) {
                $isSeasonal = true;
                $hasSeasonal = true;
                if ($applicableSeasonal->price_type === 'fixed') {
                    $finalDayPrice = (float) $applicableSeasonal->amount;
                    $reason = "{$applicableSeasonal->name} (Tarif Khusus)";
                } else {
                    // Surcharge (tambahan)
                    $finalDayPrice = $dayBasePrice + (float) $applicableSeasonal->amount;
                    $reason = "{$rateType} + {$applicableSeasonal->name} (+Rp ".number_format($applicableSeasonal->amount, 0, ',', '.').')';
                }
            } elseif ($isWeekend && $weekendPrice > $baseWeekdayPrice) {
                $hasWeekend = true;
                $reason = 'Tarif Weekend (Jum-Min)';
            }

            $rentalSubtotal += $finalDayPrice;

            $dayNamesIndo = [
                0 => 'Minggu',
                1 => 'Senin',
                2 => 'Selasa',
                3 => 'Rabu',
                4 => 'Kamis',
                5 => 'Jumat',
                6 => 'Sabtu',
            ];

            $dailyBreakdown[] = [
                'day_number' => $i + 1,
                'date' => $currentDayStart->format('d/m/Y'),
                'day_name' => $dayNamesIndo[$dayOfWeek] ?? '',
                'price' => $finalDayPrice,
                'base_price' => $baseWeekdayPrice,
                'is_weekend' => $isWeekend,
                'is_seasonal' => $isSeasonal,
                'reason' => $reason,
            ];
        }

        $overtimeSubtotal = (float) ($category->hourly_overtime_price * $overtimeHours);
        $estimatedTotal = $rentalSubtotal + $overtimeSubtotal;

        return [
            'days' => $daysCount,
            'overtime_hours' => $overtimeHours,
            'rental_subtotal' => $rentalSubtotal,
            'overtime_subtotal' => $overtimeSubtotal,
            'estimated_total' => $estimatedTotal,
            'daily_breakdown' => $dailyBreakdown,
            'has_weekend' => $hasWeekend,
            'has_seasonal' => $hasSeasonal,
        ];
    }
}
