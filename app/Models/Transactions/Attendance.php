<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['emp_id','attn_date','attn_session','time_in','time_out',
                           'latitude_in','longitude_in','latitude_out','longitude_out','remarks'];
    
    public function employee()
    {
        return $this->belongsTo('App\Models\Profiles\Employee','emp_id','id');
    }

}
