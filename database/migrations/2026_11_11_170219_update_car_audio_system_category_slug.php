<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('catalog_categories')
            ->where('name', 'Car Audio System')
            ->update(['slug' => 'car-audio-system']);
    }

    public function down(): void
    {
        DB::table('catalog_categories')
            ->where('name', 'Car Audio System')
            ->where('slug', 'car-audio-system')
            ->update(['slug' => 'car-security-system']);
    }
};
