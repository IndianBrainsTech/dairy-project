<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripSheetMarket extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trip_sheets_market';

    protected $fillable = [
        'trip_number',
        'trip_date',
        'vehicle_id',
        'route_id',
        'shift',
        'driver_name',
        'driver_phone',
        'departure_time',
        'arrival_time',
        'odometer_start',
        'odometer_end',
        'distance_km',
        'loaded_qty',
        'delivered_qty',
        'returned_qty',
        'product_type',
        'trip_amount',
        'diesel_consumed',
        'diesel_cost',
        'other_expenses',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trip_date'      => 'date',
        'distance_km'    => 'decimal:2',
        'loaded_qty'     => 'decimal:2',
        'delivered_qty'  => 'decimal:2',
        'returned_qty'   => 'decimal:2',
        'trip_amount'    => 'decimal:2',
        'diesel_consumed'=> 'decimal:2',
        'diesel_cost'    => 'decimal:2',
        'other_expenses' => 'decimal:2',
        'odometer_start' => 'integer',
        'odometer_end'   => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('trip_date', today());
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('trip_date', [$from, $to]);
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (TripSheetMarket $trip) {
            if ($trip->odometer_start && $trip->odometer_end) {
                $trip->distance_km = $trip->odometer_end - $trip->odometer_start;
            }
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateTripNumber(): string
    {
        $year   = now()->year;
        $prefix = "MTRIP-{$year}-";
        $last   = static::where('trip_number', 'like', "{$prefix}%")
                        ->orderByDesc('trip_number')
                        ->value('trip_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function getTotalExpensesAttribute(): float
    {
        return ($this->diesel_cost ?? 0) + ($this->other_expenses ?? 0);
    }
}
