<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Your project already has create_vehicles_table (2024_10_25_070154).
     * This migration ADDS the transport-specific columns to that table.
     */
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {

            if (!Schema::hasColumn('vehicles', 'vehicle_category_id')) {
                $table->unsignedBigInteger('vehicle_category_id')->nullable()->after('id');
                $table->foreign('vehicle_category_id', 'v_category_fk')
                      ->references('id')->on('vehicle_categories')->nullOnDelete();
            }

            if (!Schema::hasColumn('vehicles', 'vehicle_number')) {
                $table->string('vehicle_number', 20)->unique()->after('vehicle_category_id');
            }

            if (!Schema::hasColumn('vehicles', 'vehicle_name')) {
                $table->string('vehicle_name', 100)->nullable()->after('vehicle_number');
            }

            if (!Schema::hasColumn('vehicles', 'make')) {
                $table->string('make', 100)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'model')) {
                $table->string('model', 100)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'year_of_manufacture')) {
                $table->year('year_of_manufacture')->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'capacity_litres')) {
                $table->decimal('capacity_litres', 10, 2)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->enum('fuel_type', ['diesel', 'petrol', 'electric', 'cng'])->default('diesel');
            }

            if (!Schema::hasColumn('vehicles', 'ownership_type')) {
                $table->enum('ownership_type', ['own', 'hired', 'leased'])->default('own');
            }

            if (!Schema::hasColumn('vehicles', 'supplier_transporter_id')) {
                $table->unsignedBigInteger('supplier_transporter_id')->nullable();
                // FK to supplier_transporters added in migration 100003
                // after that table is created
            }

            if (!Schema::hasColumn('vehicles', 'driver_name')) {
                $table->string('driver_name', 100)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'driver_phone')) {
                $table->string('driver_phone', 15)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'rc_number')) {
                $table->string('rc_number', 50)->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'rc_expiry_date')) {
                $table->date('rc_expiry_date')->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'status')) {
                $table->enum('status', ['active', 'inactive', 'in_service'])->default('active');
            }

            if (!Schema::hasColumn('vehicles', 'remarks')) {
                $table->text('remarks')->nullable();
            }

            if (!Schema::hasColumn('vehicles', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->foreign('created_by', 'v_created_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by', 'v_updated_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Drop FKs before columns
            $table->dropForeign('v_category_fk');
            $table->dropForeign('v_created_by_fk');
            $table->dropForeign('v_updated_by_fk');

            $table->dropColumn([
                'vehicle_category_id', 'vehicle_number', 'vehicle_name',
                'make', 'model', 'year_of_manufacture', 'capacity_litres',
                'fuel_type', 'ownership_type', 'supplier_transporter_id',
                'driver_name', 'driver_phone', 'rc_number', 'rc_expiry_date',
                'status', 'remarks', 'created_by', 'updated_by',
            ]);
        });
    }
};
