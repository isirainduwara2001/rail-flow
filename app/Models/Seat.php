<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Seat extends Model
{
    protected $fillable = [
        'train_id',
        'seat_number',
        'class',
        'facilities',
        'status',
    ];

    protected $casts = [
        'facilities' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the train that owns this seat.
     */
    
    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    /**
     * Check if seat is available.
     */
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}
