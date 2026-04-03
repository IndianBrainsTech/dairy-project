<?php

namespace App\Models\Places;

use Illuminate\Database\Eloquent\Model;

/**
 * ============================================================
 * Route model for the existing 'routes' table.
 *
 * CONFIRMED TABLE STRUCTURE (from create_routes_table migration):
 *   id           bigint unsigned PK
 *   name         varchar(40) unique
 *   district_id  integer (plain integer, no FK enforced)
 *   tally_sync   varchar(200) nullable
 *   created_at   timestamp
 *   updated_at   timestamp
 *
 * PLACE THIS FILE AT:
 *   app/Models/Places/Route.php
 *
 * NOTE: Check if this file already exists in your project at
 *   app/Models/Places/ — if it does, do NOT replace it.
 *   If it doesn't exist, add this file.
 * ============================================================
 */
class Route extends Model
{
    protected $table = 'routes';

    protected $fillable = [
        'name',
        'district_id',
        'tally_sync',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicleRouteMappings()
    {
        return $this->hasMany(\App\Models\Transport\VehicleRouteMapping::class, 'route_id');
    }

    public function tripSheets()
    {
        return $this->hasMany(\App\Models\Transport\TripSheet::class, 'route_id');
    }

    public function tripSheetsMarket()
    {
        return $this->hasMany(\App\Models\Transport\TripSheetMarket::class, 'route_id');
    }
}
