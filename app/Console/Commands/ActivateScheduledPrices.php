<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Masters\Pricing\PriceMaster;
use App\Enums\PriceMasterStatus;

class ActivateScheduledPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'price:activate-scheduled';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Activate scheduled price masters';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        DB::transaction(function () {

            $dueMasters = PriceMaster::query()
                ->where('status', PriceMasterStatus::SCHEDULED)
                ->whereDate('effect_date', '<=', now())
                ->lockForUpdate()
                ->get();

            foreach ($dueMasters as $scheduled) {

                // 1️⃣ Supersede currently ACTIVE master
                PriceMaster::findOrFail($scheduled->parent_id)->update([
                    'status' => PriceMasterStatus::SUPERSEDED,
                ]);

                // 2️⃣ Activate scheduled master
                $scheduled->update([
                    'status' => PriceMasterStatus::ACTIVE,
                ]);
            }
        });

        $this->info('Activation of scheduled price masters done successfully.');
        return Command::SUCCESS;
    }
}
