<?php

namespace App\Models\Transactions;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Bank;
use App\Enums\PaymentType;
use Carbon\Carbon;

class BankPayment extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $fillable = [
        'document_number',
        'payment_date',
        'payment_type',
        'bank_id',
        'bank_name',
        'total_amount',
        'reference_numbers'
    ];

    protected $casts = [
        'payment_date'      => 'date',
        'payment_type'      => PaymentType::class,
        'reference_numbers' => 'array',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'bank_id');
    }

    public function getPaymentDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    public function getPaymentDateForInput()
    {
        return Carbon::parse($this->payment_date)->format('Y-m-d');
    }

    public function getPaymentDateForExcel()
    {
        return Carbon::parse($this->payment_date)->format('d/m/Y');
    }
}
