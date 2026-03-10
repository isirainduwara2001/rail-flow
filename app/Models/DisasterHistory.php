<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DisasterHistory extends Model
{
    protected $fillable = [
        'latitude',
        'longitude',
        'city',
        'risk_level',
    ];
}
