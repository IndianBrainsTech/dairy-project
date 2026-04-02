<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';    
    protected $fillable = ['name','short_name','item_code','group_id','type','description',
                           'display_index','mrp','fat','snf',
                           'image','visible_app','visible_invoice',
                           'visible_bulkmilk','tally_sync','status'];

    public function prod_group()
    {
        return $this->belongsTo('App\Models\Products\ProductGroup','group_id','id');
    }

    public function conversion()
    {
        return $this->hasMany(ViewProductUnit::class, 'product_id', 'id');
    }

    public function primaryUnit()
    {
        return $this->hasOne(ProductUnit::class, 'product_id')
            ->where('prim_unit', 1);
    }
}
