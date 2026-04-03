<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SecondaryTransportBillItem extends Model
{
    use HasFactory;

    protected $table = 'secondary_transport_bill_items';

    protected $fillable = [
        'secondary_transport_bill_id',
        'secondary_transport_id',
        'transport_date',
        'from_location',
        'to_location',
        'qty',
        'rate',
        'amount',
    ];

    protected $casts = [
        'transport_date' => 'date',
        'qty'            => 'decimal:2',
        'rate'           => 'decimal:2',
        'amount'         => 'decimal:2',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function secondaryTransportBill()
    {
        return $this->belongsTo(SecondaryTransportBill::class, 'secondary_transport_bill_id');
    }

    public function secondaryTransport()
    {
        return $this->belongsTo(SecondaryTransport::class, 'secondary_transport_id');
    }

    // ── Mutators ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::saving(function (SecondaryTransportBillItem $item) {
            $item->amount = ($item->qty ?? 0) * ($item->rate ?? 0);
        });
    }
}
