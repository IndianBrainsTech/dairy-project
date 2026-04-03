<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SupplierTransporter extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'supplier_transporters';

    protected $fillable = [
        'name',
        'contact_person',
        'phone',
        'alt_phone',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'gst_number',
        'pan_number',
        'bank_name',
        'bank_account_number',
        'bank_ifsc',
        'bank_branch',
        'status',
        'remarks',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vehicles()
    {
        return $this->hasMany(Vehicle::class, 'supplier_transporter_id');
    }

    public function transportBills()
    {
        return $this->hasMany(TransportBill::class, 'supplier_transporter_id');
    }

    public function secondaryTransports()
    {
        return $this->hasMany(SecondaryTransport::class, 'supplier_transporter_id');
    }

    public function secondaryTransportBills()
    {
        return $this->hasMany(SecondaryTransportBill::class, 'supplier_transporter_id');
    }

    public function secondaryPaymentAbstracts()
    {
        return $this->hasMany(SecondaryPaymentAbstract::class, 'supplier_transporter_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'updated_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    // ── Accessors ────────────────────────────────────────────────────────────

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->address,
            $this->city,
            $this->state,
            $this->pincode,
        ])->filter()->implode(', ');
    }
}
