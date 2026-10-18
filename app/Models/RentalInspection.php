<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalInspection extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'staff_id',
        'type',
        'fuel_level',
        'helmets_count',
        'raincoats_count',
        'key_and_stnk_included',
        'physical_id_collected',
        'emergency_contact_verified',
        'emergency_verification_note',
        'remaining_payment_collected',
        'payment_collected_amount',
        'payment_collected_method',
        'damage_notes',
        'photo_paths',
        'customer_signature',
        'inspected_at',
    ];

    protected function casts(): array
    {
        return [
            'photo_paths' => 'array',
            'inspected_at' => 'datetime',
            'key_and_stnk_included' => 'boolean',
            'physical_id_collected' => 'boolean',
            'emergency_contact_verified' => 'boolean',
            'remaining_payment_collected' => 'boolean',
            'payment_collected_amount' => 'decimal:2',
            'helmets_count' => 'integer',
            'raincoats_count' => 'integer',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
