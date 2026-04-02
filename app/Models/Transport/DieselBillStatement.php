<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transport\PetrolBunk;
use App\Models\Transport\DieselBill;
use App\Models\User;
use App\Enums\Status;
use App\Enums\PaymentStatus;
use Carbon\Carbon;

class DieselBillStatement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'diesel_bill_statements';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'document_number',
        'document_date',
        'bunk_id',
        'bunk_name',
        'from_date',
        'to_date',
        'item_ids',
        'item_count',
        'total_fuel',
        'total_running_km',
        'average_kmpl',
        'average_rate',
        'total_amount',
        'tds_amount',
        'round_off',
        'net_amount',
        'status',
        'payment_status',
        'created_by',
        'actioned_by',
        'actioned_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'document_date'    => 'date',
        'from_date'        => 'date',
        'to_date'          => 'date',
        'item_ids'         => 'array',
        'total_fuel'       => 'decimal:2',
        'total_running_km' => 'decimal:2',
        'average_kmpl'     => 'decimal:2',
        'average_rate'     => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'tds_amount'       => 'decimal:2',
        'round_off'        => 'decimal:2',
        'net_amount'       => 'decimal:2',
        'status'           => Status::class,
        'payment_status'   => PaymentStatus::class,
    ];

    /**
     * Ensure accessor always included in JSON/Array output.
     */    
    protected $appends = [
        'period',
    ];

    /**
     * Get the related bunk.
     */
    public function bunk()
    {
        return $this->belongsTo(PetrolBunk::class, 'bunk_id');
    }

    /**
     * Get the user who created the diesel bill statement.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who actioned the diesel bill statement.
     */
    public function actionedBy()
    {
        return $this->belongsTo(User::class, 'actioned_by');
    }

    /**
     * Accessor: default display format (for Blade, JSON, etc.)
     */
    public function getDocumentDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    /**
     * Accessor: diesel bill records
     */
    public function getDieselBillsAttribute()
    {
        return DieselBill::whereIn('id', $this->item_ids ?? [])->get();
    }

    /**
     * Accessor: show from-to dates as period
     */
    public function getPeriodAttribute()
    {
        return formatDateRangeAsDMY($this->from_date, $this->to_date, "");
    }

    /**
     * Accessor: net amount without precision
     */
    public function getNetAmountAttribute($value)
    {
        return (int) round($value);
    }

    /**
     * Custom method to get Y-m-d format (for UI)
     */
    public function getDocumentDateForInput()
    {
        return Carbon::parse($this->document_date)->format('Y-m-d');
    }

    /**
     * Custom method to get from-to dates as period.
     * Useful for titles (single date will be shown with 'Date : ' prefix) 
     */
    public function getPeriod()
    {
        return formatDateRangeAsDMY($this->from_date, $this->to_date);
    }
}
