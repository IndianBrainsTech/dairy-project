<?php

namespace App\Models\Production;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockEntryHistory extends Model
{
    use HasFactory;
    protected $table = 'stock_entry_histories';
    protected $fillable = [
        'stock_entry_id', 'txn_id', 'product_name', 'product_id', 'group_id',
        'entry_qty', 'entry_unit', 'primary_unit_qty', 'primary_unit', 'total_stock_qty'
    ];

    public function stockEntry()
    {
        return $this->belongsTo(StockEntry::class);
    }
}
