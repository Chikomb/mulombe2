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
                'channel' => 'USSD',
                'language_id' => 1,
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
                'channel' => 'USSD',
                'language_id' => 1,
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
                'channel' => 'USSD',
                'language_id' => 1,
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
                'channel' => 'USSD',
                'language_id' => 1,
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
                'channel' => 'USSD',
                'language_id' => 1,
                'question_number' => '5',
                'question' => 'What is your gender?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            //whatsapp
            [
                'session_id' => '12xxxxxxx20041',
                'phone_number' => '260978000001',
                'telecom_operator' => 'Airtel',
                'channel' => 'WhatApp',
                'language_id' => 2,
                'question_number' => '1',
                'question' => 'Do we have your consent?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '12xxxxxxx20041',
                'phone_number' => '260978000001',
                'telecom_operator' => 'Airtel',
                'channel' => 'WhatsApp',
                'language_id' => 2,
                'question_number' => '2',
                'question' => 'Are you Vaccinated?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '12xxxxxxx20041',
                'phone_number' => '260978000001',
                'telecom_operator' => 'Airtel',
                'channel' => 'WhatsApp',
                'language_id' => 2,
                'question_number' => '3',
                'question' => 'Which province do you stay in?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '12xxxxxxx20041',
                'phone_number' => '260978000001',
                'telecom_operator' => 'Airtel',
                'channel' => 'WhatsApp',
                'language_id' => 2,
                'question_number' => '4',
                'question' => 'Which district do you stay?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '12xxxxxxx20041',
                'phone_number' => '260978000001',
                'telecom_operator' => 'Airtel',
                'channel' => 'WhatsApp',
                'language_id' => 3,
                'question_number' => '5',
                'question' => 'What is your gender?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            //sms
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '1',
                'question' => 'Do we have your consent?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '2',
                'question' => 'Are you Vaccinated?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '3',
                'question' => 'Which province do you stay in?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '4',
                'question' => 'Which district do you stay?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '5',
                'question' => 'What is your gender?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            //IVR
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '1',
                'question' => 'Do we have your consent?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '2',
                'question' => 'Are you Vaccinated?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '3',
                'question' => 'Which province do you stay in?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '4',
                'question' => 'Which district do you stay?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'session_id' => '13xxxxxxx20041',
                'phone_number' => '260978000007',
                'telecom_operator' => 'Airtel',
                'channel' => 'SMS',
                'language_id' => 3,
                'question_number' => '5',
                'question' => 'What is your gender?',
                'answer' => 1,
                'answer_value' => 'Yes',
                'data_category' => 'demo',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }
}
