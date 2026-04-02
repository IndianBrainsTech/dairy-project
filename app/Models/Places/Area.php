<?php

namespace App\Models\Places;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['name','route_id'];

    public function route()
    {
        return $this->belongsTo('App\Models\Places\MRoute','route_id','id');
    }
}
