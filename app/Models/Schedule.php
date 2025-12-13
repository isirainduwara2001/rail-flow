<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_DELAYED = 'delayed';
    public const STATUS_DEPARTED = 'departed';
    public const STATUS_ARRIVED = 'arrived';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'train_id',
        'from',
        'to',
        'departure',
        'arrival',
        'available_seats',
        'status',
    ];

    protected $casts = [
        'departure' => 'datetime',
        'arrival' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the train for this schedule.
     */
    public function train(): BelongsTo
    {
        return $this->belongsTo(Train::class);
    }

    /**
     * Get all bookings for this schedule.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Get the count of confirmed bookings.
     */
    public function getConfirmedBookingsCount(): int
    {
        return $this->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->count();
    }

    /**
     * Get available seats for this schedule.
     */
    public function getAvailableSeatsCount(): int
    {
        return $this->train->total_seats - $this->getConfirmedBookingsCount();
    }
}
