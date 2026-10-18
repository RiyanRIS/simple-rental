<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class MotorcycleCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'brand',
        'slug',
        'cc_capacity',
        'daily_price',
        'weekend_price',
        'hourly_overtime_price',
        'description',
        'image',
        'image_thumbnail',
        'is_active',
    ];

    protected $appends = [
        'image_url',
        'image_thumbnail_url',
    ];

    protected function casts(): array
    {
        return [
            'daily_price' => 'decimal:2',
            'weekend_price' => 'decimal:2',
            'hourly_overtime_price' => 'decimal:2',
            'is_active' => 'boolean',
            'cc_capacity' => 'integer',
        ];
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image) {
            return null;
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        if (file_exists(public_path('images/motorcycles/'.$this->image))) {
            return asset('images/motorcycles/'.$this->image);
        }

        if (Storage::disk('public')->exists('categories/'.$this->image)) {
            return asset('storage/categories/'.$this->image);
        }

        return asset('images/motorcycles/'.$this->image);
    }

    public function getImageThumbnailUrlAttribute(): ?string
    {
        $thumb = $this->image_thumbnail;

        // Fallback jika belum diset kolom image_thumbnail tapi nama file punya pola
        if (! $thumb && $this->image) {
            if (str_contains($this->image, '_large.')) {
                $thumb = str_replace('_large.', '_thumb.', $this->image);
            } else {
                $ext = pathinfo($this->image, PATHINFO_EXTENSION);
                $name = pathinfo($this->image, PATHINFO_FILENAME);
                $potentialThumb = "{$name}_thumb.{$ext}";
                if (file_exists(public_path('images/motorcycles/'.$potentialThumb)) || Storage::disk('public')->exists('categories/'.$potentialThumb)) {
                    $thumb = $potentialThumb;
                }
            }
        }

        if (! $thumb) {
            return $this->image_url;
        }

        if (str_starts_with($thumb, 'http://') || str_starts_with($thumb, 'https://')) {
            return $thumb;
        }

        if (file_exists(public_path('images/motorcycles/'.$thumb))) {
            return asset('images/motorcycles/'.$thumb);
        }

        if (Storage::disk('public')->exists('categories/'.$thumb)) {
            return asset('storage/categories/'.$thumb);
        }

        return $this->image_url;
    }

    public function motorcycles(): HasMany
    {
        return $this->hasMany(Motorcycle::class, 'category_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'category_id');
    }

    public function seasonalPrices(): HasMany
    {
        return $this->hasMany(SeasonalPrice::class, 'category_id');
    }

    /**
     * Hitung unit yang tersedia pada rentang waktu tertentu secara otomatis.
     */
    public function getAvailableUnitsCount($startTime, $endTime): int
    {
        $totalActiveUnits = $this->motorcycles()
            ->where('status', '!=', 'maintenance')
            ->count();

        $bookedUnitsCount = Booking::where('category_id', $this->id)
            ->whereIn('status', ['approved', 'delivering', 'active', 'returning'])
            ->where(function ($query) use ($startTime, $endTime) {
                $query->where('start_time', '<', $endTime)
                    ->where('end_time', '>', $startTime);
            })
            ->count();

        return max(0, $totalActiveUnits - $bookedUnitsCount);
    }
}
