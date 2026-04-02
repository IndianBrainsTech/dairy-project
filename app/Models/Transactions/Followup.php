<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Followup extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['enquiry_id','emp_id','remarks','next_visit_date',
                           'followup_status','followup_datetime','latitude','longitude'];

    public function employee()
    {
        return $this->belongsTo('App\Models\Profiles\Employee','emp_id','id');
    }
    
    public function enquiry()
    {
        return $this->belongsTo('App\Models\Transactions\Enquiry','enquiry_id','id');
    }
}
