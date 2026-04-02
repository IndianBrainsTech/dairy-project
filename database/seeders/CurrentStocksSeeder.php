<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Products\Product;
use App\Models\Stocks\CurrentStock;

class CurrentStocksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Fetch all products with their primary unit
        $products = Product::with('primaryUnit')->get();

        DB::transaction(function () use ($products) {
            foreach ($products as $product) {                
                $exists = CurrentStock::where('item_id', $product->id)->exists();
                if (! $exists) {
                    CurrentStock::create([                        
                        'item_id'        => $product->id,
                        'item_name'      => $product->name,
                        'unit_id'        => $product->primaryUnit->unit_id,
                        'opening_qty'    => 0,
                        'production_qty' => 0,
                        'sales_qty'      => 0,
                        'return_qty'     => 0,
                        'current_stock'  => 0,
                    ]);
                }
            }
        });
    }
}
