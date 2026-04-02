<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashDenomination extends Model
{
    use HasFactory;
    protected $table = "cash_denomination"; 
    protected $primaryKey = 'id';
    protected $fillable = ['edate','amount','denomination'];
}
