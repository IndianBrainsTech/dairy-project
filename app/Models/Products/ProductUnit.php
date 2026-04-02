<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductUnit extends Model
{
    use HasFactory;
    protected $table = "product_units";
    protected $primaryKey = 'id';
    protected $fillable = ['product_id','unit_id','price','prim_unit','conversion'];

    public function unit()
    {
        return $this->belongsTo('App\Models\Products\UOM','unit_id','id');
    }

    public function product()
    {
        return $this->belongsTo('App\Models\Products\Product','product_id','id');
    }
}
