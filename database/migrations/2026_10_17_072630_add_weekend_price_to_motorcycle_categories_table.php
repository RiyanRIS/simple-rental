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
        Schema::table('motorcycle_categories', function (Blueprint $table) {
            $table->decimal('weekend_price', 12, 2)->nullable()->after('daily_price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motorcycle_categories', function (Blueprint $table) {
            $table->dropColumn('weekend_price');
        });
    }
};
