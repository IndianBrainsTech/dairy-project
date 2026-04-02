<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enquiry extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['shop_name','shop_type','area_id','area_name',
                           'address','landmark','remarks','followup_date',
                           'contact_num','contact_name','alternate_num','alternate_name',
                           'enq_datetime','latitude','longitude',
                           'emp_id','customer_id','conversion_status'];
    
    public function employee()
    {
        return $this->belongsTo('App\Models\Profiles\Employee','emp_id','id');
    }

    public function customer()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','customer_id','id');
    }
}
