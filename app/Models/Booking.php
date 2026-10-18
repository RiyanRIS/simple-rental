<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_code',
        'customer_name',
        'customer_phone',
        'customer_id_type',
        'customer_id_number',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relation',
        'id_card_photo',
        'sim_c_photo',
        'travel_ticket_photo',
        'hotel_booking_photo',
        'social_media_account',
        'category_id',
        'motorcycle_id',
        'start_time',
        'end_time',
        'total_days',
        'overtime_hours',
        'delivery_zone_id',
        'delivery_address',
        'return_zone_id',
        'return_address',
        'daily_price',
        'rental_subtotal',
        'pricing_breakdown',
        'overtime_fee',
        'delivery_fee',
        'return_fee',
        'total_amount',
        'down_payment_amount',
        'remaining_amount',
        'payment_status',
        'payment_proof',
        'status',
        'rejection_reason',
        'delivery_staff_id',
        'return_staff_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'daily_price' => 'decimal:2',
            'rental_subtotal' => 'decimal:2',
            'pricing_breakdown' => 'array',
            'overtime_fee' => 'decimal:2',
            'delivery_fee' => 'decimal:2',
            'return_fee' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'down_payment_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'total_days' => 'integer',
            'overtime_hours' => 'integer',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MotorcycleCategory::class, 'category_id');
    }

    public function motorcycle(): BelongsTo
    {
        return $this->belongsTo(Motorcycle::class, 'motorcycle_id');
    }

    public function deliveryZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'delivery_zone_id');
    }

    public function returnZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'return_zone_id');
    }

    public function deliveryStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivery_staff_id');
    }

    public function returnStaff(): BelongsTo
    {
        return $this->belongsTo(User::class, 'return_staff_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(RentalInspection::class, 'booking_id');
    }

    public function handoverInspection(): HasOne
    {
        return $this->hasOne(RentalInspection::class, 'booking_id')->where('type', 'handover');
    }

    public function returnInspection(): HasOne
    {
        return $this->hasOne(RentalInspection::class, 'booking_id')->where('type', 'return');
    }

    public function getFormattedStatusAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Menunggu Verifikasi',
            'approved' => 'Disetujui (Siap Antar)',
            'delivering' => 'Sedang Diantar',
            'active' => 'Sedang Disewa',
            'returning' => 'Proses Penjemputan',
            'completed' => 'Selesai',
            'rejected' => 'Ditolak',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($this->status),
        };
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-amber-100 text-amber-800 border-amber-200',
            'approved' => 'bg-blue-100 text-blue-800 border-blue-200',
            'delivering' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            'active' => 'bg-emerald-100 text-emerald-800 border-emerald-200',
            'returning' => 'bg-purple-100 text-purple-800 border-purple-200',
            'completed' => 'bg-slate-100 text-slate-800 border-slate-200',
            'rejected', 'cancelled' => 'bg-rose-100 text-rose-800 border-rose-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
