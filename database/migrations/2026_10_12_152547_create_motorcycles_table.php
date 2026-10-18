<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motorcycles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('motorcycle_categories')->cascadeOnDelete();
            $table->string('plate_number')->unique();
            $table->string('brand')->default('Honda');
            $table->string('color')->nullable();
            $table->year('year')->nullable();
            $table->string('status')->default('available'); // available, rented, maintenance
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motorcycles');
    }
};
