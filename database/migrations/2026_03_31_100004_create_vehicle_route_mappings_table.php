<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_route_mappings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('route_id');
            $table->enum('route_type', ['collection', 'marketing'])->default('collection');
            $table->string('shift', 50)->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->decimal('rate_per_km', 8, 2)->nullable();
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'vrm_vehicle_fk')
                  ->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('route_id', 'vrm_route_fk')
                  ->references('id')->on('routes')->cascadeOnDelete();
            $table->foreign('created_by', 'vrm_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'vrm_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_route_mappings');
    }
};
