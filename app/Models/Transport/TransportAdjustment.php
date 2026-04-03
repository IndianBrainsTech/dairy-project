<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportAdjustment extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transport_adjustments';

    protected $fillable = [
        'adjustment_number',
        'adjustment_date',
        'vehicle_id',
        'trip_sheet_id',
        'adjustment_type',
        'reason',
        'reason_description',
        'amount',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'amount'          => 'decimal:2',
        'approved_at'     => 'datetime',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function tripSheet()
    {
        return $this->belongsTo(TripSheet::class, 'trip_sheet_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
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

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeDebits($query)
    {
        return $query->where('adjustment_type', 'debit');
    }

    public function scopeCredits($query)
    {
        return $query->where('adjustment_type', 'credit');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateAdjustmentNumber(): string
    {
        $year   = now()->year;
        $prefix = "TADJ-{$year}-";
        $last   = static::where('adjustment_number', 'like', "{$prefix}%")
                        ->orderByDesc('adjustment_number')
                        ->value('adjustment_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function approve(int $userId): bool
    {
        return $this->update([
            'status'      => 'approved',
            'approved_by' => $userId,
            'approved_at' => now(),
        ]);
    }
}
