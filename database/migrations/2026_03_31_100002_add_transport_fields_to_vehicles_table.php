<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * STATUS  : ALREADY DONE — EMPTY MIGRATION
 * LARAVEL : Already recorded in migrations table
 *
 * CONFIRMED FROM DB (phpMyAdmin DESCRIBE vehicles):
 *   All columns already exist in vehicles table:
 *   vehicle_category_id, vehicle_number, vehicle_name,
 *   vehicle_type (enum: Lorry/Truck/Van/Two Wheeler),
 *   make, model, status (enum: Active/Inactive),
 *   year_of_manufacture, capacity_litres, fuel_type,
 *   ownership_type, supplier_transporter_id,
 *   driver_name, driver_phone, rc_number, rc_expiry_date,
 *   remarks, created_by, updated_by
 *
 * CONFIRMED FROM DB (information_schema):
 *   FK constraints already exist:
 *   vehicles_created_by_foreign        → users(id)
 *   vehicles_updated_by_foreign        → users(id)
 *   vehicles_vehicle_category_id_foreign → vehicle_categories(id)
 *
 * NOTE: supplier_transporter_id column EXISTS but FK missing.
 *       FK is added in migration 100003 after supplier_transporters
 *       table is created.
 *
 * ACTION  : Does nothing — all work already in DB.
 *           Kept so Laravel migration history stays intact.
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        // Nothing to do — all columns and FKs already confirmed in DB.
        // supplier_transporter_id FK is handled in migration 100003.
    }

    public function down(): void
    {
        // Nothing was done in up() — nothing to undo.
    }
};
