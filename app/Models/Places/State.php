<?php

namespace App\Models\Places;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class State extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';    
    protected $fillable = ['name'];
    // public $timestamps = true;
    // public $incrementing = true;
}
