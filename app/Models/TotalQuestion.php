<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'channel',
        'total_questions'
    ];
}
