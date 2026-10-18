<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('users')->cascadeOnDelete();
            $table->string('type'); // 'handover' (antar), 'return' (jemput)

            $table->string('fuel_level')->default('Full'); // E, 1/4, 1/2, 3/4, Full
            $table->unsignedSmallInteger('helmets_count')->default(2);
            $table->unsignedSmallInteger('raincoats_count')->default(1);
            $table->boolean('key_and_stnk_included')->default(true);
            $table->boolean('physical_id_collected')->default(false);
            $table->boolean('emergency_contact_verified')->default(false); // Bukti kontak darurat WA terkonfirmasi kenal penyewa
            $table->string('emergency_verification_note')->nullable();
            $table->boolean('remaining_payment_collected')->default(false);
            $table->decimal('payment_collected_amount', 12, 2)->default(0);
            $table->string('payment_collected_method')->nullable(); // cash, qris, transfer

            $table->text('damage_notes')->nullable();
            $table->json('photo_paths')->nullable();
            $table->text('customer_signature')->nullable();

            $table->dateTime('inspected_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_inspections');
    }
};
