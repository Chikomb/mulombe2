<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/token', [App\Http\Controllers\ApiuserController::class,'authenticate_user']);
Route::get('/whatsapp', [App\Http\Controllers\WhatsAppSessionController::class,'WhatsApp_Verify']);
Route::post('/whatsapp', [App\Http\Controllers\WhatsAppSessionController::class,'WhatsApp_Bot']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/surveys',[App\Http\Controllers\APIController::class,'all_surveys']);
    Route::post('/survey/filter',[App\Http\Controllers\APIController::class,'filter_survey']);
    Route::get('/survey/statistics',[App\Http\Controllers\APIController::class,'statistics']);
});
