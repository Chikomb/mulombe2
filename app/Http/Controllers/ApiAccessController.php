<?php

namespace App\Http\Controllers;

use App\Models\ApiAccess;
use App\Models\Apiuser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ApiAccessController extends Controller
{

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

                return response()->json($custom_request, 200);

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
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\ApiAccess  $apiAccess
     * @return \Illuminate\Http\Response
     */
    public function show(ApiAccess $apiAccess)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\ApiAccess  $apiAccess
     * @return \Illuminate\Http\Response
     */
    public function edit(ApiAccess $apiAccess)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\ApiAccess  $apiAccess
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ApiAccess $apiAccess)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\ApiAccess  $apiAccess
     * @return \Illuminate\Http\Response
     */
    public function destroy(ApiAccess $apiAccess)
    {
        //
    }
}
