<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Train extends Model
{
    protected $fillable = [
        'name',
        'train_number',
        'total_seats',
        'description',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all seats for this train.
     */
    
    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }

    /**
     * Get all seat classes for this train.
     */
    public function seatClasses(): HasMany
    {
        return $this->hasMany(SeatClass::class);
    }

    /**
     * Get all schedules for this train.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Get available seats count.
     */
    public function getAvailableSeatsCount(): int
    {
        return $this->seats()
            ->where('status', 'available')
            ->count();
    }
}
