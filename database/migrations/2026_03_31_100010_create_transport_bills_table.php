<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Transport Bills (header) ─────────────────────────────────────────
        Schema::create('transport_bills', function (Blueprint $table) {
            $table->id();
            $table->string('bill_number', 50)->unique();
            $table->date('bill_date');
            $table->date('bill_period_from');
            $table->date('bill_period_to');
            $table->unsignedBigInteger('vehicle_id');
            $table->unsignedBigInteger('supplier_transporter_id')->nullable();
            $table->enum('bill_type', ['own', 'hired'])->default('own');
            $table->integer('total_trips')->default(0);
            $table->decimal('total_distance_km', 10, 2)->default(0);
            $table->decimal('total_milk_litres', 12, 2)->default(0);
            $table->decimal('trip_charges', 12, 2)->default(0);
            $table->decimal('diesel_charges', 12, 2)->default(0);
            $table->decimal('adjustment_amount', 12, 2)->default(0);
            $table->decimal('other_charges', 12, 2)->default(0);
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('tds_percentage', 5, 2)->default(0);
            $table->decimal('tds_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->date('due_date')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['draft', 'approved', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('vehicle_id', 'tb_vehicle_fk')
                  ->references('id')->on('vehicles')->restrictOnDelete();
            $table->foreign('supplier_transporter_id', 'tb_supplier_fk')
                  ->references('id')->on('supplier_transporters')->nullOnDelete();
            $table->foreign('approved_by', 'tb_approved_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by', 'tb_created_by_fk')
                  ->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by', 'tb_updated_by_fk')
                  ->references('id')->on('users')->nullOnDelete();

            $table->index(['bill_date', 'vehicle_id'],              'tb_date_vehicle_idx');
            $table->index(['bill_period_from', 'bill_period_to'],   'tb_period_idx');
        });

        // ── Transport Bill Items (line rows) ─────────────────────────────────
        Schema::create('transport_bill_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transport_bill_id');
            $table->unsignedBigInteger('trip_sheet_id');
            $table->date('trip_date');
            $table->decimal('distance_km', 8, 2)->default(0);
            $table->decimal('milk_litres', 10, 2)->default(0);
            $table->decimal('trip_amount', 12, 2)->default(0);
            $table->decimal('diesel_amount', 12, 2)->default(0);
            $table->decimal('adjustment_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2)->default(0);
            $table->timestamps();

            $table->foreign('transport_bill_id', 'tbi_bill_fk')
                  ->references('id')->on('transport_bills')->cascadeOnDelete();
            $table->foreign('trip_sheet_id', 'tbi_trip_sheet_fk')
                  ->references('id')->on('trip_sheets')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transport_bill_items');
        Schema::dropIfExists('transport_bills');
    }
};
