<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoUpload extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';        
    protected $fillable = ['emp_id','tag','tag_id','name','description','upload_datetime'];
}
