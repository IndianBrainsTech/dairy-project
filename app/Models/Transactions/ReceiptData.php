<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReceiptData extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    protected $fillable = ['receipt_id','receipt_date','invoice_number','amount','receipt_status'];

    public function receipt()
    {
        return $this->belongsTo('App\Models\Transactions\Receipt','receipt_id','id');
    }
}
