<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $rows = [
            ['category'=>'Invoice', 'key'=>'sales-invoice', 'value'=>'2324S-'],
            ['category'=>'Invoice', 'key'=>'tax-invoice',   'value'=>'2324T-'],
            ['category'=>'Invoice', 'key'=>'bulk-milk',     'value'=>'2324B-'],
            ['category'=>'Invoice', 'key'=>'conversion',    'value'=>'2324C-'],
            ['category'=>'Invoice', 'key'=>'order',         'value'=>'2324O-']
        ];

        DB::table('settings')->insert($rows);
    }
}
