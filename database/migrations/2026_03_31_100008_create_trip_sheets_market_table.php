<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_sheets_market', function (Blueprint $table) {
            $table->id();
            $table->string('trip_number', 50)->unique();
            $table->date('trip_date');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('route_id');
            $table->string('shift', 50)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('driver_phone', 15)->nullable();
            $table->time('departure_time')->nullable();
            $table->time('arrival_time')->nullable();
            $table->integer('odometer_start')->nullable();
            $table->integer('odometer_end')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('loaded_qty', 10, 2)->default(0);
            $table->decimal('delivered_qty', 10, 2)->default(0);
            $table->decimal('returned_qty', 10, 2)->default(0);
            $table->string('product_type', 100)->nullable();
            $table->decimal('trip_amount', 12, 2)->default(0);
            $table->decimal('diesel_consumed', 8, 2)->nullable();
            $table->decimal('diesel_cost', 12, 2)->default(0);
            $table->decimal('other_expenses', 12, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'tsm_vehicle_fk')
                  ->references('id')->on('vehicles')->restrictOnDelete();
            $table->foreign('route_id', 'tsm_route_fk')
                  ->references('id')->on('routes')->restrictOnDelete();
            $table->foreign('created_by', 'tsm_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'tsm_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();

            $table->index(['trip_date', 'vehicle_id'], 'tsm_date_vehicle_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_sheets_market');
    }
};
