<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Transport\PetrolBunk;
use App\Models\Transport\Vehicle;
use App\Models\Profiles\Employee;
use App\Models\Places\MRoute;
use App\Models\User;
use App\Enums\Status;
use Carbon\Carbon;

class DieselBill extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';

    /**
     * The table associated with the model.
     */
    protected $table = 'diesel_bills';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['document_number','document_date','bunk_id','bunk_name','bill_number','bill_date',
        'route_id','route_name','vehicle_id','vehicle_number','driver_id','driver_name',
        'fuel','rate','amount','opening_km','closing_km','running_km','kmpl','status',
        'created_by','updated_by','actioned_by','actioned_at'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'document_date' => 'date',
        'bill_date'     => 'date',
        'fuel'          => 'decimal:2',
        'rate'          => 'decimal:2',
        'amount'        => 'decimal:2',
        'kmpl'          => 'decimal:2',
        'status'        => Status::class,
    ];

    /**
     * Get the petrol bunk associated with this diesel bill.
     */
    public function bunk()
    {
        return $this->belongsTo(PetrolBunk::class, 'bunk_id');
    }

    /**
     * Get the route associated with this diesel bill.
     */
    public function route()
    {
        return $this->belongsTo(MRoute::class, 'route_id');
    }

    /**
     * Get the vehicle associated with this diesel bill.
     */
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class, 'vehicle_id');
    }

    /**
     * Get the driver (employee) associated with this diesel bill.
     */
    public function driver()
    {
        return $this->belongsTo(Employee::class, 'driver_id');
    }

    /**
     * Get the user who created the diesel bill.
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the diesel bill.
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user who actioned the diesel bill.
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

    public function getBillDateAttribute($value)
    {
        return Carbon::parse($value)->format('d-m-Y');
    }

    /**
     * Custom method to get Y-m-d format (for editing or APIs)
     */
    public function getDocumentDateForInput(): string
    {
        return Carbon::parse($this->document_date)->format('Y-m-d');
    }

    public function getBillDateForInput(): string
    {
        return Carbon::parse($this->bill_date)->format('Y-m-d');
    }
}
