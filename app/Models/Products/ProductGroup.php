<?php

namespace App\Models\Products;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductGroup extends Model
{
    use HasFactory;    
    protected $primaryKey = 'id';    
    protected $fillable = ['name','tally_sync'];
}
