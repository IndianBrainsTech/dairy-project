<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecondaryPaymentAbstract extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'secondary_payment_abstracts';

    protected $fillable = [
        'abstract_number',
        'abstract_date',
        'period_from',
        'period_to',
        'supplier_transporter_id',
        'total_bills',
        'total_gross',
        'total_tds',
        'total_deductions',
        'total_net_payable',
        'total_paid',
        'total_balance',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'abstract_date'     => 'date',
        'period_from'       => 'date',
        'period_to'         => 'date',
        'total_bills'       => 'integer',
        'total_gross'       => 'decimal:2',
        'total_tds'         => 'decimal:2',
        'total_deductions'  => 'decimal:2',
        'total_net_payable' => 'decimal:2',
        'total_paid'        => 'decimal:2',
        'total_balance'     => 'decimal:2',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
        'deleted_at'        => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function supplierTransporter()
    {
        return $this->belongsTo(SupplierTransporter::class, 'supplier_transporter_id');
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

    public function scopeFinalised($query)
    {
        return $query->where('status', 'finalised');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    /**
     * Recalculate totals by summing all approved secondary bills
     * for this transporter in this period.
     */
    public function recalculate(): void
    {
        $bills = SecondaryTransportBill::where(
                    'supplier_transporter_id', $this->supplier_transporter_id
                )
                ->where('status', 'approved')
                ->whereBetween('bill_date', [
                    $this->period_from->toDateString(),
                    $this->period_to->toDateString(),
                ])
                ->get();

        $this->update([
            'total_bills'       => $bills->count(),
            'total_gross'       => $bills->sum('gross_amount'),
            'total_tds'         => $bills->sum('tds_amount'),
            'total_deductions'  => $bills->sum('other_deductions'),
            'total_net_payable' => $bills->sum('net_payable'),
            'total_paid'        => $bills->sum('paid_amount'),
            'total_balance'     => $bills->sum('balance_amount'),
        ]);
    }

    public static function generateAbstractNumber(): string
    {
        $year   = now()->year;
        $prefix = "SPA-{$year}-";
        $last   = static::where('abstract_number', 'like', "{$prefix}%")
                        ->orderByDesc('abstract_number')
                        ->value('abstract_number');
        $next = $last ? ((int) substr($last, -4)) + 1 : 1;
        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }
}
