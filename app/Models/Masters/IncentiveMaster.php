<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveMaster extends Model
{
    use HasFactory;
    protected $table = "incentive_master";
    protected $primaryKey = 'id';
    protected $fillable = ['txn_id','txn_date','effect_date','narration','customer_ids',
                           'incentive_type','incentive_rate','slab_data','incentive_data','status'];
}
