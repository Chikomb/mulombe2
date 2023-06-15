<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DataSurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('data_surveys')->insert([
            [
                'session_id' => '1xxxxxxxx20041',
                'phone_number' => '260978000000',
                'telecom_operator' => 'Airtel',
                'channel' => 'ussd',
                'question_number' => '1',
                'question' => 'Do we have your consent?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '1xxxxxxxx20041',
                'phone_number' => '260978000000',
                'telecom_operator' => 'Airtel',
                'channel' => 'ussd',
                'question_number' => '2',
                'question' => 'Are you Vaccinated?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '1xxxxxxxx20041',
                'phone_number' => '260978000000',
                'telecom_operator' => 'Airtel',
                'channel' => 'ussd',
                'question_number' => '3',
                'question' => 'Which province do you stay in?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '1xxxxxxxx20041',
                'phone_number' => '260978000000',
                'telecom_operator' => 'Airtel',
                'channel' => 'ussd',
                'question_number' => '4',
                'question' => 'Which district do you stay?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '1xxxxxxxx20041',
                'phone_number' => '260978000000',
                'telecom_operator' => 'Airtel',
                'channel' => 'ussd',
                'question_number' => '5',
                'question' => 'What is your gender?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ]

        ]);
    }
}
