<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UOM extends Model
{
    use HasFactory;
    protected $table = "units";
    protected $primaryKey = 'id';    
    protected $fillable = ['unit_name','display_name','hot_key','tally_sync'];
}
