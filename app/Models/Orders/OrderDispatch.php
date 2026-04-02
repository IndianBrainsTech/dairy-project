<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDispatch extends Model
{
    use HasFactory;
    protected $table = "order_dispatches";
    protected $primaryKey = 'id';
    protected $fillable = ['invoice_date','route_id','route_name','vehicle_id','vehicle_number',
                           'driver_id','driver_name','mobile_num','order_nums'];
}
