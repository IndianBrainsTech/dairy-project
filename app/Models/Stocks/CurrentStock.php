<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Products\Product;
use App\Models\Products\UOM;

class CurrentStock extends Model
{
    use HasFactory;

    protected $table = 'current_stocks';

    protected $fillable = [
        'item_id',
        'item_name',
        'unit_id',
        'opening_qty',
        'production_qty',
        'sales_qty',
        'return_qty',
        'current_stock',
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
     * Accessor: calculate balance on the fly.
     */
    public function getBalanceAttribute(): float
    {
        return (float) ($this->opening_qty + $this->production_qty - $this->sales_qty + $this->return_qty);
    }

    /**
     * Check if stock is in deficit.
     */
    public function getIsDeficitAttribute(): bool
    {
        return $this->balance < 0;
    }
}
