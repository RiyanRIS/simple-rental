<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryZone extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'fee',
        'description',
        'is_free',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'fee' => 'decimal:2',
            'is_free' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function deliveryBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'delivery_zone_id');
    }

    public function returnBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'return_zone_id');
    }
}
