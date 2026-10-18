<?php

namespace Tests\Feature;

use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use App\Models\SeasonalPrice;
use App\Models\User;
use App\Services\PricingCalculator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DynamicPricingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pricing_calculator_calculates_regular_and_weekend_prices(): void
    {
        $category = MotorcycleCategory::create([
            'name' => 'Honda Vario 125 Sporty',
            'brand' => 'Honda',
            'slug' => Str::slug('Honda Vario 125 Sporty'),
            'daily_price' => 70000,
            'weekend_price' => 85000,
            'hourly_overtime_price' => 10000,
            'cc_capacity' => 125,
            'is_active' => true,
        ]);

        // Jumat 09:00 sampai Minggu 09:00 (2 hari weekend: Jumat & Sabtu)
        $start = Carbon::parse('2026-10-23 09:00:00'); // Friday
        $end = Carbon::parse('2026-10-25 09:00:00'); // Sunday

        $pricing = PricingCalculator::calculateRentalPrice($category, $start, $end);

        $this->assertEquals(2, $pricing['days']);
        $this->assertTrue($pricing['has_weekend']);
        $this->assertEquals(170000, $pricing['rental_subtotal']); // 2 * 85000
        $this->assertCount(2, $pricing['daily_breakdown']);
    }

    public function test_pricing_calculator_applies_seasonal_prices(): void
    {
        $category = MotorcycleCategory::create([
            'name' => 'Yamaha NMAX 155 Connected',
            'brand' => 'Yamaha',
            'slug' => Str::slug('Yamaha NMAX 155 Connected'),
            'daily_price' => 100000,
            'weekend_price' => 115000,
            'hourly_overtime_price' => 15000,
            'cc_capacity' => 155,
            'is_active' => true,
        ]);

        SeasonalPrice::create([
            'name' => 'Libur Lebaran',
            'start_date' => '2026-10-20',
            'end_date' => '2026-10-22',
            'category_id' => null,
            'price_type' => 'surcharge',
            'amount' => 25000,
            'is_active' => true,
        ]);

        // Selasa 20 Okt - Kamis 22 Okt (2 hari weekday + surcharge 25.000)
        $start = Carbon::parse('2026-10-20 09:00:00');
        $end = Carbon::parse('2026-10-22 09:00:00');

        $pricing = PricingCalculator::calculateRentalPrice($category, $start, $end);

        $this->assertEquals(2, $pricing['days']);
        $this->assertTrue($pricing['has_seasonal']);
        // Tiap hari: 100.000 + 25.000 = 125.000 -> 2 hari = 250.000
        $this->assertEquals(250000, $pricing['rental_subtotal']);
    }

    public function test_admin_can_manage_seasonal_prices(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        // Create seasonal price
        $response = $this->actingAs($admin)->post(route('admin.seasonal-prices.store'), [
            'name' => 'Tahun Baru 2027',
            'start_date' => '2026-12-30',
            'end_date' => '2027-01-02',
            'price_type' => 'fixed',
            'amount' => 150000,
            'notes' => 'Peak season pergantian tahun',
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.seasonal-prices.index'));
        $this->assertDatabaseHas('seasonal_prices', [
            'name' => 'Tahun Baru 2027',
            'amount' => 150000,
            'price_type' => 'fixed',
        ]);

        $seasonal = SeasonalPrice::where('name', 'Tahun Baru 2027')->first();

        // Update
        $this->actingAs($admin)->put(route('admin.seasonal-prices.update', $seasonal->id), [
            'name' => 'Tahun Baru 2027 Revisit',
            'start_date' => '2026-12-30',
            'end_date' => '2027-01-02',
            'price_type' => 'fixed',
            'amount' => 160000,
            'is_active' => '1',
        ]);

        $this->assertDatabaseHas('seasonal_prices', [
            'id' => $seasonal->id,
            'name' => 'Tahun Baru 2027 Revisit',
            'amount' => 160000,
        ]);

        // Delete
        $this->actingAs($admin)->delete(route('admin.seasonal-prices.destroy', $seasonal->id));
        $this->assertDatabaseMissing('seasonal_prices', [
            'id' => $seasonal->id,
        ]);
    }

    public function test_check_availability_api_returns_dynamic_pricing_breakdown(): void
    {
        $category = MotorcycleCategory::create([
            'name' => 'Honda Scoopy Prestige',
            'brand' => 'Honda',
            'slug' => Str::slug('Honda Scoopy Prestige'),
            'daily_price' => 75000,
            'weekend_price' => 90000,
            'hourly_overtime_price' => 10000,
            'cc_capacity' => 110,
            'is_active' => true,
        ]);

        Motorcycle::create([
            'category_id' => $category->id,
            'plate_number' => 'AB 9999 ZZ',
            'color' => 'Merah',
            'year' => 2023,
            'status' => 'available',
        ]);

        // Jumat 09:00 - Sabtu 09:00 (1 hari weekend)
        $start = '2026-10-23 09:00:00';
        $end = '2026-10-24 09:00:00';

        $response = $this->postJson(route('booking.check'), [
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('calculation.days', 1);

        $returnedCategories = collect($response->json('categories'));
        $targetCategory = $returnedCategories->firstWhere('id', $category->id);

        $this->assertNotNull($targetCategory);
        $this->assertEquals(90000, $targetCategory['rental_subtotal']);
        $this->assertTrue($targetCategory['has_weekend']);
    }
}
