<?php

namespace App\Models\Places;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MRoute extends Model
{
    use HasFactory;
    protected $table = "routes";
    protected $primaryKey = 'id';    
    protected $fillable = ['name','district_id','tally_sync'];

    public function district()
    {
        return $this->belongsTo('App\Models\Places\District','district_id','id');
    }
}
