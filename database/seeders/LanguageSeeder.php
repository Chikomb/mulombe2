<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('languages')->insert([
           [
               "name" => "English",
               "is_active" => 1
           ],
            [
                "name" => "Nyanja",
                "is_active" => 1
            ],
            [
                "name" => "Bemba",
                "is_active" => 1
            ],
            [
                "name" => "Tonga",
                "is_active" => 1
            ],
            [
                "name" => "Kaonde",
                "is_active" => 1
            ],
            [
                "name" => "Lunda",
                "is_active" => 1
            ],
            [
                "name" => "Luvale",
                "is_active" => 1
            ]
        ]);
    }
}
