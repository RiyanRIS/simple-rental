<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\DeliveryZone;
use App\Models\Motorcycle;
use App\Models\MotorcycleCategory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun Admin dan Petugas Lapangan
        $admin = User::create([
            'name' => 'Admin Rental',
            'email' => 'admin@rentaljogja.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        $budi = User::create([
            'name' => 'Budi Santoso (Petugas Lapangan)',
            'email' => 'budi@rentaljogja.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '081298765432',
            'is_active' => true,
        ]);

        $agus = User::create([
            'name' => 'Agus Pratama (Petugas Lapangan)',
            'email' => 'agus@rentaljogja.com',
            'password' => Hash::make('password'),
            'role' => 'staff',
            'phone' => '085612345678',
            'is_active' => true,
        ]);

        // 2. Zona Antar-Jemput
        $zoneTugu = DeliveryZone::create([
            'name' => 'Stasiun Tugu Yogyakarta (Pintu Selatan/Timur)',
            'fee' => 0,
            'description' => 'Titik kumpul gratis di Stasiun Tugu',
            'is_free' => true,
            'is_active' => true,
        ]);

        $zoneLempuyangan = DeliveryZone::create([
            'name' => 'Stasiun Lempuyangan (Pintu Depan)',
            'fee' => 0,
            'description' => 'Titik kumpul gratis di Stasiun Lempuyangan',
            'is_free' => true,
            'is_active' => true,
        ]);

        $zoneGarasi = DeliveryZone::create([
            'name' => 'Ambil Sendiri di Garasi / Kantor Pusat',
            'fee' => 0,
            'description' => 'Jl. Sosrowijayan No. 12, Malioboro, Yogyakarta',
            'is_free' => true,
            'is_active' => true,
        ]);

        $zoneRingroad = DeliveryZone::create([
            'name' => 'Zona 1: Area Dalam Ring Road / Hotel Malioboro / Prawirotaman',
            'fee' => 15000,
            'description' => 'Antar/jemput ke hotel, homestay, atau stasiun/terminal dalam kota',
            'is_free' => false,
            'is_active' => true,
        ]);

        $zoneLuarRingroad = DeliveryZone::create([
            'name' => 'Zona 2: Luar Ring Road Dekat (Seturan, Gejayan, Jombor, UGM)',
            'fee' => 25000,
            'description' => 'Area Sleman selatan / Bantul utara',
            'is_free' => false,
            'is_active' => true,
        ]);

        $zoneSlemanBantul = DeliveryZone::create([
            'name' => 'Zona 3: Sleman Utara / Bantul Kota / Kasihan',
            'fee' => 45000,
            'description' => 'Radius lebih dari 10 km dari pusat kota Jogja',
            'is_free' => false,
            'is_active' => true,
        ]);

        $zoneYIA = DeliveryZone::create([
            'name' => 'Zona 4: Bandara Internasional YIA (Kulon Progo)',
            'fee' => 120000,
            'description' => 'Antar / jemput langsung di lobi kedatangan Bandara YIA',
            'is_free' => false,
            'is_active' => true,
        ]);

        // 3. Kategori Motor
        $catBeat = MotorcycleCategory::create([
            'name' => 'Honda BeAT 110cc All New',
            'brand' => 'Honda',
            'slug' => 'honda-beat-110cc',
            'cc_capacity' => 110,
            'daily_price' => 80000,
            'hourly_overtime_price' => 10000,
            'description' => 'Paling hemat bahan bakar, ramping, dan lincah untuk keliling wisata kota Jogja.',
            'image' => 'beat.png',
            'image_thumbnail' => 'beat_thumb.png',
            'is_active' => true,
        ]);

        $catVario = MotorcycleCategory::create([
            'name' => 'Honda Vario 125cc eSP ISS',
            'brand' => 'Honda',
            'slug' => 'honda-vario-125cc',
            'cc_capacity' => 125,
            'daily_price' => 95000,
            'hourly_overtime_price' => 10000,
            'description' => 'Bagasi lega, tarikan bertenaga, sangat nyaman untuk berboncengan jarak menengah.',
            'image' => 'vario.png',
            'image_thumbnail' => 'vario_thumb.png',
            'is_active' => true,
        ]);

        $catScoopy = MotorcycleCategory::create([
            'name' => 'Honda Scoopy 110cc Smart Key',
            'brand' => 'Honda',
            'slug' => 'honda-scoopy-110cc',
            'cc_capacity' => 110,
            'daily_price' => 90000,
            'hourly_overtime_price' => 10000,
            'description' => 'Desain retro estetik, keyless smart key, sangat digemari wisatawan untuk hunting foto.',
            'image' => 'scoopy.png',
            'image_thumbnail' => 'scoopy_thumb.png',
            'is_active' => true,
        ]);

        $catNmax = MotorcycleCategory::create([
            'name' => 'Yamaha NMAX 155cc Connected',
            'brand' => 'Yamaha',
            'slug' => 'yamaha-nmax-155cc',
            'cc_capacity' => 155,
            'daily_price' => 140000,
            'hourly_overtime_price' => 15000,
            'description' => 'Maxi scooter tangguh, bertenaga untuk rute pantai Gunungkidul atau Kaliurang/Merapi.',
            'image' => 'nmax.png',
            'image_thumbnail' => 'nmax_thumb.png',
            'is_active' => true,
        ]);

        $catPcx = MotorcycleCategory::create([
            'name' => 'Honda PCX 160cc ABS',
            'brand' => 'Honda',
            'slug' => 'honda-pcx-160cc',
            'cc_capacity' => 160,
            'daily_price' => 150000,
            'hourly_overtime_price' => 15000,
            'description' => 'Kenyamanan kelas atas, suspensi empuk, posisi berkendara sangat rileks.',
            'image' => 'pcx.png',
            'image_thumbnail' => 'pcx_thumb.png',
            'is_active' => true,
        ]);

        // 4. Unit Motor Fisik
        $unitBeat1 = Motorcycle::create(['category_id' => $catBeat->id, 'plate_number' => 'AB 2041 YK', 'color' => 'Hitam', 'year' => 2024, 'status' => 'available']);
        $unitBeat2 = Motorcycle::create(['category_id' => $catBeat->id, 'plate_number' => 'AB 4821 ZA', 'color' => 'Merah', 'year' => 2023, 'status' => 'available']);
        $unitBeat3 = Motorcycle::create(['category_id' => $catBeat->id, 'plate_number' => 'AB 3190 BX', 'color' => 'Silver', 'year' => 2024, 'status' => 'available']);

        $unitVario1 = Motorcycle::create(['category_id' => $catVario->id, 'plate_number' => 'AB 5102 QW', 'color' => 'Hitam Doff', 'year' => 2024, 'status' => 'available']);
        $unitVario2 = Motorcycle::create(['category_id' => $catVario->id, 'plate_number' => 'AB 6319 PL', 'color' => 'Putih', 'year' => 2023, 'status' => 'available']);
        $unitVario3 = Motorcycle::create(['category_id' => $catVario->id, 'plate_number' => 'AB 4022 KJ', 'color' => 'Biru', 'year' => 2023, 'status' => 'available']);

        $unitScoopy1 = Motorcycle::create(['category_id' => $catScoopy->id, 'plate_number' => 'AB 3381 SC', 'color' => 'Prestige White', 'year' => 2024, 'status' => 'available']);
        $unitScoopy2 = Motorcycle::create(['category_id' => $catScoopy->id, 'plate_number' => 'AB 5509 QY', 'color' => 'Fashion Blue', 'year' => 2023, 'status' => 'available']);

        $unitNmax1 = Motorcycle::create(['category_id' => $catNmax->id, 'plate_number' => 'AB 2844 NM', 'color' => 'Matte Black', 'year' => 2024, 'status' => 'available']);
        $unitNmax2 = Motorcycle::create(['category_id' => $catNmax->id, 'plate_number' => 'AB 3912 MAX', 'color' => 'Matte Green', 'year' => 2023, 'status' => 'available']);

        $unitPcx1 = Motorcycle::create(['category_id' => $catPcx->id, 'plate_number' => 'AB 6112 PC', 'color' => 'Glorious Matte Black', 'year' => 2024, 'status' => 'available']);
        $unitPcx2 = Motorcycle::create(['category_id' => $catPcx->id, 'plate_number' => 'AB 7014 XA', 'color' => 'Wonderful White', 'year' => 2023, 'status' => 'available']);

        // 5. Contoh Booking
        // Booking 1: Disetujui & Siap Diantar oleh Budi Santoso (Tugas Antar Hari Ini)
        $startToday = Carbon::now()->setHour(10)->setMinute(0);
        $endTomorrow = Carbon::now()->addDays(2)->setHour(10)->setMinute(0);

        Booking::create([
            'booking_code' => 'JOG-2610-001',
            'customer_name' => 'Dimas Surya Wijaya',
            'customer_phone' => '081234455667',
            'customer_id_type' => 'KTP',
            'customer_id_number' => '3273012903950002',
            'emergency_contact_name' => 'Bambang Wijaya',
            'emergency_contact_phone' => '081399887766',
            'emergency_contact_relation' => 'Orang Tua (Ayah)',
            'category_id' => $catBeat->id,
            'motorcycle_id' => $unitBeat1->id,
            'start_time' => $startToday,
            'end_time' => $endTomorrow,
            'total_days' => 2,
            'overtime_hours' => 0,
            'delivery_zone_id' => $zoneTugu->id,
            'delivery_address' => 'Pintu Selatan Stasiun Tugu, depan Indomaret Point',
            'return_zone_id' => $zoneTugu->id,
            'return_address' => 'Pintu Selatan Stasiun Tugu',
            'daily_price' => 80000,
            'rental_subtotal' => 160000,
            'overtime_fee' => 0,
            'delivery_fee' => 0,
            'return_fee' => 0,
            'total_amount' => 160000,
            'down_payment_amount' => 50000,
            'remaining_amount' => 110000,
            'payment_status' => 'dp_approved',
            'payment_proof' => 'sample_proof.jpg',
            'status' => 'approved',
            'delivery_staff_id' => $budi->id,
            'return_staff_id' => $budi->id,
            'notes' => 'Tamu tiba naik KA Taksaka jam 09.45 pagi. Bawa 2 helm (1 pria, 1 wanita).',
        ]);

        // Booking 2: Menunggu Verifikasi Admin (Pending)
        Booking::create([
            'booking_code' => 'JOG-2610-002',
            'customer_name' => 'Sarah Melinda',
            'customer_phone' => '087811223344',
            'customer_id_type' => 'KTP',
            'customer_id_number' => '3171054408990003',
            'emergency_contact_name' => 'Rina Kartika',
            'emergency_contact_phone' => '081233445566',
            'emergency_contact_relation' => 'Saudara Kandung',
            'category_id' => $catVario->id,
            'motorcycle_id' => null, // Belum di-assign
            'start_time' => Carbon::now()->addDay()->setHour(9)->setMinute(0),
            'end_time' => Carbon::now()->addDays(4)->setHour(12)->setMinute(0), // 3 hari + 3 jam overtime
            'total_days' => 3,
            'overtime_hours' => 3,
            'delivery_zone_id' => $zoneRingroad->id,
            'delivery_address' => 'Hotel Tentrem Yogyakarta, Lobby Utama',
            'return_zone_id' => $zoneLempuyangan->id,
            'return_address' => 'Drop zone Stasiun Lempuyangan',
            'daily_price' => 95000,
            'rental_subtotal' => 285000,
            'overtime_fee' => 30000, // 3 * 10.000
            'delivery_fee' => 15000,
            'return_fee' => 0,
            'total_amount' => 330000,
            'down_payment_amount' => 100000,
            'remaining_amount' => 230000,
            'payment_status' => 'dp_pending_verification',
            'payment_proof' => 'sample_transfer.jpg',
            'status' => 'pending',
            'notes' => 'Pesan untuk liburan keluarga di Jogja.',
        ]);
    }
}
