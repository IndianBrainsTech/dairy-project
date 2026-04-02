<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentivePayout extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['incentive_number','customer_id','document_date','amount',
                           'payout_mode','reference_number','payout_status','approval_date'];

    public function incentiveNumber()
    {
        return $this->belongsTo('App\Models\Transactions\Incentive','incentive_number','incentive_number');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }
}
