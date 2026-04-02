<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BulkMilkOrder extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['invoice_num','invoice_date','order_dt','customer_id','customer_name','customer_data',
                           'route_id','route_name','vehicle_id','vehicle_num','driver_id','driver_name','driver_mobile_num',
                           'item_count','tot_amt','tcs','round_off','net_amt',
                           'order_status','receipt_status','invoice_status','cancel_remarks','tally_sync'];
                           
    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }
}
