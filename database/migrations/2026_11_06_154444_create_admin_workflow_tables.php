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
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->decimal('cost_price', 12, 2)->nullable();
            $table->unsignedInteger('stock_quantity')->nullable();
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('admin_role')->nullable()->index();
        });

        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('catalog_products')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->integer('quantity_change');
            $table->unsignedInteger('quantity_before')->nullable();
            $table->unsignedInteger('quantity_after');
            $table->string('reason', 190);
            $table->timestamps();
            $table->index(['product_id', 'created_at']);
        });

        Schema::create('installation_bookings', function (Blueprint $table) {
            $table->id();
            $table->string('customer_name');
            $table->string('email');
            $table->string('phone', 40);
            $table->string('vehicle_make', 100);
            $table->string('vehicle_model', 120);
            $table->unsignedSmallInteger('vehicle_year')->nullable();
            $table->string('service', 190);
            $table->dateTime('scheduled_at')->nullable();
            $table->string('location')->nullable();
            $table->string('technician')->nullable();
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['scheduled_at', 'technician']);
        });

        Schema::create('admin_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100);
            $table->string('subject_type', 120)->nullable();
            $table->string('subject_id', 120)->nullable();
            $table->string('summary', 500);
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['subject_type', 'subject_id']);
            $table->index(['user_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_activity_logs');
        Schema::dropIfExists('installation_bookings');
        Schema::dropIfExists('inventory_movements');
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['admin_role']);
            $table->dropColumn('admin_role');
        });
        Schema::table('catalog_products', function (Blueprint $table) {
            $table->dropColumn(['cost_price', 'stock_quantity', 'low_stock_threshold', 'meta_title', 'meta_description']);
        });
    }
};
