<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ObjectDetection extends Model
{
    protected $fillable = [
        'image',
        'type',
        'distance',
        'size',
        'latitude',
        'longitude',
    ];
}
