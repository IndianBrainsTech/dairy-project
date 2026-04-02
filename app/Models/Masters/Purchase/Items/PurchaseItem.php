<?php

namespace App\Models\Masters\Purchase\Items;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Masters\Purchase\Items\PurchaseItemGroup;
use App\Enums\MasterStatus;
use App\Enums\TallySyncStatus;
use App\Enums\TaxType;
use App\Models\User;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_items';

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

    protected $fillable = [
        'code',
        'name',
        'group_id',
        'hsn_code',
        'tax_type',
        'gst',
        'sgst',
        'cgst',
        'igst',
        'sort_order',
        'tally_sync_status',
        'status',
        'created_by',
        'updated_by',
    ];

    /*
    |--------------------------------------------------------------------------
    | Attribute Casting
    |--------------------------------------------------------------------------
    */

    protected $casts = [
        'gst'        => 'decimal:2',
        'sgst'       => 'decimal:2',
        'cgst'       => 'decimal:2',
        'igst'       => 'decimal:2',
        'sort_order' => 'integer',
        'tax_type'   => TaxType::class,
        'tally_sync_status' => TallySyncStatus::class,
        'status'     => MasterStatus::class,
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function group()
    {
        return $this->belongsTo(PurchaseItemGroup::class, 'group_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MasterStatus::ACTIVE);
    }

    public function scopeTaxable(Builder $query): Builder
    {
        return $query->where('tax_type', TaxType::TAXABLE);
    }

    public function scopeExempted(Builder $query): Builder
    {
        return $query->where('tax_type', TaxType::EXEMPTED);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getIsActiveAttribute(): bool
    {
        return $this->status === MasterStatus::ACTIVE;
    }

    public function getIsTaxableAttribute(): bool
    {
        return $this->tax_type === TaxType::TAXABLE;
    }

    /*
    |--------------------------------------------------------------------------
    | Model Events
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (PurchaseItem $item) {
            $item->created_by = auth()->id();            
        });

        static::updating(function (PurchaseItem $item) {
            $item->updated_by = auth()->id();
        });

        static::created(function (PurchaseItem $item) {
            $item->update(['sort_order' => $item->id]);
        });
    }
}
