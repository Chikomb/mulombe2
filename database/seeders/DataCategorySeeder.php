<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('data_categories')->insert([
            [
                'name' => 'live',
                'is_active' => 0
            ],
            [
                'name' => 'demo',
                'is_active' => 1
            ]
        ]);
    }
}
