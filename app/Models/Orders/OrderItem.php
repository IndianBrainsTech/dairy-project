<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['order_num','item_category','product_id','product_name','qty','unit_id','unit_name',
                           'qty_str','price_str','amount','tax','total','discount','taxable'];

    public function unit()
    {
        return $this->belongsTo('App\Models\Products\UOM','unit_id','id');
    }
}
