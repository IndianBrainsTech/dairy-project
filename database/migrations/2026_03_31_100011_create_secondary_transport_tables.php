<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * STATUS  : NOT YET RUN
 * LARAVEL : NOT recorded in migrations table
 *
 * DEPENDENCIES (all satisfied before this runs):
 *   supplier_transporters → migration 100003
 *   routes                → confirmed name 'routes', exists in DB
 *   users                 → always exists
 *
 * THIS MIGRATION CREATES 4 TABLES:
 *   1. secondary_transports
 *   2. secondary_transport_bills
 *   3. secondary_transport_bill_items
 *   4. secondary_payment_abstracts
 *
 * ALL FK NAMES are short custom names (max 18 chars).
 * The original auto-generated name for secondary_transport_bill_items
 * was 66 chars — exceeded MySQL 64-char limit causing original error.
 * Custom names fix this completely.
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // ── 1. Secondary Transports ──────────────────────────────────────────
        if (!Schema::hasTable('secondary_transports')) {
            Schema::create('secondary_transports', function (Blueprint $table) {
                $table->id();
                $table->string('reference_number', 50)->unique();
                $table->date('transport_date');
                $table->unsignedBigInteger('supplier_transporter_id');
                $table->string('vehicle_number', 20);
                $table->string('vehicle_type', 100)->nullable();
                $table->unsignedBigInteger('route_id')->nullable();
                $table->string('from_location', 150)->nullable();
                $table->string('to_location', 150)->nullable();
                $table->decimal('distance_km', 8, 2)->nullable();
                $table->decimal('loaded_qty', 10, 2)->default(0);
                $table->string('product_type', 100)->nullable();
                $table->decimal('rate', 10, 2)->default(0);
                $table->enum('rate_type', ['per_trip', 'per_km', 'per_litre'])->default('per_trip');
                $table->decimal('amount', 12, 2)->default(0);
                $table->decimal('other_charges', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->enum('status', ['pending', 'billed', 'cancelled'])->default('pending');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('supplier_transporter_id', 'sect_supplier_fk')
                      ->references('id')->on('supplier_transporters')->restrictOnDelete();
                $table->foreign('route_id', 'sect_route_fk')
                      ->references('id')->on('routes')->nullOnDelete();
                $table->foreign('created_by', 'sect_created_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by', 'sect_updated_by_fk')
                      ->references('id')->on('users')->nullOnDelete();

                $table->index(
                    ['transport_date', 'supplier_transporter_id'],
                    'sect_date_supplier_idx'
                );
            });
        }

        // ── 2. Secondary Transport Bills (header) ────────────────────────────
        if (!Schema::hasTable('secondary_transport_bills')) {
            Schema::create('secondary_transport_bills', function (Blueprint $table) {
                $table->id();
                $table->string('bill_number', 50)->unique();
                $table->date('bill_date');
                $table->date('bill_period_from');
                $table->date('bill_period_to');
                $table->unsignedBigInteger('supplier_transporter_id');
                $table->integer('total_trips')->default(0);
                $table->decimal('total_qty', 12, 2)->default(0);
                $table->decimal('gross_amount', 12, 2)->default(0);
                $table->decimal('tds_percentage', 5, 2)->default(0);
                $table->decimal('tds_amount', 12, 2)->default(0);
                $table->decimal('other_deductions', 12, 2)->default(0);
                $table->decimal('net_payable', 12, 2)->default(0);
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

                $table->foreign('supplier_transporter_id', 'stb_supplier_fk')
                      ->references('id')->on('supplier_transporters')->restrictOnDelete();
                $table->foreign('approved_by', 'stb_approved_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
                $table->foreign('created_by', 'stb_created_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by', 'stb_updated_by_fk')
                      ->references('id')->on('users')->nullOnDelete();

                $table->index(
                    ['bill_date', 'supplier_transporter_id'],
                    'stb_date_supplier_idx'
                );
            });
        }

        // ── 3. Secondary Transport Bill Items (line rows) ────────────────────
        // IMPORTANT: Auto-generated FK name would be:
        // secondary_transport_bill_items_secondary_transport_bill_id_foreign
        // = 66 chars — EXCEEDS MySQL 64-char limit.
        // Custom short names fix this completely.
        if (!Schema::hasTable('secondary_transport_bill_items')) {
            Schema::create('secondary_transport_bill_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('secondary_transport_bill_id');
                $table->unsignedBigInteger('secondary_transport_id');
                $table->date('transport_date');
                $table->string('from_location', 150)->nullable();
                $table->string('to_location', 150)->nullable();
                $table->decimal('qty', 10, 2)->default(0);
                $table->decimal('rate', 10, 2)->default(0);
                $table->decimal('amount', 12, 2)->default(0);
                $table->timestamps();

                // SHORT custom names — auto-names exceed MySQL 64-char limit
                $table->foreign('secondary_transport_bill_id', 'stbi_bill_fk')
                      ->references('id')->on('secondary_transport_bills')->cascadeOnDelete();
                $table->foreign('secondary_transport_id', 'stbi_transport_fk')
                      ->references('id')->on('secondary_transports')->restrictOnDelete();
            });
        }

        // ── 4. Secondary Payment Abstracts ───────────────────────────────────
        // Matches PDF template: SEC TRANSPORT ABSTRACT.pdf
        if (!Schema::hasTable('secondary_payment_abstracts')) {
            Schema::create('secondary_payment_abstracts', function (Blueprint $table) {
                $table->id();
                $table->string('abstract_number', 50)->unique();
                $table->date('abstract_date');
                $table->date('period_from');
                $table->date('period_to');
                $table->unsignedBigInteger('supplier_transporter_id');
                $table->integer('total_bills')->default(0);
                $table->decimal('total_gross', 12, 2)->default(0);
                $table->decimal('total_tds', 12, 2)->default(0);
                $table->decimal('total_deductions', 12, 2)->default(0);
                $table->decimal('total_net_payable', 12, 2)->default(0);
                $table->decimal('total_paid', 12, 2)->default(0);
                $table->decimal('total_balance', 12, 2)->default(0);
                $table->enum('status', ['draft', 'finalised'])->default('draft');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                // SHORT custom names — auto-names exceed MySQL 64-char limit
                $table->foreign('supplier_transporter_id', 'spa_supplier_fk')
                      ->references('id')->on('supplier_transporters')->restrictOnDelete();
                $table->foreign('created_by', 'spa_created_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by', 'spa_updated_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('secondary_payment_abstracts');
        Schema::dropIfExists('secondary_transport_bill_items');
        Schema::dropIfExists('secondary_transport_bills');
        Schema::dropIfExists('secondary_transports');
        Schema::enableForeignKeyConstraints();
    }
};
