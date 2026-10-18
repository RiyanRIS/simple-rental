<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_guest_is_redirected_to_login_when_accessing_users_page(): void
    {
        $response = $this->get('/admin/users');

        $response->assertRedirect('/login');
    }

    public function test_staff_cannot_access_user_management(): void
    {
        $staff = User::where('role', 'staff')->firstOrFail();

        $response = $this->actingAs($staff)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_view_user_management_index_and_filters(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();

        // 1. Halaman utama
        $response = $this->actingAs($admin)->get('/admin/users');
        $response->assertStatus(200);
        $response->assertSee('Kelola Pengguna');
        $response->assertSee($admin->name);
        $response->assertSee($staff->name);
        $response->assertSee('Admin Rental');
        $response->assertSee('Staf Lapangan');

        // 2. Filter role
        $filterResponse = $this->actingAs($admin)->get('/admin/users?role=staff');
        $filterResponse->assertStatus(200);
        $filterResponse->assertSee($staff->name);

        // 3. Pencarian
        $searchResponse = $this->actingAs($admin)->get("/admin/users?q={$staff->email}");
        $searchResponse->assertStatus(200);
        $searchResponse->assertSee($staff->name);
    }

    public function test_admin_can_create_new_staff_user(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Doni Wahyudi',
            'email' => 'doni@rentaljogja.com',
            'phone' => '087811223344',
            'role' => 'staff',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'is_active' => '1',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'name' => 'Doni Wahyudi',
            'email' => 'doni@rentaljogja.com',
            'phone' => '087811223344',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $newUser = User::where('email', 'doni@rentaljogja.com')->firstOrFail();
        $this->assertTrue(Hash::check('secret123', $newUser->password));
    }

    public function test_admin_can_update_existing_user(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();

        $response = $this->actingAs($admin)->put("/admin/users/{$staff->id}", [
            'name' => 'Budi Santoso Lapangan (Updated)',
            'email' => $staff->email,
            'phone' => '081299998888',
            'role' => 'staff',
            'is_active' => '1',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'name' => 'Budi Santoso Lapangan (Updated)',
            'phone' => '081299998888',
        ]);
    }

    public function test_admin_cannot_deactivate_or_demote_themselves(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        // Coba nonaktifkan akun sendiri
        $response = $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'role' => 'admin',
            'is_active' => '0',
        ]);

        $response->assertSessionHas('error');
        $this->assertTrue($admin->fresh()->is_active);

        // Coba turunkan role sendiri menjadi staff
        $demoteResponse = $this->actingAs($admin)->put("/admin/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'role' => 'staff',
            'is_active' => '1',
        ]);

        $demoteResponse->assertSessionHas('error');
        $this->assertEquals('admin', $admin->fresh()->role);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_cannot_delete_user_with_assigned_booking_history(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();
        $staff = User::where('role', 'staff')->firstOrFail();

        // Hubungkan staf ke sebuah booking
        $booking = Booking::firstOrFail();
        $booking->update(['delivery_staff_id' => $staff->id]);

        $response = $this->actingAs($admin)->delete("/admin/users/{$staff->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
        ]);
    }

    public function test_admin_can_delete_unused_user(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $temporaryUser = User::create([
            'name' => 'Temporary Staff',
            'email' => 'temp@rentaljogja.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '08999999999',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->delete("/admin/users/{$temporaryUser->id}");

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('users', [
            'id' => $temporaryUser->id,
        ]);
    }
}
