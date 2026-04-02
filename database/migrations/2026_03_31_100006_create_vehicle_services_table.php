<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('service_number', 50)->unique();
            $table->date('service_date');
            $table->enum('service_type', ['routine', 'repair', 'breakdown', 'tyre', 'other'])
                  ->default('routine');
            $table->string('service_center', 150)->nullable();
            $table->string('mechanic_name', 100)->nullable();
            $table->integer('odometer_reading')->nullable();
            $table->integer('next_service_km')->nullable();
            $table->date('next_service_date')->nullable();
            $table->decimal('labour_cost', 12, 2)->default(0);
            $table->decimal('parts_cost', 12, 2)->default(0);
            $table->decimal('other_cost', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->default(0);
            $table->text('work_done')->nullable();
            $table->string('document_path', 255)->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed'])->default('completed');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'vs_vehicle_fk')
                  ->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('created_by', 'vs_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'vs_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_services');
    }
};
