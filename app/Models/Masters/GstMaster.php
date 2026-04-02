<?php

namespace App\Models\Masters;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GstMaster extends Model
{
    use HasFactory;
    protected $table = "gst_master";
    protected $primaryKey = 'id';
    protected $fillable = ['hsn_code','description','tax_type','gst','sgst','cgst','igst'];
}
