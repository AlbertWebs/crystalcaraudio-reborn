<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('catalog_categories')
            ->where('name', 'Car Speakers')
            ->update(['slug' => 'car-speakers']);
    }

    public function down(): void
    {
        DB::table('catalog_categories')
            ->where('name', 'Car Speakers')
            ->where('slug', 'car-speakers')
            ->update(['slug' => 'mid-range-speakers']);
    }
};
