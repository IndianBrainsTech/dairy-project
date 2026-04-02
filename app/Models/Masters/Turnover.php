<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Turnover extends Model
{
    use HasFactory;
    protected $table = "customer_turnover";
    protected $primaryKey = 'id';
    protected $fillable = ['customer_id','customer_name','amount','txn_date','status'];
}
