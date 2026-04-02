<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Order extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['order_num','customer_id','area_id','route_id','user_id',
                           'order_status','invoice_date','delivery_dt','address_data',
                           'sales_disc','tax_disc','sales_tcs','tax_tcs','invoice_status','cancel_remarks',
                           'created_by','edited_by','actioned_by','edited_at','actioned_at'];

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Places\Area','area_id','id');
    }

    public function route()
    {
        return $this->belongsTo('App\Models\Places\MRoute','route_id','id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editedBy()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }
}
