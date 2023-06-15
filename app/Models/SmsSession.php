<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmsSession extends Model
{
    use HasFactory;

    protected $fillable = [
        "phone_number",
        "case_no",
        "step_no",
        "session_id",
        "status",
        "language_id"
    ];
}
