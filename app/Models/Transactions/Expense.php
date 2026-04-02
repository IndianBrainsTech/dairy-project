<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $guarded = [];
    
    public function ExDeno()
    {
        return $this->belongsTo('App\Models\Transactions\ExpenseDenomination','denomination','id');
    }
    
}
