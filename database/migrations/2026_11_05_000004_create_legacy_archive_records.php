<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('legacy_archive_records', function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 64);
            $table->string('source_id', 128);
            $table->longText('payload');
            $table->timestamps();
            $table->unique(['source_table', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legacy_archive_records');
    }
};
