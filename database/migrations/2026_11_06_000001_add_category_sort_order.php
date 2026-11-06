<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->index();
        });

        DB::table('catalog_categories')->orderBy('name')->orderBy('id')->get(['id'])->values()->each(function ($category, $index) {
            DB::table('catalog_categories')->where('id', $category->id)->update(['sort_order' => $index + 1]);
        });
    }

    public function down(): void
    {
        Schema::table('catalog_categories', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
