<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class UpdateGst extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:gst';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'GST update done effect from Sep 22, 2025';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        DB::statement("UPDATE products SET gst=5, sgst=2.5, cgst=2.5, igst=5 WHERE gst=12");
        DB::statement("UPDATE gst_master SET gst=5, sgst=2.5, cgst=2.5, igst=5 WHERE gst=12");
        $this->info('GST updated successfully.');
        return Command::SUCCESS;
    }
}
