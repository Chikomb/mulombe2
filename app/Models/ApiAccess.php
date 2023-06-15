<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiAccess extends Model
{
    use HasFactory;

    protected $fillable = [
        "ip_address",
        "is_active",
        "is_deleted"
    ];
}
