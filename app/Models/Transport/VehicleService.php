<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleService extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_services';

    protected $fillable = [
        'vehicle_id',
        'service_number',
        'service_date',
        'service_type',
        'service_center',
        'mechanic_name',
        'odometer_reading',
        'next_service_km',
        'next_service_date',
        'labour_cost',
        'parts_cost',
        'other_cost',
        'total_cost',
        'work_done',
        'document_path',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'service_date'      => 'date',
        'next_service_date' => 'date',
        'labour_cost'       => 'decimal:2',
        'parts_cost'        => 'decimal:2',
        'other_cost'        => 'decimal:2',
        'total_cost'        => 'decimal:2',
        'odometer_reading'  => 'integer',
        'next_service_km'   => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
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

    public function scopeDueSoon($query, int $days = 30)
    {
        return $query->where('status', 'completed')
                     ->whereNotNull('next_service_date')
                     ->whereBetween('next_service_date', [
                         now()->toDateString(),
                         now()->addDays($days)->toDateString(),
                     ]);
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    // Auto-calculate total_cost before saving
    protected static function booted(): void
    {
        static::saving(function (VehicleService $service) {
            $service->total_cost =
                ($service->labour_cost ?? 0) +
                ($service->parts_cost  ?? 0) +
                ($service->other_cost  ?? 0);
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateServiceNumber(): string
    {
        $year   = now()->year;
        $prefix = "SVC-{$year}-";
        $last   = static::where('service_number', 'like', "{$prefix}%")
                        ->orderByDesc('service_number')
                        ->value('service_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
