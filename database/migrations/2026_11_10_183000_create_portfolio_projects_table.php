<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('portfolio_projects', function (Blueprint $table) {
            $table->id();
            $table->string('legacy_source_id', 128)->nullable()->unique();
            $table->string('title', 190);
            $table->string('slug', 190)->unique();
            $table->string('vehicle_make', 100)->nullable();
            $table->string('vehicle_model', 160)->nullable();
            $table->unsignedSmallInteger('vehicle_year')->nullable();
            $table->string('work_order', 100)->nullable();
            $table->string('upgrade_type', 190)->nullable();
            $table->date('project_date')->nullable();
            $table->string('service_area', 190)->nullable();
            $table->string('client_name', 190)->nullable();
            $table->longText('description')->nullable();
            $table->json('images')->nullable();
            $table->string('video_path')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('portfolio_projects');
    }
};
