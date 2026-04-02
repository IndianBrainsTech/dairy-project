<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveItem extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['incentive_number','item_id','item_name','qty','inc_rate','inc_amt','lkg_qty','lkg_amt'];
}
