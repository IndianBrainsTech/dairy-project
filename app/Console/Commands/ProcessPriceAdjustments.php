<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Masters\Pricing\PriceMaster;
use App\Models\Masters\Pricing\PriceAdjustment;
use App\Enums\Status;

class ProcessPriceAdjustments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:process-adjustments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process pending price adjustments based on effect date';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $today = today();
            $processedCount = 0;

            $adjustments = PriceAdjustment::where('status', Status::PENDING)
                ->whereDate('effect_date', '<=', $today)
                ->get();

            if ($adjustments->isEmpty()) {
                $this->info('No pending adjustments to process.');
                return Command::SUCCESS;
            }

            foreach ($adjustments as $adjustment) {

                // Idempotency check
                if ($adjustment->status !== Status::PENDING) {
                    continue;
                }

                DB::transaction(function () use ($adjustment, &$processedCount) {

                    $masterIds = array_map('intval', $adjustment->masters_list);

                    // adjustment_data assumed already JSON → array
                    $adjustmentData = $adjustment->adjustment_data;

                    $this->adjustPriceMasters($masterIds, $adjustmentData);

                    $adjustment->update([
                        'status' => Status::PROCESSED,
                        'processed_at' => now(),
                    ]);

                    $processedCount++;
                });
            }

            $this->info("Processed {$processedCount} adjustment(s).");

            return Command::SUCCESS;

        } catch (\Throwable $e) {

            \Log::error('Price Adjustment Processing Failed', [
                'error' => $e->getMessage(),
            ]);

            $this->error('Processing failed: ' . $e->getMessage());

            return Command::FAILURE;
        }
    }

    private function adjustPriceMasters($masterIds, $adjustmentData)
    {
        PriceMaster::whereIn('id', $masterIds)->chunkById(50, function ($masters) use ($adjustmentData) {
            foreach ($masters as $master) {
                $prices = $master->price_list;

                foreach ($adjustmentData as $productId => $adjust) {
                    // Skip if product not present in this master
                    if (! array_key_exists($productId, $prices)) {
                        continue;
                    }

                    // Convert price and adjustment to floats
                    $current = (float) $prices[$productId];
                    $delta   = (float) $adjust;

                    // Apply increase / decrease
                    $prices[$productId] = $current + $delta;
                }

                // Save back to DB
                $master->price_list = $prices;
                $master->save();
            }
        });
    }
}
