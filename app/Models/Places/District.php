<?php

namespace App\Models\Places;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';    
    protected $fillable = ['name','state_id'];

    public function state()
    {
        return $this->belongsTo('App\Models\Places\State','state_id','id');
    }
}
