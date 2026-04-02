<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchDenomination extends Model
{
    use HasFactory;
    protected $table = "batch_denomination";
    protected $primaryKey = 'id';
    protected $fillable = ['route_id','receipt_date','receipt_numbers','amount','denomination'];

    public function route()
    {
        return $this->belongsTo('App\Models\Places\MRoute','route_id','id');
    }
}
