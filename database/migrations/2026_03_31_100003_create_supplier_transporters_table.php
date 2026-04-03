<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * ============================================================
 * STATUS  : NOT YET RUN
 * LARAVEL : NOT recorded in migrations table
 *
 * CONFIRMED FROM DB:
 *   SHOW TABLES LIKE 'supplier%' → only 'suppliers' exists
 *   supplier_transporters        → does NOT exist
 *   vehicles.supplier_transporter_id → column EXISTS, FK missing
 *
 * THIS MIGRATION DOES:
 *   Step 1 — Create supplier_transporters table
 *   Step 2 — Add v_supplier_fk on vehicles.supplier_transporter_id
 *             (column already exists, just adding the constraint)
 *
 * SAFETY GUARDS:
 *   - hasTable() check before creating supplier_transporters
 *   - information_schema check before adding FK
 *   - disableForeignKeyConstraints() / enableForeignKeyConstraints()
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        // ── Step 1: Create supplier_transporters ────────────────────────────
        if (!Schema::hasTable('supplier_transporters')) {
            Schema::create('supplier_transporters', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('contact_person', 100)->nullable();
                $table->string('phone', 15)->nullable();
                $table->string('alt_phone', 15)->nullable();
                $table->string('email', 100)->nullable();
                $table->text('address')->nullable();
                $table->string('city', 100)->nullable();
                $table->string('state', 100)->nullable();
                $table->string('pincode', 10)->nullable();
                $table->string('gst_number', 20)->nullable();
                $table->string('pan_number', 15)->nullable();
                $table->string('bank_name', 100)->nullable();
                $table->string('bank_account_number', 30)->nullable();
                $table->string('bank_ifsc', 15)->nullable();
                $table->string('bank_branch', 100)->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->foreign('created_by', 'st_created_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
                $table->foreign('updated_by', 'st_updated_by_fk')
                      ->references('id')->on('users')->nullOnDelete();
            });
        }

        // ── Step 2: Add FK on vehicles.supplier_transporter_id ──────────────
        // The column already exists (confirmed from phpMyAdmin DESCRIBE vehicles).
        // The FK does NOT exist yet (confirmed from information_schema query).
        // We check information_schema to be 100% safe before adding.
        $fkExists = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vehicles'
              AND COLUMN_NAME = 'supplier_transporter_id'
              AND REFERENCED_TABLE_NAME IS NOT NULL
        ");

        if ($fkExists[0]->cnt == 0) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->foreign('supplier_transporter_id', 'v_supplier_fk')
                      ->references('id')->on('supplier_transporters')
                      ->nullOnDelete();
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();

        // Remove FK from vehicles first
        $fkExists = DB::select("
            SELECT COUNT(*) as cnt
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'vehicles'
              AND CONSTRAINT_NAME = 'v_supplier_fk'
        ");

        if ($fkExists[0]->cnt > 0) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropForeign('v_supplier_fk');
            });
        }

        Schema::dropIfExists('supplier_transporters');

        Schema::enableForeignKeyConstraints();
    }
};
