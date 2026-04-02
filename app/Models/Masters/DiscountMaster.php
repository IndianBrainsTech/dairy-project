<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountMaster extends Model
{
    use HasFactory;
    protected $table = "discount_master";
    protected $primaryKey = 'id';
    protected $fillable = ['txn_id','txn_date','effect_date','narration','customer_ids','discount_list','status'];
}
