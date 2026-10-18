<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambah kolom brand di motorcycle_categories
        Schema::table('motorcycle_categories', function (Blueprint $table) {
            $table->string('brand')->default('Honda')->after('name');
        });

        // 2. Data migration: salin brand dari unit motor yang ada ke kategorinya (atau ekstrak kata pertama dari name)
        $categories = DB::table('motorcycle_categories')->get();
        foreach ($categories as $cat) {
            $brand = 'Honda';
            // Cek apakah ada unit motor di kategori ini yang punya brand
            $firstMotor = DB::table('motorcycles')
                ->where('category_id', $cat->id)
                ->whereNotNull('brand')
                ->first();

            if ($firstMotor && ! empty($firstMotor->brand)) {
                $brand = $firstMotor->brand;
            } else {
                $parts = explode(' ', trim($cat->name));
                $brand = $parts[0] ?? 'Honda';
            }

            DB::table('motorcycle_categories')
                ->where('id', $cat->id)
                ->update(['brand' => $brand]);
        }

        // 3. Hapus kolom brand di motorcycles
        Schema::table('motorcycles', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('motorcycles', function (Blueprint $table) {
            $table->string('brand')->default('Honda')->after('plate_number');
        });

        $categories = DB::table('motorcycle_categories')->get();
        foreach ($categories as $cat) {
            DB::table('motorcycles')
                ->where('category_id', $cat->id)
                ->update(['brand' => $cat->brand ?? 'Honda']);
        }

        Schema::table('motorcycle_categories', function (Blueprint $table) {
            $table->dropColumn('brand');
        });
    }
};
