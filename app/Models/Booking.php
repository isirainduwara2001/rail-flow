<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'seat_id',
        'price',
        'status',
        'booking_reference',
        'booked_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'booked_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user who made the booking.
     */
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the schedule for this booking.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /**
     * Get the seat for this booking.
     */
    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    /**
     * Check if booking is active.
     */
    public function isActive(): bool
    {
        return in_array($this->status, ['confirmed', 'pending']);
    }

    /**
     * Generate a unique booking reference.
     */
    public static function generateReference(): string
    {
        return 'BK' . strtoupper(uniqid());
    }
}
