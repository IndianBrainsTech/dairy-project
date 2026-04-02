<?php

namespace App\Models\Profiles;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Customer extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $fillable = ['customer_name','customer_code','group','route_id','area_id',
                           'address_lines','district','state','landmark','pincode',
                           'contact_num','contact_name','alternate_num','alternate_name',
                           'email_id','staff_id','remarks',
                           'billing_name','credit_limit','gst_type','gst_number','pan_number',
                           'outstanding','incentive_mode','payment_mode',
                           'tcs_status','tds_status','link_customer','link_cust_id','customer_since',
                           'owner_name','gender','dob','aadhaar',
                           'profile_image','shop_photo',
                           'bank_name','branch','ifsc','acc_holder','acc_number','tally_sync'];

    public function route()
    {
        return $this->belongsTo('App\Models\Places\MRoute','route_id','id');
    }

    public function area()
    {
        return $this->belongsTo('App\Models\Places\Area','area_id','id');
    }
/*
    public function link_cust()
    {
        return $this->belongsTo('App\Models\Profiles\Customer','link_cust_id','id');
    }
*/    
}
