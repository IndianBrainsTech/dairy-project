<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Products\Product;
use App\Models\Products\UOM;

class StockRegister extends Model
{
    use HasFactory;

    protected $table = 'stock_register';

    protected $fillable = [
        'record_date',
        'item_id',
        'item_name',
        'unit_id',
        'opening_qty',
        'production_qty',
        'sales_qty',
        'return_qty',
        'closing_qty',
    ];

    /**
     * Relationship with Product (item).
     */
    public function item(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Relationship with Unit.
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(UOM::class, 'unit_id');
    }

    /**
     * Calculate total in/out for the day.
     */
    public function getTotalInAttribute(): float
    {
        return (float) ($this->production_qty + $this->return_qty);
    }

    public function getTotalOutAttribute(): float
    {
        return (float) $this->sales_qty;
    }

    /**
     * Get balance (closing stock).
     */
    public function getBalanceAttribute(): float
    {
        return (float) $this->closing_qty;
    }
}
