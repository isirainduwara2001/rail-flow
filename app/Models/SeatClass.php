<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeatClass extends Model
{
    protected $fillable = [
        'train_id',
        'class_name',
        'seat_count',
        'price',
        'description',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the train that owns this seat class.
     */
    
    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    /**
     * Get all seats for this class.
     */
    public function seats()
    {
        return $this->train->seats()->where('class', $this->class_name);
    }
}
