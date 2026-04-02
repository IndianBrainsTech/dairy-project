<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Masters\DateEntrySetting;

class DateEntrySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $records = [
            [
                'module' => 'RECEIPT',
                'tag'    => 'CASH',
                'days_before' => 0,
                'days_after'  => 0,
            ],
            [
                'module' => 'RECEIPT',
                'tag'    => 'BANK',
                'days_before' => 0,
                'days_after'  => 0,
            ],
        ];
        
        foreach ($records as $record) {
            DateEntrySetting::updateOrCreate([
                'module' => $record['module'],
                'tag' => $record['tag'],
            ], $record);
        }
    }
}
