<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentivePayment extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['incentive_id','payment_mode','payment_date','reference_num','amount','payment_status'];
}
