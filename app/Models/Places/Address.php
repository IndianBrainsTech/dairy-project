<?php

namespace App\Models\Places;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['customer_id','customer_name','address_lines','district','state','pincode'];
}
