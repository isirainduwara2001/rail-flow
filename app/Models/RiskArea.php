<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiskArea extends Model
{
    protected $fillable = [
        'heading',
        'description',
        'status',
        'latitude',
        'longitude',
    ];
}
