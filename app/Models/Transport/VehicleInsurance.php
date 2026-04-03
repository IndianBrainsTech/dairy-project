<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class VehicleInsurance extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'vehicle_insurance';

    protected $fillable = [
        'vehicle_id',
        'policy_number',
        'insurance_company',
        'agent_name',
        'agent_phone',
        'insurance_type',
        'start_date',
        'expiry_date',
        'premium_amount',
        'insured_value',
        'document_path',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date'     => 'date',
        'expiry_date'    => 'date',
        'premium_amount' => 'decimal:2',
        'insured_value'  => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'active')
                     ->whereBetween('expiry_date', [
                         now()->toDateString(),
                         now()->addDays($days)->toDateString(),
                     ]);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now()->toDateString());
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getDaysToExpiryAttribute(): int
    {
        if (!$this->expiry_date) {
            return 0;
        }
        return max(0, (int) now()->diffInDays($this->expiry_date, false));
    }

    public function getExpiryStatusAttribute(): string
    {
        if (!$this->expiry_date) {
            return 'unknown';
        }
        if ($this->expiry_date->isPast()) {
            return 'expired';
        }
        if ($this->expiry_date->diffInDays(now()) <= 7) {
            return 'critical';
        }
        if ($this->expiry_date->diffInDays(now()) <= 30) {
            return 'expiring_soon';
        }
        return 'valid';
    }
}
