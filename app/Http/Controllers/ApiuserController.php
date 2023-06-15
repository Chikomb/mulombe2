<?php

namespace App\Http\Controllers;

use App\Models\Apiuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiuserController extends Controller
{
    //api credentials
    public function authenticate_user(Request $request){
        $request->validate([
            'client_access' => 'required',
            'access_secret' => 'required'
        ]);

        //check if the client_access exists or not
        if(Apiuser::where('client_access', $request->client_access)->count() > 0){
            //get user details
            $api_user = Apiuser::where('client_access', $request->client_access)->first();
            //check if the access_secret is correct
            if(Hash::check($request->access_secret,$api_user->access_secret)){
                //re-generate access token
                $token = $api_user->createToken('survey_akros')->plainTextToken;

                $custom_request = [
                    "success" => true,
                    "message" => "access granted successfully",
                    "token" => $token,
                    "datetime" =>now()
                ];

                return response()->json($custom_request, 400);

            }else{
                $custom_request = [
                    "success" => false,
                    "message" => "invalid credentials"
                ];

                return response()->json($custom_request, 400);
            }

        }else{
            $custom_request = [
                "success" => false,
                "message" => "invalid credentials"
            ];

            return response()->json($custom_request, 404);
        }
    }
}
