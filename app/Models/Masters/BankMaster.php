<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BankMaster extends Model
{
    use HasFactory;
    protected $table = "bank_account"; 
    protected $primaryKey = 'id';
    protected $fillable = ['bank_name','acc_holder','acc_number','ifsc','branch','display_name']; 
}
