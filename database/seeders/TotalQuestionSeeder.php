<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TotalQuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('total_questions')->insert([
            [
                'channel' => 'USSD',
                'total_questions' => 20
            ],
            [
                'channel' => 'SMS',
                'total_questions' => 20
            ],
            [
                'channel' => 'IVR',
                'total_questions' => 20
            ],
            [
                'channel' => 'WhatsApp',
                'total_questions' => 20
            ]
        ]);
    }
}
