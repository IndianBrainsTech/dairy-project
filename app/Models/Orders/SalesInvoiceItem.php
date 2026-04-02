<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['invoice_num','product_id','product_name','item_category',
                           'hsn_code','crates','qty','amount'];
}
