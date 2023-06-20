<?php

namespace App\Http\Controllers;

use App\Http\Resources\SurveryStatisticsResource;
use App\Http\Resources\SurveyResource;
use App\Models\DataSurvey;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class APIController extends Controller
{
    //change access_secret

    //get all surveys
    public function all_surveys()
    {
        DB::statement("SET sql_mode = ''");

        $surveys = DataSurvey::groupBy('phone_number')->get();
        $participant_count = DataSurvey::groupBy('phone_number')->count();
        $custom_response = [
            'success' => true,
            'message' => 'All Survey Records',
            'total participants' => $participant_count,
            'data' => SurveyResource::collection($surveys),
        ];

        return response()->json($custom_response, 200);
    }

    public function filter_survey(Request $request)
    {
        DB::statement("SET sql_mode = ''");

        if(!empty($request->start_date) && !empty($request->end_date)){
            if(!empty($request->channel) && !empty($request->telecom_operator)){
                //both channel and operator
                //date range
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);

                $surveys = DataSurvey::whereBetween('created_at', [$startDate, $endDate])->where('channel', $request->channel)->where('telecom_operator', $request->telecom_operator)->
                groupBy('phone_number')->get();

                $custom_response = [
                    'success' => true,
                    'message' => 'Filtered by date range from ' . $request->start_date . " to " . $request->end_date.", for ".$request->channel." channel and ".$request->telecom_operator." mobile operator",
                    'data' => SurveyResource::collection($surveys),
                ];

                return response()->json($custom_response, 200);

            }elseif(!empty($request->channel)){
                //get by channel
                //date range
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);

                $surveys = DataSurvey::whereBetween('created_at', [$startDate, $endDate])->where('channel', $request->channel)->
                groupBy('phone_number')->get();

                $custom_response = [
                    'success' => true,
                    'message' => 'Filtered by date range from ' . $request->start_date . " to " . $request->end_date.", for ".$request->channel." channel.",
                    'data' => SurveyResource::collection($surveys),
                ];

                return response()->json($custom_response, 200);
            }elseif(!empty($request->telecom_operator)){
                //get by mobile operator
                //date range
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);

                $surveys = DataSurvey::whereBetween('created_at', [$startDate, $endDate])->where('telecom_operator', $request->telecom_operator)->
                groupBy('phone_number')->get();

                $custom_response = [
                    'success' => true,
                    'message' => 'Filtered by date range from ' . $request->start_date . " to " . $request->end_date.", for ".$request->telecom_operator." mobile operator",
                    'data' => SurveyResource::collection($surveys),
                ];

                return response()->json($custom_response, 200);
            }else{
                //date range
                $startDate = Carbon::parse($request->start_date);
                $endDate = Carbon::parse($request->end_date);

                $surveys = DataSurvey::whereBetween('created_at', [$startDate, $endDate])->
                groupBy('phone_number')->get();

                $custom_response = [
                    'success' => true,
                    'message' => 'Filtered by date range from ' . $request->start_date . " to " . $request->end_date,
                    'data' => SurveyResource::collection($surveys),
                ];

                return response()->json($custom_response, 200);
            }
        }elseif(!empty($request->channel) && !empty($request->telecom_operator)){
        //both channel and operator
            $surveys = DataSurvey::where('channel', $request->channel)->where('telecom_operator', $request->telecom_operator)->
            groupBy('phone_number')->get();

            $custom_response = [
                'success' => true,
                'message' => 'Filtered by ' .$request->channel." channel and ".$request->telecom_operator." mobile operator",
                'data' => SurveyResource::collection($surveys),
            ];

            return response()->json($custom_response, 200);

        }elseif(!empty($request->channel)){
        //get by channel
            $surveys = DataSurvey::where('channel', $request->channel)->
            groupBy('phone_number')->get();

            $custom_response = [
                'success' => true,
                'message' => 'Filtered by ' .$request->channel." channel",
                'data' => SurveyResource::collection($surveys),
            ];

            return response()->json($custom_response, 200);
        }elseif(!empty($request->telecom_operator)){
        //get by mobile operator
            $surveys = DataSurvey::where('telecom_operator', $request->telecom_operator)->
            groupBy('phone_number')->get();

            $custom_response = [
                'success' => true,
                'message' => 'Filtered by ' .$request->telecom_operator." mobile operator",
                'data' => SurveyResource::collection($surveys),
            ];

            return response()->json($custom_response, 200);
        }else{
            $surveys = DataSurvey::groupBy('phone_number')->get();
            return response()->json([
                'success' => true,
                'message' => 'Unfiltered Data',
                'data' => SurveyResource::collection($surveys),
            ]);
        }

    }

    public function statistics()
    {
        DB::statement("SET sql_mode = ''");
        $surveys = DataSurvey::groupBy('phone_number')->get();

        return SurveryStatisticsResource::collection($surveys);
    }
}
