<?php

namespace App\Models\Profiles;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['name','code','role_id','manager_id','dob','gender',
                           'user_name','password','photo','remarks',
                           'address','district','state','landmark','pincode',
                           'mobile_num','alternate_num','email_id',
                           'father_name','aadhaar_num','license_num',
                           'license_validity','blood_group','doj',
                           'bank_name','branch','ifsc','acc_holder','acc_number'];
                           
    public function role()
    {
        return $this->belongsTo('App\Models\Profiles\Designation','role_id','id');
    }
}
