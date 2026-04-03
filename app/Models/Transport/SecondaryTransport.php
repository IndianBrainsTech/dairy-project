<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecondaryTransport extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'secondary_transports';

    protected $fillable = [
        'reference_number',
        'transport_date',
        'supplier_transporter_id',
        'vehicle_number',
        'vehicle_type',
        'route_id',
        'from_location',
        'to_location',
        'distance_km',
        'loaded_qty',
        'product_type',
        'rate',
        'rate_type',
        'amount',
        'other_charges',
        'total_amount',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'transport_date' => 'date',
        'distance_km'    => 'decimal:2',
        'loaded_qty'     => 'decimal:2',
        'rate'           => 'decimal:2',
        'amount'         => 'decimal:2',
        'other_charges'  => 'decimal:2',
        'total_amount'   => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function supplierTransporter()
    {
        return $this->belongsTo(SupplierTransporter::class, 'supplier_transporter_id');
    }

    public function route()
    {
        return $this->belongsTo(\App\Models\Places\Route::class, 'route_id');
    }

    public function billItems()
    {
        return $this->hasMany(SecondaryTransportBillItem::class, 'secondary_transport_id');
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

    public function scopeUnbilled($query)
    {
        return $query->where('status', 'pending')
                     ->whereDoesntHave('billItems');
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('transport_date', [$from, $to]);
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (SecondaryTransport $st) {
            $st->total_amount = ($st->amount ?? 0) + ($st->other_charges ?? 0);
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateReferenceNumber(): string
    {
        $year   = now()->year;
        $prefix = "SECT-{$year}-";
        $last   = static::where('reference_number', 'like', "{$prefix}%")
                        ->orderByDesc('reference_number')
                        ->value('reference_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
