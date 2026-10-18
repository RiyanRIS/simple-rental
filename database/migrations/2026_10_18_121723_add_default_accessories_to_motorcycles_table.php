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
        Schema::table('motorcycles', function (Blueprint $table) {
            $table->unsignedSmallInteger('default_helmets_count')->default(2)->after('year');
            $table->unsignedSmallInteger('default_raincoats_count')->default(1)->after('default_helmets_count');
            $table->boolean('default_key_and_stnk')->default(true)->after('default_raincoats_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motorcycles', function (Blueprint $table) {
            $table->dropColumn(['default_helmets_count', 'default_raincoats_count', 'default_key_and_stnk']);
        });
    }
};
