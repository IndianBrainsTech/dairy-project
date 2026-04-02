<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkMilkOrderItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['invoice_num','product_id','product_name','hsn_code',
                           'qty_kg','clr','fat','snf','qty_ltr','ts','ts_rate','rate','amount'];
}
