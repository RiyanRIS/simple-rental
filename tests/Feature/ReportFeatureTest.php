<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DeliveryZone;
use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ReportFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login_when_accessing_reports(): void
    {
        $response = $this->get(route('admin.reports.index'));
        $response->assertRedirect(route('login'));
    }

    public function test_staff_cannot_access_reports(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();
        $response = $this->actingAs($staff)->get(route('admin.reports.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_view_reports_and_filters(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $category = MotorcycleCategory::create([
            'name' => 'Yamaha Aerox 155',
            'brand' => 'Yamaha',
            'slug' => Str::slug('Yamaha Aerox 155'),
            'daily_price' => 95000,
            'hourly_overtime_price' => 12000,
            'cc_capacity' => 155,
            'is_active' => true,
        ]);

        $motorcycle = Motorcycle::create([
            'category_id' => $category->id,
            'plate_number' => 'AB 7777 KL',
            'color' => 'Hitam',
            'year' => 2024,
            'status' => 'available',
        ]);

        $zone = DeliveryZone::create([
            'name' => 'Stasiun Lempuyangan',
            'fee' => 10000,
            'is_active' => true,
        ]);

        $start = Carbon::now()->addDays(1)->setHour(9)->setMinute(0);
        $end = Carbon::now()->addDays(3)->setHour(9)->setMinute(0);

        Booking::create([
            'booking_code' => 'BOOK-TEST-001',
            'customer_name' => 'Budi Santoso',
            'customer_phone' => '081299998888',
            'customer_id_type' => 'KTP',
            'customer_id_number' => '3471010101010001',
            'emergency_contact_name' => 'Siti',
            'emergency_contact_phone' => '081299997777',
            'emergency_contact_relation' => 'Istri',
            'id_card_photo' => 'ktp.jpg',
            'sim_c_photo' => 'sim.jpg',
            'category_id' => $category->id,
            'motorcycle_id' => $motorcycle->id,
            'start_time' => $start,
            'end_time' => $end,
            'total_days' => 2,
            'overtime_hours' => 0,
            'daily_price' => 95000,
            'rental_subtotal' => 190000,
            'overtime_fee' => 0,
            'delivery_zone_id' => $zone->id,
            'return_zone_id' => $zone->id,
            'delivery_fee' => 10000,
            'return_fee' => 10000,
            'delivery_address' => 'Pintu Barat Stasiun',
            'return_address' => 'Pintu Barat Stasiun',
            'total_amount' => 210000,
            'down_payment_amount' => 50000,
            'remaining_amount' => 160000,
            'payment_status' => 'dp_approved',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.reports.index', [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee('Laporan & Rekapitulasi', false)
            ->assertSee('Total Omset Bruto')
            ->assertSee('BOOK-TEST-001')
            ->assertSee('Budi Santoso');
    }

    public function test_admin_can_export_csv_report(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.reports.export-csv', [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk();
        $this->assertEquals('text/csv; charset=UTF-8', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment; filename=', $response->headers->get('Content-Disposition'));
    }

    public function test_admin_can_view_print_report(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get(route('admin.reports.print', [
            'start_date' => Carbon::now()->startOfMonth()->toDateString(),
            'end_date' => Carbon::now()->endOfMonth()->toDateString(),
        ]));

        $response->assertOk()
            ->assertSee('JOGJAMOTOR RENTAL')
            ->assertSee('Laporan Rekapitulasi Transaksi Sewa', false);
    }
}
