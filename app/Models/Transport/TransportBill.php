<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'transport_bills';

    protected $fillable = [
        'bill_number',
        'bill_date',
        'bill_period_from',
        'bill_period_to',
        'vehicle_id',
        'supplier_transporter_id',
        'bill_type',
        'total_trips',
        'total_distance_km',
        'total_milk_litres',
        'trip_charges',
        'diesel_charges',
        'adjustment_amount',
        'other_charges',
        'gross_amount',
        'tds_percentage',
        'tds_amount',
        'net_amount',
        'payment_status',
        'paid_amount',
        'balance_amount',
        'due_date',
        'remarks',
        'status',
        'approved_by',
        'approved_at',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'bill_date'         => 'date',
        'bill_period_from'  => 'date',
        'bill_period_to'    => 'date',
        'due_date'          => 'date',
        'approved_at'       => 'datetime',
        'total_trips'       => 'integer',
        'total_distance_km' => 'decimal:2',
        'total_milk_litres' => 'decimal:2',
        'trip_charges'      => 'decimal:2',
        'diesel_charges'    => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'other_charges'     => 'decimal:2',
        'gross_amount'      => 'decimal:2',
        'tds_percentage'    => 'decimal:2',
        'tds_amount'        => 'decimal:2',
        'net_amount'        => 'decimal:2',
        'paid_amount'       => 'decimal:2',
        'balance_amount'    => 'decimal:2',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    public function supplierTransporter()
    {
        return $this->belongsTo(SupplierTransporter::class, 'supplier_transporter_id');
    }

    public function items()
    {
        return $this->hasMany(TransportBillItem::class, 'transport_bill_id');
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

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['unpaid', 'partial']);
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('bill_date', [$from, $to]);
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (TransportBill $bill) {
            $bill->gross_amount   = ($bill->trip_charges    ?? 0)
                                  + ($bill->diesel_charges  ?? 0)
                                  + ($bill->other_charges   ?? 0)
                                  - ($bill->adjustment_amount ?? 0);

            $bill->tds_amount     = round(
                $bill->gross_amount * (($bill->tds_percentage ?? 0) / 100),
                2
            );

            $bill->net_amount     = $bill->gross_amount - $bill->tds_amount;
            $bill->balance_amount = $bill->net_amount   - ($bill->paid_amount ?? 0);

            $bill->payment_status = match (true) {
                ($bill->paid_amount ?? 0) <= 0                  => 'unpaid',
                ($bill->paid_amount ?? 0) >= $bill->net_amount  => 'paid',
                default                                          => 'partial',
            };
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateBillNumber(): string
    {
        $year   = now()->year;
        $prefix = "TBILL-{$year}-";
        $last   = static::where('bill_number', 'like', "{$prefix}%")
                        ->orderByDesc('bill_number')
                        ->value('bill_number');
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

    public function recalculateTotals(): void
    {
        $items = $this->items()->get();
        $this->update([
            'total_trips'       => $items->count(),
            'total_distance_km' => $items->sum('distance_km'),
            'total_milk_litres' => $items->sum('milk_litres'),
            'trip_charges'      => $items->sum('trip_amount'),
            'diesel_charges'    => $items->sum('diesel_amount'),
            'adjustment_amount' => $items->sum('adjustment_amount'),
        ]);
        // booted() auto-recalculates gross/tds/net/balance on save
    }
}
