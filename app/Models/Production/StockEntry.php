<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Products\Product;
use App\Models\Production\StockEntryHistory;
class StockEntry extends Model
{
    use HasFactory;
    protected $table = 'stock_entries'; 
    protected $fillable = [
        'product_unique_id', 'product_name', 'product_id', 'batch_no', 'group_id', 'entry_qty', 'entry_unit', 
        'primary_unit_qty', 'primary_unit', 'total_stock_qty'
    ]; 
    public function product()
    {
        return $this->belongsTo(Product::class,'product_id','id');
    }

    public function history()
    {
        return $this->hasMany(StockEntryHistory::class);
    }

    protected static function booted()
    {
        // Trigger the updating event when a stock entry is being updated
        static::updating(function ($stockEntry) {
            // Save the old data to the history table before updating
            StockEntryHistory::create([
                'stock_entry_id' => $stockEntry->id,
                'txn_id' => $stockEntry->txn_id,
                'product_name' => $stockEntry->product_name,
                'product_id' => $stockEntry->product_id,
                'batch_no' => $stockEntry->batch_no,
                'group_id' => $stockEntry->group_id,
                'entry_qty' => $stockEntry->entry_qty,
                'entry_unit' => $stockEntry->entry_unit,
                'primary_unit_qty' => $stockEntry->primary_unit_qty,
                'primary_unit' => $stockEntry->primary_unit,
                'total_stock_qty' => $stockEntry->total_stock_qty,
            ]);
        });
    }
}