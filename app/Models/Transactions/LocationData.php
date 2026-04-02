<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationData extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['emp_id','latitude','longitude','tag','title','description'];

    public function employee()
    {
        return $this->belongsTo('App\Models\Profiles\Employee','emp_id','id'); 
    }
}
