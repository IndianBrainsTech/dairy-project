<?php

namespace App\Models\Masters\Pricing;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Models\Profiles\Customer;
use App\Models\Products\Product;
use App\Enums\PriceMasterStatus;
use Carbon\Carbon;

class PriceMaster extends Model
{
    use HasFactory;

    protected $table = 'price_masters';

    /**
     * Mass assignable attributes.
     */
    protected $fillable = [
        'document_number',
        'document_date',
        'effect_date',
        'narration',
        'customer_ids',
        'price_list',
        'parent_id',
        'status',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'document_date' => 'date',
        'effect_date'   => 'date',
        'customer_ids'  => 'array',
        'price_list'    => 'array',
        'status'        => PriceMasterStatus::class,
    ];

    /**
     * Default attribute values.
     */
    protected $attributes = [
        'status' => 'ACTIVE',
    ];

    /**
     * Custom attribute: document date formatted for UI (Y-m-d)
     */
    protected function documentDateForForm(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->document_date?->format('Y-m-d')
        );
    }

    /**
     * Custom attribute: effect date formatted for UI (Y-m-d)
     */
    protected function effectDateForForm(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->effect_date?->format('Y-m-d')
        );
    }

    /**
     * Custom attribute: document date formatted for display (d-m-Y)
     */
    protected function documentDateForDisplay(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->document_date?->format('d-m-Y')
        );
    }

    /**
     * Custom attribute: effect date formatted for display (d-m-Y)
     */
    protected function effectDateForDisplay(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->effect_date?->format('d-m-Y')
        );
    }

    /**
     * Custom attribute: get associated customers
     */
    protected function associatedCustomers(): Attribute
    {
        return Attribute::make(
            get: fn (): Collection => empty($this->customer_ids)
                ? collect()
                : Customer::whereIn('id', $this->customer_ids)
                    ->select('id', 'customer_name')
                    ->orderBy('customer_name')
                    ->get()
        );
    }

    /**
     * Custom attribute: get associated products with price
     */
    protected function associatedProducts(): Attribute
    {
        return Attribute::make(
            get: fn (): Collection => empty($this->price_list)
                ? collect()
                : Product::whereIn('id', array_keys($this->price_list))
                    ->orderBy('display_index')
                    ->select('id', 'name')
                    ->get()
                    ->map(fn ($product) => [
                        'id'    => $product->id,
                        'name'  => $product->name,
                        'price' => $this->price_list[$product->id] ?? null,
                    ])
        );
    }

    /**
     * Check if a customer is included in this price master.
     */
    public function hasCustomer(int $customerId): bool
    {
        return in_array($customerId, $this->customer_ids ?? [], true);
    }

    /**
     * Get price for a given product ID.
     */
    public function getPriceForProduct(int $productId): ?float
    {
        return $this->price_list[$productId] ?? null;
    }
}
