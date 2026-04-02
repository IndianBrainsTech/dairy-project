<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use App\Models\Stocks\CurrentStock;
use App\Models\Products\ProductUnit;
use App\Enums\StockAction;
use Carbon\Carbon;
use InvalidArgumentException;

/**
 * Service class for managing stock transactions.
 *
 * This service provides methods for:
 *  - Converting product quantities into their primary unit
 *  - Updating stock balances in the `current_stocks` table
 *  - Handling bulk updates efficiently with minimal database queries
 *
 * Optimizations:
 *  - Loads all relevant product units in one query
 *  - Loads all current stock records in one query
 *  - Applies updates in memory
 *  - Persists changes using a single bulk `upsert` query
 *
 * Typical usage example:
 *
 * ```php
 * use App\Services\StockService;
 * use App\Enums\StockAction;
 *
 * $service = app(StockService::class);
 *
 * // Add production
 * $service->updateCurrentStock(
 *     StockAction::PRODUCTION,
 *     [
 *         ['item_id' => 10, 'unit_id' => 3, 'qty' => 5],
 *         ['item_id' => 12, 'unit_id' => 1, 'qty' => 20],
 *     ]
 * );
 *
 * // Record sales
 * $service->updateCurrentStock(
 *     StockAction::SALES,
 *     [
 *         ['item_id' => 10, 'unit_id' => 2, 'qty' => 7],
 *     ]
 * );
 * ```
 */
class StockService
{
    /**
     * Update current stock quantities with optimized queries.
     *
     * @param \App\Enums\StockAction $action
     *        The type of stock operation to perform. 
     *        Allowed values are:
     *          - StockAction::OPENING
     *          - StockAction::PRODUCTION
     *          - StockAction::SALES
     *          - StockAction::RETURN
     *          - StockAction::RETURN_ORDER
     *
     * @param array $data  
     *        An array of stock updates.  
     *        Each element should be an associative array with keys:
     *          - int    item_id : The product ID to update
     *          - int    unit_id : The unit ID of the provided quantity
     *          - float  qty     : The quantity in the given unit
     *
     * @return void
     *
     * @throws \InvalidArgumentException If a product-unit mapping is invalid
     */
    public function updateCurrentStock(StockAction $action, array $data): void    
    {        
        $itemIds = collect($data)->pluck('item_id')->unique();

        DB::transaction(function () use ($action, $data, $itemIds) {
            // Fetch all stocks in one query
            $stocks = CurrentStock::whereIn('item_id', $itemIds)
                ->get()
                ->keyBy('item_id');

            // Fetch all product_units in one query
            $productUnits = ProductUnit::whereIn('product_id', $itemIds)->get()
                ->groupBy('product_id');

            // Apply updates in memory
            foreach ($data as $row) {
                $itemId = $row['item_id'];
                $qty    = (float) $row['qty'];

                $convertedQty = isset($row['unit_id'])
                    ? $this->convertToPrimaryUnitFromCache($itemId, $row['unit_id'], $qty, $productUnits)
                    : $qty;

                $stock = $stocks->get($itemId);
                if (! $stock) {
                    continue; // record missing
                }

                switch ($action) {
                    case StockAction::OPENING:
                        $stock->opening_qty += $convertedQty;
                        $stock->current_stock += $convertedQty;
                        break;

                    case StockAction::PRODUCTION:
                        $stock->production_qty += $convertedQty;
                        $stock->current_stock += $convertedQty;
                        break;

                    case StockAction::SALES:
                        $stock->sales_qty += $convertedQty;
                        $stock->current_stock -= $convertedQty;
                        break;

                    case StockAction::RETURN:
                        $stock->return_qty += $convertedQty;
                        $stock->current_stock += $convertedQty;
                        break;

                    case StockAction::RETURN_ORDER:
                        $stock->sales_qty -= $convertedQty;
                        $stock->current_stock += $convertedQty;
                        break;
                }
            }

            // Bulk update in one query
            CurrentStock::upsert(
                $stocks->map(fn ($s) => [
                    'item_id'        => $s->item_id,
                    'item_name'      => 'Test',
                    'unit_id'        => 1,
                    'opening_qty'    => $s->opening_qty,
                    'production_qty' => $s->production_qty,
                    'sales_qty'      => $s->sales_qty,
                    'return_qty'     => $s->return_qty,
                    'current_stock'  => $s->current_stock,
                    'updated_at'     => now(),
                ])->values()->toArray(),
                ['item_id'], // use item_id as the unique key
                ['opening_qty', 'production_qty', 'sales_qty', 'return_qty', 'current_stock', 'updated_at']
            );
        });
    }

    /**
     * Convert quantity to primary unit using preloaded product_units.
     *
     * @param int $productId  The ID of the product whose unit conversion is required
     * @param int $unitId     The unit ID in which the quantity is currently given
     * @param float $qty      The quantity in the given unit
     * @param \Illuminate\Support\Collection $productUnits
     *        A grouped collection of ProductUnit models (keyed by product_id),
     *        preloaded to avoid repeated database queries
     *
     * @return float          The converted quantity in the product's primary unit
     *
     * @throws \InvalidArgumentException If no units exist for the product or the unitId is invalid
     */
    private function convertToPrimaryUnitFromCache(int $productId, int $unitId, float $qty, Collection $productUnits): float 
    {
        $units = $productUnits->get($productId);

        if (! $units) {
            throw new InvalidArgumentException("No units found for product {$productId}");
        }

        $unit = $units->firstWhere('unit_id', $unitId);

        if (! $unit) {
            throw new InvalidArgumentException("Unit {$unitId} not found for product {$productId}");
        }

        return $unit->prim_unit
            ? $qty
            : $qty * (float) $unit->conversion;
    }
}