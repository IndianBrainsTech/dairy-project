<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransportBillItem extends Model
{
    use HasFactory;

    protected $table = 'transport_bill_items';

    protected $fillable = [
        'transport_bill_id',
        'trip_sheet_id',
        'trip_date',
        'distance_km',
        'milk_litres',
        'trip_amount',
        'diesel_amount',
        'adjustment_amount',
        'line_total',
    ];

    protected $casts = [
        'trip_date'         => 'date',
        'distance_km'       => 'decimal:2',
        'milk_litres'       => 'decimal:2',
        'trip_amount'       => 'decimal:2',
        'diesel_amount'     => 'decimal:2',
        'adjustment_amount' => 'decimal:2',
        'line_total'        => 'decimal:2',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function transportBill()
    {
        return $this->belongsTo(TransportBill::class, 'transport_bill_id');
    }

    public function tripSheet()
    {
        return $this->belongsTo(TripSheet::class, 'trip_sheet_id');
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (TransportBillItem $item) {
            $item->line_total =
                ($item->trip_amount       ?? 0) +
                ($item->diesel_amount     ?? 0) -
                ($item->adjustment_amount ?? 0);
        });
    }
}
