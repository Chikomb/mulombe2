<?php

namespace App\Http\Resources;

use App\Models\DataSurvey;
use App\Models\TotalQuestion;
use Illuminate\Http\Resources\Json\JsonResource;

class SurveryStatisticsResource extends JsonResource
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
            'survey_channel' => [
                'USSD' => [
                    'total participants' => DataSurvey::where('channel', 'USSD')->groupBy('phone_number')->count(),
                    'total questions answered' => DataSurvey::where('channel', 'USSD')->groupBy('question_number')->count(),
                    'total questions asked' => TotalQuestion::where('channel', 'USSD')->first()->total_questions
                ],
                'SMS' => [
                    'total participants' => DataSurvey::where('channel', 'SMS')->groupBy('phone_number')->count(),
                    'total questions answered' => DataSurvey::where('channel', 'SMS')->groupBy('question_number')->count(),
                    'total questions asked' => TotalQuestion::where('channel', 'SMS')->first()->total_questions
                ],
                'WhatsApp' => [
                    'total participants' => DataSurvey::where('channel', 'WhatsApp')->groupBy('phone_number')->count(),
                    'total questions answered' => DataSurvey::where('channel', 'WhatsApp')->groupBy('question_number')->count(),
                    'total questions asked' => TotalQuestion::where('channel', 'WhatsApp')->first()->total_questions
                ],
                'IVR' => [
                    'total participants' => DataSurvey::where('channel', 'IVR')->groupBy('phone_number')->count(),
                    'total questions answered' => DataSurvey::where('channel', 'IVR')->groupBy('question_number')->count(),
                    'total questions asked' => TotalQuestion::where('channel', 'IVR')->first()->total_questions
                ],
            ]
        ];
    }
}
