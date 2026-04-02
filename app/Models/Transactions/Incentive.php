<?php

namespace App\Models\Transactions; 

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incentive extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['incentive_number','incentive_date','customer_id','customer_name',
                           'from_date','to_date','incentive_total','leakage_total',
                           'tds_amount','round_off','net_amount','incentive_status','payment_status'];

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }
}
