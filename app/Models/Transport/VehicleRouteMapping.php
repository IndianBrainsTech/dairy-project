<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleRouteMapping extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_route_mappings';

    protected $fillable = [
        'vehicle_id',
        'route_id',
        'route_type',
        'shift',
        'distance_km',
        'rate_per_km',
        'effective_from',
        'effective_to',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_from' => 'date',
        'effective_to'   => 'date',
        'distance_km'    => 'decimal:2',
        'rate_per_km'    => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Links to existing 'routes' table (confirmed name from DB).
     * Your project uses PlaceController for routes — no Route model exists.
     * Using DB facade for raw queries where needed, or create a simple Route model.
     */
    public function route()
    {
        return $this->belongsTo(\App\Models\Places\Route::class, 'route_id');
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCollection($query)
    {
        return $query->where('route_type', 'collection');
    }

    public function scopeMarketing($query)
    {
        return $query->where('route_type', 'marketing');
    }

    public function scopeCurrentlyActive($query)
    {
        return $query->where('status', 'active')
                     ->where('effective_from', '<=', now())
                     ->where(function ($q) {
                         $q->whereNull('effective_to')
                           ->orWhere('effective_to', '>=', now());
                     });
    }
}
