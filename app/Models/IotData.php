<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IotData extends Model
{
    protected $table = 'iot_data';

    protected $fillable = [
        'sf_front_distance',
        'sf_side_distance',
        't_front_distance',
        'temperature',
        'humidity',
        'lux',
        'rain_percentage',
        'latitude',
        'longitude',
        'speed',
    ];
}
