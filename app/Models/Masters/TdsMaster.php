<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdsMaster extends Model
{
    use HasFactory;
    protected $table = "tds_master";
    protected $primaryKey = 'id';
    protected $fillable = ['effect_date','tds_limit','with_pan','without_pan'];
}
