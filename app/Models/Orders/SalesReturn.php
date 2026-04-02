<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturn extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['txn_id','txn_date','route_id','customer_id','invoice_num','invoice_type',
                           'return_data','amount','tax_amt','total_amt','round_off','net_amt','action'];

    protected $casts = [
        'return_data' => 'array',
    ];

    public function route()
    {
        return $this->belongsTo('App\Models\Places\MRoute','route_id','id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }
}
