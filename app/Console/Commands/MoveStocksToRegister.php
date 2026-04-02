<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Stocks\CurrentStock;
use App\Models\Stocks\StockRegister;
use App\Models\Stocks\StockJobsLog;
use Carbon\Carbon;
use Throwable;

class MoveStocksToRegister extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stocks:move-to-register';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Move today\'s current_stocks into stock_register as closing balances';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $date = Carbon::today()->toDateString();
        try {
            DB::transaction(function () use ($date){
                $currentStocks = CurrentStock::get();
                $records = [];
                $now = now();

                foreach ($currentStocks as $stock) {
                    $records[] = [
                        'record_date'    => $date,
                        'item_id'        => $stock->item_id,
                        'item_name'      => $stock->item_name,
                        'unit_id'        => $stock->unit_id,
                        'opening_qty'    => $stock->opening_qty,
                        'production_qty' => $stock->production_qty,
                        'sales_qty'      => $stock->sales_qty,
                        'return_qty'     => $stock->return_qty,
                        'closing_qty'    => $stock->current_stock,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }

                // Insert into stock_register
                StockRegister::insert($records);

                // Reset quantities in current_stocks
                CurrentStock::query()->update([
                    'opening_qty'    => DB::raw('current_stock'),
                    'production_qty' => 0,
                    'sales_qty'      => 0,
                    'return_qty'     => 0,
                    // current_stock remains unchanged
                ]);

                // Log success
                StockJobsLog::create([
                    'job_date'        => $date,
                    'status'          => 'success',
                    'processed_count' => $currentStocks->count(),
                    'message'         => 'Stock transfer completed successfully',
                ]);
            });

            $this->info('Current stocks moved to stock_register successfully.');
            return Command::SUCCESS;
        }
        catch (Throwable $e) {
            // Log failure
            StockJobsLog::create([
                'job_date' => $date,
                'status'   => 'failed',
                'message'  => $e->getMessage(),
            ]);

            $this->error('Failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
