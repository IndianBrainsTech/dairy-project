<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transport\DieselBillStatement;
use App\Models\User;
use App\Enums\Status;
use Carbon\Carbon;

class DieselBillPayment extends Model
{
    use HasFactory;

    protected $table = 'diesel_bill_payments';

    protected $fillable = [
        'statement_id',
        'request_date',
        'amount',
        'status',
        'created_by',
        'actioned_by',
        'actioned_at',
    ];

    protected $casts = [
        'request_date' => 'date',
        'actioned_at'  => 'datetime',
        'amount'       => 'decimal:2',
        'status'       => Status::class,
    ];

    /**
     * Relationship: DieselBillPayment belongs to a DieselBillStatement
     */
    public function statement()
    {
        return $this->belongsTo(DieselBillStatement::class, 'statement_id');
    }

    /**
     * Relationship: DieselBillPayment created by a User
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: DieselBillPayment actioned by a User
     */
    public function actioner()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    /**
     * Accessor: default display format (for Blade, JSON, etc.)
     */
    public function getRequestDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    /**
     * Accessor: amount without precision
     */
    public function getAmountAttribute($value)
    {
        return (int) round($value);
    }
}
