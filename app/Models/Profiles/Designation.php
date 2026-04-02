<?php

namespace App\Models\Profiles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Designation extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['role_name','short_name','role_nature','department','reporting_roles'];
}
