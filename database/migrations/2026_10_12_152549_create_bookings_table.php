<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code')->unique();

            // Pelanggan & Kontak Darurat
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_id_type')->default('KTP');
            $table->string('customer_id_number')->nullable();
            $table->string('emergency_contact_name');
            $table->string('emergency_contact_phone');
            $table->string('emergency_contact_relation');

            // Verifikasi Berkas
            $table->string('id_card_photo')->nullable();
            $table->string('sim_c_photo')->nullable();
            $table->string('travel_ticket_photo')->nullable();
            $table->string('hotel_booking_photo')->nullable();
            $table->string('social_media_account')->nullable();

            // Unit & Kategori
            $table->foreignId('category_id')->constrained('motorcycle_categories')->restrictOnDelete();
            $table->foreignId('motorcycle_id')->nullable()->constrained('motorcycles')->nullOnDelete();

            // Waktu Rental (24 jam)
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->unsignedInteger('total_days')->default(1);
            $table->unsignedInteger('overtime_hours')->default(0);

            // Titik Antar & Jemput
            $table->foreignId('delivery_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->text('delivery_address')->nullable();
            $table->foreignId('return_zone_id')->nullable()->constrained('delivery_zones')->nullOnDelete();
            $table->text('return_address')->nullable();

            // Biaya & Pembayaran
            $table->decimal('daily_price', 12, 2);
            $table->decimal('rental_subtotal', 12, 2);
            $table->decimal('overtime_fee', 12, 2)->default(0);
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->decimal('return_fee', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('down_payment_amount', 12, 2)->default(0);
            $table->decimal('remaining_amount', 12, 2)->default(0);

            $table->string('payment_status')->default('dp_pending_verification'); // dp_pending_verification, dp_approved, paid_full, rejected
            $table->string('payment_proof')->nullable();

            // Status Booking
            $table->string('status')->default('pending'); // pending, approved, delivering, active, returning, completed, rejected, cancelled
            $table->text('rejection_reason')->nullable();

            // Petugas
            $table->foreignId('delivery_staff_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('return_staff_id')->nullable()->constrained('users')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
