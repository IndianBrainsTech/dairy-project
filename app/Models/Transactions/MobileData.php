<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MobileData extends Model
{
    use HasFactory, SoftDeletes;
    protected $primaryKey = 'id';
    protected $fillable = ['user_id','mobile_num','app_version','model','android_version','unique_code'];

    public function user()
    {
        return $this->belongsTo('App\Models\Profiles\Employee','user_id','id'); 
    }
}
