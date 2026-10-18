<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Motorcycle extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'plate_number',
        'color',
        'year',
        'default_helmets_count',
        'default_raincoats_count',
        'default_key_and_stnk',
        'status',
        'notes',
    ];

    protected $casts = [
        'default_helmets_count' => 'integer',
        'default_raincoats_count' => 'integer',
        'default_key_and_stnk' => 'boolean',
    ];

    public function getBrandAttribute(): ?string
    {
        return $this->category?->brand ?? null;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MotorcycleCategory::class, 'category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'motorcycle_id');
    }

    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    /**
     * Cari pesanan aktif yang bentrok dengan rentang jadwal ini.
     */
    public function getConflictingBooking($startTime, $endTime, ?int $excludeBookingId = null): ?Booking
    {
        return $this->bookings()
            ->whereIn('status', ['approved', 'delivering', 'active', 'returning'])
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->first();
    }

    /**
     * Cek apakah motor sedang memiliki konflik jadwal sewa.
     */
    public function hasScheduleConflict($startTime, $endTime, ?int $excludeBookingId = null): bool
    {
        return $this->getConflictingBooking($startTime, $endTime, $excludeBookingId) !== null;
    }
}
