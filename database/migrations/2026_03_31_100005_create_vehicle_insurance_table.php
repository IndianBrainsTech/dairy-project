<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_insurance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vehicle_id');
            $table->string('policy_number', 100);
            $table->string('insurance_company', 150);
            $table->string('agent_name', 100)->nullable();
            $table->string('agent_phone', 15)->nullable();
            $table->enum('insurance_type', ['comprehensive', 'third_party', 'fire_theft'])
                  ->default('comprehensive');
            $table->date('start_date');
            $table->date('expiry_date');
            $table->decimal('premium_amount', 12, 2)->default(0);
            $table->decimal('insured_value', 12, 2)->nullable();
            $table->string('document_path', 255)->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'vi_vehicle_fk')
                  ->references('id')->on('vehicles')->cascadeOnDelete();
            $table->foreign('created_by', 'vi_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'vi_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_insurance');
    }
};
