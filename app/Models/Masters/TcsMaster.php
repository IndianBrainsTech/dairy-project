<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TcsMaster extends Model
{
    use HasFactory;
    protected $table = "tcs_master"; 
    protected $primaryKey = 'id';
    protected $fillable = ['effect_date','tcs_limit','with_pan','without_pan'];
}
