<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seasonal_prices', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. "Libur Lebaran Idul Fitri", "Tahun Baru & Natal"
            $table->date('start_date');
            $table->date('end_date');
            $table->foreignId('category_id')->nullable()->constrained('motorcycle_categories')->cascadeOnDelete(); // null = berlaku untuk semua kategori motor
            $table->string('price_type')->default('surcharge'); // surcharge (penambahan dari tarif normal) atau fixed (tarif mutlak per hari)
            $table->decimal('amount', 12, 2); // nominal tambahan (misal 25000) atau tarif mutlak (misal 130000)
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seasonal_prices');
    }
};
