<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'phone_number',
        'telecom_operator',
        'channel',
        'question_number',
        'question',
        'answer',
        'answer_value',
        'data_category' //demo or live
    ];
}
