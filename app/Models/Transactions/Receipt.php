<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['receipt_num','receipt_date','route_id','customer_id','customer_name',
                           'amount','mode','receipt_data','denomination','bank_id','trans_num','remarks',
                           'aggregate_amt','advance_amt','excess_amt','status'];

    public function route()
    {
        return $this->belongsTo('App\Models\Places\MRoute','route_id','id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }

    public function bank()
    {
        return $this->belongsTo('App\Models\Masters\BankMaster','bank_id','id');
    }

    public function denominationDetails()
    {
        return $this->belongsTo('App\Models\Transactions\BatchDenomination', 'denomination', 'id');
    }
}
