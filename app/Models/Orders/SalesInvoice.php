<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class SalesInvoice extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['invoice_num','invoice_dt','order_num','order_dt',
                           'customer_id','customer_name','mobile_num','gst_number',
                           'route_id','route_name','vehicle_num','driver_name',
                           'item_count','crates','qty','amount',
                           'tcs','discount','round_off','net_amt',
                           'receipt_status','invoice_status','cancel_remarks',
                           'last_in_amount','empty_crates_received','amount_received',
                           'last_receipt','last_crates_received','ordered_by','is_printed','tally_sync'];

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }

    public function orderedBy()
    {
        return $this->belongsTo(User::class, 'ordered_by');
    }
}
