<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * ============================================================
 * IMPORTANT — EXISTING COLUMNS (from original migration):
 *   vehicle_number  varchar(15)
 *   vehicle_type    enum('Lorry','Truck','Van','Two Wheeler')
 *   make            varchar(30)
 *   model           varchar(30)
 *   status          enum('Active','Inactive') — CAPITAL letters
 *
 * ADDED COLUMNS (from migration 100002 — confirmed in DB):
 *   vehicle_category_id, vehicle_name, year_of_manufacture,
 *   capacity_litres, fuel_type, ownership_type,
 *   supplier_transporter_id, driver_name, driver_phone,
 *   rc_number, rc_expiry_date, remarks, created_by, updated_by
 *
 * NOTE: The existing TransportController uses:
 *   ->where('status','Active')  — capital A
 *   This model preserves that convention.
 * ============================================================
 */
class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicles';

    protected $fillable = [
        // Original columns
        'vehicle_number',
        'vehicle_type',
        'make',
        'model',
        'status',
        // Added by migration 100002
        'vehicle_category_id',
        'vehicle_name',
        'year_of_manufacture',
        'capacity_litres',
        'fuel_type',
        'ownership_type',
        'supplier_transporter_id',
        'driver_name',
        'driver_phone',
        'rc_number',
        'rc_expiry_date',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'rc_expiry_date'      => 'date',
        'capacity_litres'     => 'decimal:2',
        'year_of_manufacture' => 'integer',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
        'deleted_at'          => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function category()
    {
        return $this->belongsTo(VehicleCategory::class, 'vehicle_category_id');
    }

    public function supplierTransporter()
    {
        return $this->belongsTo(SupplierTransporter::class, 'supplier_transporter_id');
    }

    public function routeMappings()
    {
        return $this->hasMany(VehicleRouteMapping::class, 'vehicle_id');
    }

    public function activeRouteMappings()
    {
        return $this->hasMany(VehicleRouteMapping::class, 'vehicle_id')
                    ->where('status', 'active');
    }

    public function insurance()
    {
        return $this->hasMany(VehicleInsurance::class, 'vehicle_id');
    }

    public function activeInsurance()
    {
        return $this->hasOne(VehicleInsurance::class, 'vehicle_id')
                    ->where('status', 'active')
                    ->latest('expiry_date');
    }

    public function services()
    {
        return $this->hasMany(VehicleService::class, 'vehicle_id');
    }

    public function tripSheets()
    {
        return $this->hasMany(TripSheet::class, 'vehicle_id');
    }

    public function tripSheetsMarket()
    {
        return $this->hasMany(TripSheetMarket::class, 'vehicle_id');
    }

    public function transportBills()
    {
        return $this->hasMany(TransportBill::class, 'vehicle_id');
    }

    public function transportAdjustments()
    {
        return $this->hasMany(TransportAdjustment::class, 'vehicle_id');
    }

    public function dieselBills()
    {
        return $this->hasMany(\App\Models\Transport\DieselBill::class, 'vehicle_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    // Matches existing controller: ->where('status','Active')
    public function scopeActive($query)
    {
        return $query->where('status', 'Active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', 'Inactive');
    }

    public function scopeOwn($query)
    {
        return $query->where('ownership_type', 'own');
    }

    public function scopeHired($query)
    {
        return $query->where('ownership_type', 'hired');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getRcExpiryStatusAttribute(): string
    {
        if (!$this->rc_expiry_date) {
            return 'unknown';
        }
        if ($this->rc_expiry_date->isPast()) {
            return 'expired';
        }
        if ($this->rc_expiry_date->diffInDays(now()) <= 30) {
            return 'expiring_soon';
        }
        return 'valid';
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->vehicle_number . ($this->vehicle_name ? ' - ' . $this->vehicle_name : '');
    }
}
