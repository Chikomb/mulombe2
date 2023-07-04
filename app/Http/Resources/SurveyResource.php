<?php

namespace App\Http\Resources;

use App\Models\DataSurvey;
use App\Models\TotalQuestion;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class SurveyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {

        return [
            'reference_id' => $this->id,
            'phone_number' => $this->phone_number,
            'survey_channel' => [
                'USSD' => $this->sesssions($this->phone_number, 'USSD'),
                'SMS' => $this->sesssions($this->phone_number, 'SMS'),
                'WhatsApp' => $this->sesssions($this->phone_number, 'WhatsApp'),
                'IVR' => $this->sesssions($this->phone_number, 'IVR'),
            ]
        ];
    }

    function sesssions($phone_number, $channel)
    {
        $records = DataSurvey::where('phone_number', $phone_number)->where('channel', $channel)->groupBy('session_id')->get();
        $custom_response = [];

        foreach ($records as $group) {
            // Access the grouped records
            $session_id = $group->session_id;
            $session_record = DataSurvey::where('phone_number', $phone_number)->where('channel', $channel)->where('session_id', $session_id)->with('language')->select('telecom_operator','language_id','question_number', 'question', 'answer','answer_value', 'data_category','created_at','updated_at')->get();
            $answered_questions = DataSurvey::where('phone_number', $phone_number)->where('channel', $channel)->groupBy('session_id')->count();
            $total_questions = TotalQuestion::where('channel', $channel)->first()->total_questions;

            $session_array_block = [];

            foreach ($session_record as $record) {
                $session_array_block[] = [
                    "telecom_operator" => $record->telecom_operator,
                    "language" => $record->language->name,
                    "question_number" => $record->question_number,
                    "question" => $record->question,
                    "answer" => $record->answer,
                    "answer_value" => $record->answer_value,
                    "data_category" => $record->data_category,
                    "created_at" => $record->created_at,
                    "updated_at" => $record->updated_at,
                ];
            }

            $custom_response[] = [
                "session id" => $session_id,
                "questions answered" => $answered_questions,
                "total questions" => $total_questions,
                "survey progress" => $answered_questions."/".$total_questions,
                "session surveys" => $session_array_block
            ];
        }

        return $custom_response;
    }



}
