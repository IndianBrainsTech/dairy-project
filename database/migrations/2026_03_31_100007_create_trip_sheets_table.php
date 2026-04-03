<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * STATUS  : ALREADY DONE — hasTable guard protects against re-run
 * LARAVEL : Already recorded in migrations table
 * DB      : trip_sheets table already exists
 *
 * NOTE: routes table confirmed — name is exactly 'routes'
 *       district_id in routes is plain integer (not FK enforced)
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('trip_sheets')) {
            return;
        }

        Schema::create('trip_sheets', function (Blueprint $table) {
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
            $table->decimal('milk_collected_litres', 10, 2)->default(0);
            $table->decimal('milk_rejected_litres', 10, 2)->default(0);
            $table->decimal('net_milk_litres', 10, 2)->default(0);
            $table->decimal('rate_per_litre', 8, 2)->nullable();
            $table->decimal('trip_amount', 12, 2)->default(0);
            $table->enum('payment_mode', ['flat_rate', 'per_litre', 'per_km'])->default('flat_rate');
            $table->decimal('flat_rate_amount', 12, 2)->default(0);
            $table->decimal('diesel_consumed', 8, 2)->nullable();
            $table->decimal('diesel_cost', 12, 2)->default(0);
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'ts_vehicle_fk')
                  ->references('id')->on('vehicles')->restrictOnDelete();
            $table->foreign('route_id', 'ts_route_fk')
                  ->references('id')->on('routes')->restrictOnDelete();
            $table->foreign('created_by', 'ts_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'ts_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();

            $table->index(['trip_date', 'vehicle_id'], 'ts_date_vehicle_idx');
            $table->index(['trip_date', 'route_id'],   'ts_date_route_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_sheets');
    }
};
