<?php

namespace App\Models;

use App\Http\Middleware\Authenticate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;

class API extends Authenticate
{
    use HasFactory,HasApiTokens;

    protected $fillable = [
        "request_from_api",
        "response",
        "response_status",
        "status_reason" //success //ip not allowed
    ];
}
