<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecondaryTransportBill extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'secondary_transport_bills';

    protected $fillable = [
        'bill_number',
        'bill_date',
        'bill_period_from',
        'bill_period_to',
        'supplier_transporter_id',
        'total_trips',
        'total_qty',
        'gross_amount',
        'tds_percentage',
        'tds_amount',
        'other_deductions',
        'net_payable',
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
        'bill_date'        => 'date',
        'bill_period_from' => 'date',
        'bill_period_to'   => 'date',
        'due_date'         => 'date',
        'approved_at'      => 'datetime',
        'total_trips'      => 'integer',
        'total_qty'        => 'decimal:2',
        'gross_amount'     => 'decimal:2',
        'tds_percentage'   => 'decimal:2',
        'tds_amount'       => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_payable'      => 'decimal:2',
        'paid_amount'      => 'decimal:2',
        'balance_amount'   => 'decimal:2',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
        'deleted_at'       => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function supplierTransporter()
    {
        return $this->belongsTo(SupplierTransporter::class, 'supplier_transporter_id');
    }

    public function items()
    {
        return $this->hasMany(SecondaryTransportBillItem::class, 'secondary_transport_bill_id');
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

    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['unpaid', 'partial']);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeForPeriod($query, string $from, string $to)
    {
        return $query->whereBetween('bill_date', [$from, $to]);
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (SecondaryTransportBill $bill) {
            $bill->tds_amount    = round(
                ($bill->gross_amount ?? 0) * (($bill->tds_percentage ?? 0) / 100),
                2
            );
            $bill->net_payable   = ($bill->gross_amount   ?? 0)
                                 - $bill->tds_amount
                                 - ($bill->other_deductions ?? 0);
            $bill->balance_amount = $bill->net_payable - ($bill->paid_amount ?? 0);

            $bill->payment_status = match (true) {
                ($bill->paid_amount ?? 0) <= 0                   => 'unpaid',
                ($bill->paid_amount ?? 0) >= $bill->net_payable  => 'paid',
                default                                           => 'partial',
            };
        });
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function generateBillNumber(): string
    {
        $year   = now()->year;
        $prefix = "STBILL-{$year}-";
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
            'total_trips'  => $items->count(),
            'total_qty'    => $items->sum('qty'),
            'gross_amount' => $items->sum('amount'),
        ]);
        // booted() auto-recalculates tds/net/balance on save
    }
}
