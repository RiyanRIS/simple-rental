<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\DeliveryZone;
use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use App\Models\RentalInspection;
use App\Models\User;
use App\Services\ImageOptimizer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BookingFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_public_home_page_can_be_rendered(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('JogjaMotor');
        $response->assertSee('Sistem Sewa Otomatis');
    }

    public function test_availability_check_calculates_24hr_duration_and_returns_units(): void
    {
        $start = Carbon::now()->addDay()->setHour(9)->setMinute(0)->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays(2)->setHour(12)->setMinute(0)->format('Y-m-d H:i:s'); // 1 hari + 3 jam overtime

        $response = $this->postJson('/cek-ketersediaan', [
            'start_time' => $start,
            'end_time' => $end,
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('calculation.days', 1);
        $response->assertJsonPath('calculation.overtime_hours', 3);
        $response->assertJsonStructure([
            'categories' => [
                '*' => ['id', 'name', 'daily_price', 'available_count', 'is_available'],
            ],
        ]);
    }

    public function test_booking_track_page_can_be_rendered(): void
    {
        $response = $this->get('/cek-booking');

        $response->assertStatus(200);
        $response->assertSee('Cek Status Pemesanan');
    }

    public function test_track_booking_using_dash_free_code_and_lowercase(): void
    {
        // Cari pakai huruf kecil tanpa strip: "jog2610001"
        $response = $this->get('/cek-booking?q=jog2610001');

        $response->assertStatus(200);
        $response->assertSee('JOG-2610-001');
        $response->assertSee('Dimas Surya Wijaya');

        // Cari hanya angka tanpa JOG dan tanpa strip: "2610002"
        $response2 = $this->get('/cek-booking?q=2610002');
        $response2->assertStatus(200);
        $response2->assertSee('JOG-2610-002');
        $response2->assertSee('Sarah Melinda');
    }

    public function test_track_booking_using_whatsapp_number_variants(): void
    {
        // Cari pakai nomor berawalan 08
        $response1 = $this->get('/cek-booking?q=081234455667');
        $response1->assertStatus(200);
        $response1->assertSee('JOG-2610-001');

        // Cari pakai nomor berawalan 62 (internasional)
        $response2 = $this->get('/cek-booking?q=6281234455667');
        $response2->assertStatus(200);
        $response2->assertSee('JOG-2610-001');

        // Cari nomor dengan strip / spasi
        $response3 = $this->get('/cek-booking?q=0812-3445-5667');
        $response3->assertStatus(200);
        $response3->assertSee('JOG-2610-001');
    }

    public function test_completed_booking_ticket_renders_all_5_steps_and_timestamps(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::firstOrFail();
        $booking->update(['status' => 'completed']);

        RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => $staff->id,
            'type' => 'handover',
            'fuel_level' => 'Full',
            'helmets_count' => 2,
            'raincoats_count' => 1,
            'key_and_stnk_included' => true,
            'physical_id_collected' => true,
            'emergency_contact_verified' => true,
            'remaining_payment_collected' => true,
            'inspected_at' => Carbon::now()->subHours(5),
        ]);

        RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => $staff->id,
            'type' => 'return',
            'fuel_level' => 'Full',
            'helmets_count' => 2,
            'raincoats_count' => 1,
            'key_and_stnk_included' => true,
            'physical_id_collected' => true,
            'remaining_payment_collected' => true,
            'inspected_at' => Carbon::now()->subMinute(),
        ]);

        $response = $this->get("/booking/tiket/{$booking->booking_code}");

        $response->assertStatus(200);
        $response->assertSee('Bukti Reservasi Motor');
        $response->assertSee('Verifikasi DP');
        $response->assertSee('Disetujui');
        $response->assertSee('Diantar (OTW)');
        $response->assertSee('Aktif Digunakan');
        $response->assertSee('Selesai (Kembali)');
        $response->assertSee('Sewa Telah Selesai');
        $response->assertSee('Unit motor telah diserah-terimakan kembali');
    }

    public function test_admin_dashboard_requires_auth(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/login');
    }

    public function test_admin_dashboard_loads_for_admin_user(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
        $response->assertSee('Admin Workspace');
        $response->assertSee('Unit Sedang Disewa');
        $response->assertSee($admin->name);
        $response->assertSee('Keluar / Logout');
    }

    public function test_admin_bookings_index_page_loads_and_supports_search(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();

        // 1. Cek halaman index tanpa filter
        $response = $this->actingAs($admin)->get('/admin/bookings');
        $response->assertStatus(200);
        $response->assertSee('Semua');
        $response->assertSee($booking->booking_code);
        $response->assertSee($booking->customer_name);
        $response->assertSee(route('admin.bookings.show', $booking->id));

        // 2. Cek fitur pencarian kode booking
        $searchResponse = $this->actingAs($admin)->get("/admin/bookings?q={$booking->booking_code}");
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee($booking->customer_name);

        // 3. Cek filter status
        $filterResponse = $this->actingAs($admin)->get('/admin/bookings?status=pending');
        $filterResponse->assertStatus(200);
    }

    public function test_staff_tasks_portal_loads_for_staff(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();

        $response = $this->actingAs($staff)->get('/staff/tasks');

        $response->assertStatus(200);
        $response->assertSee('Portal Lapangan');
        $response->assertSee('Jadwal Pengantaran Motor');
    }

    public function test_admin_can_approve_pending_booking(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::where('status', 'pending')->firstOrFail();

        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/approve", [
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => $staff->id,
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => $staff->id,
        ]);
    }

    public function test_staff_can_submit_handover_checklist_with_emergency_contact_verification(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::firstOrFail();
        $booking->update([
            'status' => 'delivering',
            'delivery_staff_id' => $staff->id,
            'remaining_amount' => 50000,
        ]);

        $response = $this->actingAs($staff)->post("/staff/tasks/{$booking->id}/handover", [
            'fuel_level' => 'Full',
            'helmets_count' => 2,
            'raincoats_count' => 1,
            'key_and_stnk_included' => 1,
            'physical_id_collected' => 1,
            'emergency_contact_verified' => 1,
            'emergency_verification_note' => 'Telah dihubungi via WA, konfirmasi kenal',
            'remaining_payment_collected' => 1,
            'payment_collected_amount' => 50000,
            'payment_collected_method' => 'cash',
        ]);

        $response->assertRedirect('/staff/tasks');
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'active',
            'remaining_amount' => 0,
        ]);
        $this->assertDatabaseHas('rental_inspections', [
            'booking_id' => $booking->id,
            'type' => 'handover',
            'emergency_contact_verified' => 1,
            'emergency_verification_note' => 'Telah dihubungi via WA, konfirmasi kenal',
        ]);
    }

    public function test_customer_can_submit_booking_with_emergency_contact(): void
    {
        Storage::fake('public');

        $category = MotorcycleCategory::firstOrFail();
        $zone = DeliveryZone::firstOrFail();

        $start = Carbon::now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays(4)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');

        $response = $this->post('/booking', [
            'customer_name' => 'Aditya Pratama',
            'customer_phone' => '081299887711',
            'customer_id_type' => 'KTP',
            'customer_id_number' => '3271012345678901',
            'emergency_contact_name' => 'Wahyudi (Kakak)',
            'emergency_contact_phone' => '081233445588',
            'emergency_contact_relation' => 'Saudara Kandung',
            'category_id' => $category->id,
            'start_time' => $start,
            'end_time' => $end,
            'delivery_zone_id' => $zone->id,
            'delivery_address' => 'Stasiun Tugu Pintu Timur',
            'return_zone_id' => $zone->id,
            'return_address' => 'Stasiun Tugu Pintu Timur',
            'down_payment_amount' => 50000,
            'id_card_photo' => UploadedFile::fake()->image('ktp.jpg'),
            'sim_c_photo' => UploadedFile::fake()->image('sim.jpg'),
            'payment_proof' => UploadedFile::fake()->image('transfer.jpg'),
        ]);

        $response->assertSessionHasNoErrors();
        $booking = Booking::where('customer_name', 'Aditya Pratama')->firstOrFail();
        $this->assertEquals('Wahyudi (Kakak)', $booking->emergency_contact_name);
        $this->assertEquals('081233445588', $booking->emergency_contact_phone);
        $this->assertEquals('Saudara Kandung', $booking->emergency_contact_relation);
        $this->assertEquals('pending', $booking->status);
        $response->assertRedirect("/booking/tiket/{$booking->booking_code}");
    }

    public function test_customer_cannot_upload_php_backdoor_or_malicious_script(): void
    {
        Storage::fake('public');

        $category = MotorcycleCategory::firstOrFail();
        $zone = DeliveryZone::firstOrFail();

        $start = Carbon::now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays(4)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');

        // Percobaan unggah backdoor php
        $fakeBackdoor = UploadedFile::fake()->createWithContent('backdoor.php', '<?php system($_GET["cmd"]); ?>');

        $response = $this->post('/booking', [
            'customer_name' => 'Hacker Test',
            'customer_phone' => '081299887711',
            'customer_id_type' => 'KTP',
            'customer_id_number' => '3271012345678901',
            'emergency_contact_name' => 'Wahyudi',
            'emergency_contact_phone' => '081233445588',
            'emergency_contact_relation' => 'Teman',
            'category_id' => $category->id,
            'start_time' => $start,
            'end_time' => $end,
            'delivery_zone_id' => $zone->id,
            'delivery_address' => 'Stasiun Tugu',
            'return_zone_id' => $zone->id,
            'return_address' => 'Stasiun Tugu',
            'down_payment_amount' => 50000,
            'id_card_photo' => $fakeBackdoor,
            'sim_c_photo' => UploadedFile::fake()->image('sim.jpg'),
            'payment_proof' => UploadedFile::fake()->image('transfer.jpg'),
        ]);

        $response->assertSessionHasErrors(['id_card_photo']);
        $this->assertDatabaseMissing('bookings', [
            'customer_name' => 'Hacker Test',
        ]);
        Storage::disk('public')->assertMissing('bookings/id_cards/backdoor.php');
    }

    public function test_admin_can_reject_booking_and_ticket_displays_rejection_reason(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::where('status', 'pending')->firstOrFail();

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/reject", [
            'rejection_reason' => 'Foto KTP buram dan tidak terbaca jelas',
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'rejected',
            'rejection_reason' => 'Foto KTP buram dan tidak terbaca jelas',
        ]);

        $ticketResponse = $this->get("/booking/tiket/{$booking->booking_code}");
        $ticketResponse->assertStatus(200);
        $ticketResponse->assertSee('Ditolak');
        $ticketResponse->assertSee('Alasan Penolakan:');
        $ticketResponse->assertSee('Foto KTP buram dan tidak terbaca jelas');
        $ticketResponse->assertSee('Informasi Pengembalian Dana (DP)');
    }

    public function test_admin_can_update_booking_status_from_rejected_to_approved(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::firstOrFail();
        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => 'KTP buram',
            'payment_status' => 'rejected',
        ]);

        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/update-status", [
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => $staff->id,
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => $staff->id,
            'rejection_reason' => null,
            'payment_status' => 'dp_approved',
        ]);
    }

    public function test_admin_can_update_primary_booking_documents(): void
    {
        Storage::fake('public');

        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();

        $newKtp = UploadedFile::fake()->image('ktp_baru.jpg');
        $newSim = UploadedFile::fake()->image('sim_baru.jpg');
        $newDp = UploadedFile::fake()->image('dp_baru.jpg');

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/update-documents", [
            'id_card_photo' => $newKtp,
            'sim_c_photo' => $newSim,
            'payment_proof' => $newDp,
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $booking->refresh();

        $this->assertNotNull($booking->id_card_photo);
        $this->assertNotNull($booking->sim_c_photo);
        $this->assertNotNull($booking->payment_proof);
        Storage::disk('public')->assertExists($booking->id_card_photo);
        Storage::disk('public')->assertExists($booking->sim_c_photo);
        Storage::disk('public')->assertExists($booking->payment_proof);
    }

    public function test_booking_rejects_files_exceeding_5mb_with_friendly_message(): void
    {
        Storage::fake('public');

        $category = MotorcycleCategory::firstOrFail();
        $zone = DeliveryZone::firstOrFail();

        $start = Carbon::now()->addDays(2)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays(4)->setHour(10)->setMinute(0)->format('Y-m-d H:i:s');

        // File berukuran 6000 KB (> 5120 KB / 5 MB)
        $oversizedFile = UploadedFile::fake()->create('ktp_raksasa.jpg', 6000, 'image/jpeg');

        $response = $this->post('/booking', [
            'customer_name' => 'Ukuran File Test',
            'customer_phone' => '081299887711',
            'customer_id_type' => 'KTP',
            'customer_id_number' => '3271012345678901',
            'emergency_contact_name' => 'Wahyudi',
            'emergency_contact_phone' => '081233445588',
            'emergency_contact_relation' => 'Teman',
            'category_id' => $category->id,
            'start_time' => $start,
            'end_time' => $end,
            'delivery_zone_id' => $zone->id,
            'delivery_address' => 'Stasiun Tugu',
            'return_zone_id' => $zone->id,
            'return_address' => 'Stasiun Tugu',
            'down_payment_amount' => 50000,
            'id_card_photo' => $oversizedFile,
            'sim_c_photo' => UploadedFile::fake()->image('sim.jpg'),
            'payment_proof' => UploadedFile::fake()->image('transfer.jpg'),
        ]);

        $response->assertSessionHasErrors(['id_card_photo']);
        $errors = session('errors')->get('id_card_photo');
        $this->assertStringContainsString('5 MB', $errors[0]);
    }

    public function test_image_optimizer_compresses_and_stores_image(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->image('foto_dokumen.jpg', 2000, 1500);

        $path = ImageOptimizer::compressAndStore($image, 'bookings/test');

        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_admin_booking_detail_displays_field_inspection_photos_and_return_raincoat_info(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::firstOrFail();

        // Buat inspeksi handover dengan foto
        RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => $staff->id,
            'type' => 'handover',
            'fuel_level' => 'Full',
            'helmets_count' => 2,
            'raincoats_count' => 1,
            'key_and_stnk_included' => true,
            'physical_id_collected' => true,
            'emergency_contact_verified' => true,
            'remaining_payment_collected' => true,
            'payment_collected_amount' => 50000,
            'photo_paths' => ['inspections/handover/sample_handover.jpg'],
            'inspected_at' => Carbon::now()->subDay(),
        ]);

        // Buat inspeksi return dengan foto dan jas hujan
        RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => $staff->id,
            'type' => 'return',
            'fuel_level' => '3/4',
            'helmets_count' => 2,
            'raincoats_count' => 1,
            'key_and_stnk_included' => true,
            'physical_id_collected' => false,
            'remaining_payment_collected' => true,
            'payment_collected_amount' => 0,
            'photo_paths' => ['inspections/return/sample_return.jpg'],
            'inspected_at' => Carbon::now(),
        ]);

        $response = $this->actingAs($admin)->get("/admin/bookings/{$booking->id}");

        $response->assertStatus(200);
        $response->assertSee('Hasil Ceklis Lapangan');
        $response->assertSee('Foto Dokumentasi Petugas (Serah Terima)');
        $response->assertSee('sample_handover.jpg');
        $response->assertSee('Foto Dokumentasi Petugas (Pengembalian)');
        $response->assertSee('sample_return.jpg');
        $response->assertSee('Jas Hujan Kembali');
        $response->assertSee('1 Buah Jas Hujan');
    }

    public function test_admin_can_approve_booking_without_manual_staff_assignment(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::where('status', 'pending')->firstOrFail();
        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/approve", [
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => null,
            'return_staff_id' => null,
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => null,
            'return_staff_id' => null,
        ]);
    }

    public function test_staff_can_view_all_open_tasks_and_claim_delivery_task(): void
    {
        $staff1 = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::firstOrFail();
        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        $booking->update([
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => null,
        ]);

        // Staf melihat portal dan melihat tugas belum diambil
        $response = $this->actingAs($staff1)->get('/staff/tasks');
        $response->assertStatus(200);
        $response->assertSee($booking->booking_code);
        $response->assertSee($motor->plate_number);
        $response->assertSee('Belum Ada Petugas');

        // Staf mengklaim tugas antar
        $claimResponse = $this->actingAs($staff1)->post("/staff/tasks/{$booking->id}/claim-delivery");
        $claimResponse->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'delivery_staff_id' => $staff1->id,
        ]);

        // Staf mulai jalan OTW
        $startResponse = $this->actingAs($staff1)->post("/staff/tasks/{$booking->id}/start");
        $startResponse->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'delivering',
            'delivery_staff_id' => $staff1->id,
        ]);
    }

    public function test_delivery_and_return_staff_can_be_different_users(): void
    {
        $staffA = User::where('role', 'staff')->firstOrFail();
        // Buat atau cari staf B kedua
        $staffB = User::firstOrCreate(
            ['email' => 'staff2@sewamotor.com'],
            [
                'name' => 'Petugas Lapangan B',
                'password' => bcrypt('password'),
                'role' => 'staff',
                'phone' => '089999888777',
                'is_active' => true,
            ]
        );

        $booking = Booking::firstOrFail();
        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        // Staf A adalah petugas antar
        $booking->update([
            'status' => 'active',
            'motorcycle_id' => $motor->id,
            'delivery_staff_id' => $staffA->id,
            'return_staff_id' => null,
        ]);

        // Staf B mengklaim tugas jemput
        $claimResponse = $this->actingAs($staffB)->post("/staff/tasks/{$booking->id}/claim-return");
        $claimResponse->assertRedirect();

        // Verifikasi bahwa delivery_staff dan return_staff berbeda
        $booking->refresh();
        $this->assertEquals($staffA->id, $booking->delivery_staff_id);
        $this->assertEquals($staffB->id, $booking->return_staff_id);
        $this->assertNotEquals($booking->delivery_staff_id, $booking->return_staff_id);

        // Staf B mulai jemput OTW
        $startReturnResponse = $this->actingAs($staffB)->post("/staff/tasks/{$booking->id}/start-return");
        $startReturnResponse->assertRedirect();
        $booking->refresh();
        $this->assertEquals('returning', $booking->status);
    }

    public function test_customer_pages_have_whatsapp_admin_button(): void
    {
        // 1. Pengunjung umum / calon penyewa (Guest)
        $homeResponse = $this->get('/');
        $homeResponse->assertStatus(200);
        $homeResponse->assertSee('Hubungi WA Admin');
        $homeResponse->assertSee('https://wa.me/6281234567890', false);
        $homeResponse->assertDontSee('Panel Admin');

        // Halaman cek pesanan penyewa
        $trackResponse = $this->get('/cek-booking');
        $trackResponse->assertStatus(200);
        $trackResponse->assertSee('Hubungi WA Admin');
        $trackResponse->assertSee('https://wa.me/6281234567890', false);
        $trackResponse->assertDontSee('Panel Admin');

        // 2. Bahkan saat login sebagai admin sekalipun, tidak ada akses direct ke panel admin di halaman penyewa
        $admin = User::where('role', 'admin')->firstOrFail();
        $adminHomeResponse = $this->actingAs($admin)->get('/');
        $adminHomeResponse->assertStatus(200);
        $adminHomeResponse->assertSee('Hubungi WA Admin');
        $adminHomeResponse->assertDontSee('Panel Admin');
        $adminHomeResponse->assertDontSee('Dashboard');
    }

    public function test_admin_can_update_status_motorcycle_and_payment_status_without_staff_setting(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();
        $booking->update([
            'status' => 'pending',
            'payment_status' => 'dp_pending_verification',
            'motorcycle_id' => null,
            'delivery_staff_id' => null,
            'return_staff_id' => null,
        ]);

        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        // 1. Admin approve dengan unit motor dan verifikasi DP tanpa setting kurir
        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/update-status", [
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'payment_status' => 'dp_approved',
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'payment_status' => 'dp_approved',
            'delivery_staff_id' => null,
            'return_staff_id' => null,
        ]);

        // 2. Admin konfirmasi pelunasan penuh (paid_full)
        $fullPaymentResponse = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/update-status", [
            'status' => 'approved',
            'motorcycle_id' => $motor->id,
            'payment_status' => 'paid_full',
        ]);

        $fullPaymentResponse->assertRedirect("/admin/bookings/{$booking->id}");
        $this->assertDatabaseHas('bookings', [
            'id' => $booking->id,
            'payment_status' => 'paid_full',
            'remaining_amount' => 0,
        ]);
    }

    public function test_admin_can_reschedule_rejected_booking_with_new_dates_and_motorcycle_and_it_becomes_approved(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();

        // 1. Awalnya booking ditolak dengan alasan penolakan
        $booking->update([
            'status' => 'rejected',
            'payment_status' => 'rejected',
            'rejection_reason' => 'Unit penuh di tanggal tersebut',
            'down_payment_amount' => 50000,
            'delivery_fee' => 15000,
            'return_fee' => 15000,
        ]);

        $category = MotorcycleCategory::firstOrFail();
        $motor = Motorcycle::where('category_id', $category->id)->firstOrFail();

        // Jadwal baru: 2 hari + 2 jam overtime
        $newStart = Carbon::now()->addDays(5)->setHour(9)->setMinute(0);
        $newEnd = Carbon::now()->addDays(7)->setHour(11)->setMinute(0);

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/reschedule", [
            'start_time' => $newStart->format('Y-m-d H:i:s'),
            'end_time' => $newEnd->format('Y-m-d H:i:s'),
            'category_id' => $category->id,
            'motorcycle_id' => $motor->id,
            'target_status' => 'approved',
            'reschedule_note' => 'Tamu konfirmasi ganti hari via WhatsApp',
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");

        $booking->refresh();

        // Verifikasi status berubah jadi approved
        $this->assertEquals('approved', $booking->status);
        $this->assertEquals('dp_approved', $booking->payment_status);
        $this->assertNull($booking->rejection_reason);
        $this->assertEquals($motor->id, $booking->motorcycle_id);
        $this->assertEquals(2, $booking->total_days);
        $this->assertEquals(2, $booking->overtime_hours);

        // Verifikasi kalkulasi keuangan otomatis
        $expectedRentalSubtotal = $category->daily_price * 2;
        $expectedOvertimeFee = $category->hourly_overtime_price * 2;
        $expectedTotal = $expectedRentalSubtotal + $expectedOvertimeFee + 15000 + 15000;
        $expectedRemaining = $expectedTotal - 50000;

        $this->assertEquals((float) $expectedRentalSubtotal, (float) $booking->rental_subtotal);
        $this->assertEquals((float) $expectedOvertimeFee, (float) $booking->overtime_fee);
        $this->assertEquals((float) $expectedTotal, (float) $booking->total_amount);
        $this->assertEquals((float) $expectedRemaining, (float) $booking->remaining_amount);
        $this->assertStringContainsString('Tamu konfirmasi ganti hari via WhatsApp', $booking->notes);
    }

    public function test_admin_booking_detail_renders_guided_actions_for_pending_and_rejected_statuses(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();

        // 1. Kondisi Pending: Tampilkan form persetujuan cepat & tombol tolak
        $booking->update(['status' => 'pending']);
        $pendingResponse = $this->actingAs($admin)->get("/admin/bookings/{$booking->id}");
        $pendingResponse->assertStatus(200);
        $pendingResponse->assertSee('Reservasi Baru Menunggu Keputusan Admin');
        $pendingResponse->assertSee('Konfirmasi Reservasi', false);
        $pendingResponse->assertSee('Tolak Reservasi');

        // 2. Kondisi Rejected: Tampilkan banner penolakan & tombol revisi jadwal
        $booking->update([
            'status' => 'rejected',
            'rejection_reason' => 'Bukti DP buram',
        ]);
        $rejectedResponse = $this->actingAs($admin)->get("/admin/bookings/{$booking->id}");
        $rejectedResponse->assertStatus(200);
        $rejectedResponse->assertSee('Reservasi Sedang Dalam Status Ditolak');
        $rejectedResponse->assertSee('Bukti DP buram');
        $rejectedResponse->assertSee('Revisi Jadwal & Buka Kembali (Setujui)', false);
        $rejectedResponse->assertSee('Revisi Jadwal & Motor Reservasi', false);
    }

    public function test_two_door_payment_admin_confirms_dp_and_adjusts_dp_nominal_for_field_collection(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();
        $booking = Booking::firstOrFail();

        // Tamu reservasi dengan total 100.000 tapi klaim DP 50.000
        $booking->update([
            'status' => 'pending',
            'payment_status' => 'dp_pending_verification',
            'total_amount' => 100000,
            'down_payment_amount' => 50000,
            'remaining_amount' => 50000,
            'motorcycle_id' => null,
            'delivery_staff_id' => null,
        ]);

        $motor = Motorcycle::where('category_id', $booking->category_id)->firstOrFail();

        // 1. Pintu 1: Admin cek rekening ternyata hanya masuk 10.000, admin sesuaikan DP jadi 10.000 & approve
        $approveResponse = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/approve", [
            'motorcycle_id' => $motor->id,
            'down_payment_amount' => 10000,
            'dp_correction_note' => 'Tamu transfer 10rb dan setuju lanjut booking via WA',
        ]);

        $approveResponse->assertRedirect("/admin/bookings/{$booking->id}");
        $booking->refresh();

        $this->assertEquals('approved', $booking->status);
        $this->assertEquals('dp_approved', $booking->payment_status);
        $this->assertEquals(10000, (float) $booking->down_payment_amount);
        $this->assertEquals(90000, (float) $booking->remaining_amount);
        $this->assertStringContainsString('Tamu transfer 10rb dan setuju lanjut booking via WA', $booking->notes);

        // 2. Petugas Lapangan melihat tugas di portal dan melihat sisa tagih Rp 90.000
        $staffTasksResponse = $this->actingAs($staff)->get('/staff/tasks');
        $staffTasksResponse->assertStatus(200);
        $staffTasksResponse->assertSee('90.000');

        // 3. Pintu 2: Petugas Lapangan serah terima unit motor & menagih sisa 90.000
        $handoverResponse = $this->actingAs($staff)->post("/staff/tasks/{$booking->id}/handover", [
            'fuel_level' => 'Full',
            'helmets_count' => 2,
            'raincoats_count' => 1,
            'key_and_stnk_included' => true,
            'physical_id_collected' => true,
            'emergency_contact_verified' => true,
            'emergency_verification_note' => 'Dikonfirmasi kenal penyewa',
            'remaining_payment_collected' => true,
            'payment_collected_amount' => 90000,
            'payment_collected_method' => 'cash',
        ]);

        $handoverResponse->assertRedirect(route('staff.tasks'));
        $booking->refresh();

        // Setelah serah terima, status menjadi active dan pembayaran lunas penuh
        $this->assertEquals('active', $booking->status);
        $this->assertEquals('paid_full', $booking->payment_status);
        $this->assertEquals(0, (float) $booking->remaining_amount);
    }

    public function test_admin_can_update_dp_amount_via_update_dp_route_and_updates_remaining_amount(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking = Booking::firstOrFail();

        $booking->update([
            'status' => 'approved',
            'payment_status' => 'dp_approved',
            'total_amount' => 200000,
            'down_payment_amount' => 50000,
            'remaining_amount' => 150000,
        ]);

        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking->id}/update-dp", [
            'down_payment_amount' => 70000,
            'dp_correction_note' => 'Koreksi transfer tambahan',
        ]);

        $response->assertRedirect("/admin/bookings/{$booking->id}");
        $booking->refresh();

        $this->assertEquals(70000, (float) $booking->down_payment_amount);
        $this->assertEquals(130000, (float) $booking->remaining_amount);
        $this->assertStringContainsString('Koreksi transfer tambahan', $booking->notes);
    }

    public function test_admin_cannot_approve_booking_with_motorcycle_having_schedule_conflict(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $booking1 = Booking::firstOrFail();
        $motorcycle = Motorcycle::where('category_id', $booking1->category_id)->firstOrFail();

        $startTime = Carbon::now()->addDays(10)->setTime(9, 0);
        $endTime = Carbon::now()->addDays(12)->setTime(9, 0);

        // Booking 1 sudah approved memegang unit motor tersebut pada jadwal ini
        $booking1->update([
            'status' => 'approved',
            'motorcycle_id' => $motorcycle->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ]);

        // Booking 2 masih pending untuk rentang tanggal/jam yang bentrok
        $booking2 = Booking::where('id', '!=', $booking1->id)->firstOrFail();
        $booking2->update([
            'status' => 'pending',
            'motorcycle_id' => null,
            'category_id' => $booking1->category_id,
            'start_time' => $startTime->copy()->addHours(2),
            'end_time' => $endTime->copy()->addHours(2),
        ]);

        // Admin mencoba menyetujui booking 2 dengan motor yang sama -> HARUS DITOLAK
        $response = $this->actingAs($admin)->post("/admin/bookings/{$booking2->id}/approve", [
            'motorcycle_id' => $motorcycle->id,
            'down_payment_amount' => 50000,
        ]);

        $response->assertSessionHas('error');
        $booking2->refresh();
        $this->assertEquals('pending', $booking2->status);
        $this->assertNull($booking2->motorcycle_id);
    }

    public function test_admin_can_set_motorcycle_default_accessories(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $category = MotorcycleCategory::firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/motorcycles', [
            'category_id' => $category->id,
            'plate_number' => 'AB 9999 TEST',
            'color' => 'Hitam',
            'year' => 2024,
            'default_helmets_count' => 1,
            'default_raincoats_count' => 2,
            'default_key_and_stnk' => 1,
            'status' => 'available',
        ]);

        $response->assertRedirect();
        $motorcycle = Motorcycle::where('plate_number', 'AB 9999 TEST')->firstOrFail();
        $this->assertEquals(1, $motorcycle->default_helmets_count);
        $this->assertEquals(2, $motorcycle->default_raincoats_count);
        $this->assertTrue($motorcycle->default_key_and_stnk);

        // Update default accessories
        $updateResponse = $this->actingAs($admin)->put("/admin/motorcycles/{$motorcycle->id}", [
            'category_id' => $category->id,
            'plate_number' => 'AB 9999 TEST',
            'color' => 'Hitam',
            'year' => 2024,
            'default_helmets_count' => 2,
            'default_raincoats_count' => 0,
            'default_key_and_stnk' => 0,
            'status' => 'available',
        ]);

        $updateResponse->assertRedirect();
        $motorcycle->refresh();
        $this->assertEquals(2, $motorcycle->default_helmets_count);
        $this->assertEquals(0, $motorcycle->default_raincoats_count);
        $this->assertFalse($motorcycle->default_key_and_stnk);
    }

    public function test_handover_form_uses_motorcycle_default_accessories(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();
        $motorcycle = Motorcycle::firstOrFail();
        $motorcycle->update([
            'default_helmets_count' => 1,
            'default_raincoats_count' => 2,
            'default_key_and_stnk' => true,
        ]);

        $booking = Booking::firstOrFail();
        $booking->update([
            'status' => 'delivering',
            'delivery_staff_id' => $staff->id,
            'motorcycle_id' => $motorcycle->id,
        ]);

        $response = $this->actingAs($staff)->get("/staff/tasks/{$booking->id}/handover");

        $response->assertStatus(200);
        $response->assertSee('Bawaan fisik motor: 1 Helm • 2 Jas Hujan');
        $response->assertSee('1 Buah Helm (Standar Unit Ini)');
        $response->assertSee('2 Jas Hujan (Standar Unit Ini)');
    }

    public function test_return_form_uses_handover_inspection_baseline_instead_of_motorcycle_default(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();

        // Unit motor memiliki default di DB: 2 helm dan 1 jas hujan
        $motorcycle = Motorcycle::firstOrFail();
        $motorcycle->update([
            'default_helmets_count' => 2,
            'default_raincoats_count' => 1,
            'default_key_and_stnk' => true,
        ]);

        $booking = Booking::firstOrFail();
        $booking->update([
            'status' => 'returning',
            'return_staff_id' => $staff->id,
            'motorcycle_id' => $motorcycle->id,
        ]);

        // Namun saat serah terima pengantaran kemarin, tamu hanya membawa 1 helm dan membawa 2 jas hujan!
        RentalInspection::create([
            'booking_id' => $booking->id,
            'staff_id' => $staff->id,
            'type' => 'handover',
            'fuel_level' => '3/4',
            'helmets_count' => 1, // Bukan 2 dari default DB
            'raincoats_count' => 2, // Bukan 1 dari default DB
            'key_and_stnk_included' => true,
            'physical_id_collected' => true,
            'emergency_contact_verified' => true,
            'remaining_payment_collected' => true,
            'payment_collected_amount' => 0,
            'inspected_at' => Carbon::now()->subDay(),
        ]);

        $response = $this->actingAs($staff)->get("/staff/tasks/{$booking->id}/return");

        $response->assertStatus(200);
        // Harus menampilkan acuan dari serah terima pengantaran kemarin: 1 Helm dan 2 Jas Hujan
        $response->assertSee('Helm Diserahkan');
        $response->assertSee('1 Buah');
        $response->assertSee('Jas Hujan Diserahkan');
        $response->assertSee('2 Buah');
        $response->assertSee('Bensin Awal');
        $response->assertSee('3/4');

        // Dan dropdown nilai lengkap harus mengacu pada pengantaran tadi (1 Helm Lengkap, 2 Jas Hujan Lengkap)
        $response->assertSee('1 Buah Helm (Lengkap - Sesuai Serah Terima)');
        $response->assertSee('2 Jas Hujan (Lengkap - Sesuai Serah Terima)');
    }
}
