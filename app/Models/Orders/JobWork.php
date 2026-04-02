<?php

namespace App\Models\Orders;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobWork extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['job_work_num','job_work_date','job_work_dt',
                           'customer_id','customer_name','customer_data',
                           'route_id','route_name','vehicle_id','vehicle_num',
                           'driver_id','driver_name','driver_mobile_num',
                           'item_count','tot_amt','round_off','net_amt',
                           'job_work_status','invoice_status','cancel_remarks'];

    public function items()
    {
        return $this->hasMany(JobWorkItem::class, 'job_work_num', 'job_work_num');
    }
}
