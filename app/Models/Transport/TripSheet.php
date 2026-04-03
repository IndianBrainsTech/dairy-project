<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TripSheet extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'trip_sheets';

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
        'milk_collected_litres',
        'milk_rejected_litres',
        'net_milk_litres',
        'rate_per_litre',
        'trip_amount',
        'payment_mode',
        'flat_rate_amount',
        'diesel_consumed',
        'diesel_cost',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'trip_date'             => 'date',
        'distance_km'           => 'decimal:2',
        'milk_collected_litres' => 'decimal:2',
        'milk_rejected_litres'  => 'decimal:2',
        'net_milk_litres'       => 'decimal:2',
        'rate_per_litre'        => 'decimal:2',
        'trip_amount'           => 'decimal:2',
        'flat_rate_amount'      => 'decimal:2',
        'diesel_consumed'       => 'decimal:2',
        'diesel_cost'           => 'decimal:2',
        'odometer_start'        => 'integer',
        'odometer_end'          => 'integer',
        'created_at'            => 'datetime',
        'updated_at'            => 'datetime',
        'deleted_at'            => 'datetime',
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

    public function transportBillItems()
    {
        return $this->hasMany(TransportBillItem::class, 'trip_sheet_id');
    }

    public function adjustment()
    {
        return $this->hasOne(TransportAdjustment::class, 'trip_sheet_id');
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('trip_date', today());
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('trip_date', [$from, $to]);
    }

    public function scopeUnbilled($query)
    {
        return $query->where('status', 'completed')
                     ->whereDoesntHave('transportBillItems');
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (TripSheet $trip) {
            // Auto-calculate net milk
            $trip->net_milk_litres =
                ($trip->milk_collected_litres ?? 0) -
                ($trip->milk_rejected_litres  ?? 0);

            // Auto-calculate distance
            if ($trip->odometer_start && $trip->odometer_end) {
                $trip->distance_km = $trip->odometer_end - $trip->odometer_start;
            }

            // Auto-calculate trip amount
            $trip->trip_amount = match ($trip->payment_mode) {
                'per_litre' => $trip->net_milk_litres * ($trip->rate_per_litre ?? 0),
                default     => $trip->flat_rate_amount ?? 0,
            };
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateTripNumber(): string
    {
        $year   = now()->year;
        $prefix = "TRIP-{$year}-";
        $last   = static::where('trip_number', 'like', "{$prefix}%")
                        ->orderByDesc('trip_number')
                        ->value('trip_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function isBilled(): bool
    {
        return $this->transportBillItems()->exists();
    }
}
