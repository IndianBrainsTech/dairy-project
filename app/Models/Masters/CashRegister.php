<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashRegister extends Model
{
    use HasFactory;
    protected $table = "cash_register"; 
    protected $primaryKey = 'id';
    protected $fillable = ['record_date','opening_amount','receipt_amount','expense_amount','closing_amount',
                           'opening_denomination','receipt_denomination','expense_denomination','closing_denomination'];
}
