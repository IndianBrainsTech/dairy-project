<?php

namespace App\Models\Stocks;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products\Product;
use App\Models\Products\UOM;
use App\Models\User;

class StockHistory extends Model
{
    use HasFactory;

    protected $table = "stock_history";

    protected $fillable = [
        'stock_id',
        'item_id',
        'item_name',
        'batch_number',
        'quantity',
        'unit_id',
        'version_code',
        'user_id',
        'record_id',
        'action_type',
    ];

    /**
     * Get the stock that owns this item.
     */
    public function stock()
    {
        return $this->belongsTo(Stock::class);
    }

    /**
     * Get the product linked to this item.
     */
    public function item()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    /**
     * Get the unit of measurement for this item.
     */
    public function unit()
    {
        return $this->belongsTo(UOM::class, 'unit_id');
    }

    /**
     * Get the user who made changes for this item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
