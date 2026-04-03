<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * STATUS  : ALREADY DONE — hasTable guard protects against re-run
 * LARAVEL : Already recorded in migrations table
 * DB      : transport_adjustments table already exists
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('transport_adjustments')) {
            return;
        }

        Schema::create('transport_adjustments', function (Blueprint $table) {
            $table->id();
            $table->string('adjustment_number', 50)->unique();
            $table->date('adjustment_date');
            $table->unsignedBigInteger('vehicle_id')->nullable();
            $table->unsignedBigInteger('trip_sheet_id')->nullable();
            $table->enum('adjustment_type', ['debit', 'credit'])->default('debit');
            $table->enum('reason', [
                'damage', 'shortage', 'delay',
                'toll', 'loading_unloading', 'other'
            ])->default('other');
            $table->string('reason_description', 255)->nullable();
            $table->decimal('amount', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'ta_vehicle_fk')
                  ->references('id')->on('vehicles')->nullOnDelete();
            $table->foreign('trip_sheet_id', 'ta_trip_sheet_fk')
                  ->references('id')->on('trip_sheets')->nullOnDelete();
            $table->foreign('approved_by', 'ta_approved_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by', 'ta_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'ta_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_adjustments');
    }
};
