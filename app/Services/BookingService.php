<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class BookingService
{
    /**
     * Create a new booking with atomic database constraints.
     * The unique constraint on (schedule_id, seat_id) ensures double-booking is physically impossible.
     *
     * @param User $user
     * @param Schedule $schedule
     * @param Seat $seat
     * @param float $price
     * @return Booking
     * @throws \Exception
     */
    public function createBooking(User $user, Schedule $schedule, Seat $seat, float $price): Booking
    {
        return DB::transaction(function () use ($user, $schedule, $seat, $price) {
            // Validate seat belongs to the train in the schedule
            if ($seat->train_id !== $schedule->train_id) {
                throw new \Exception('Seat does not belong to the scheduled train.');
            }

            // Check seat is available (final validation before booking)
            if (!$seat->isAvailable()) {
                throw new \Exception('Seat is no longer available.');
            }

            // Check schedule departure hasn't passed
            if ($schedule->departure->isPast()) {
                throw new \Exception('Cannot book for a past departure.');
            }

            // Attempt to create booking
            // The database unique constraint (schedule_id, seat_id) will prevent duplicate bookings
            $booking = Booking::create([
                'user_id' => $user->id,
                'schedule_id' => $schedule->id,
                'seat_id' => $seat->id,
                'price' => $price,
                'status' => 'confirmed',
                'booking_reference' => Booking::generateReference(),
                'booked_at' => now(),
            ]);

            // Update seat status
            $seat->update(['status' => 'occupied']);

            // Update schedule available seats
            $schedule->update([
                'available_seats' => $schedule->getAvailableSeatsCount(),
            ]);

            return $booking->load(['user', 'schedule.train', 'seat']);
        });
    }

    /**
     * Cancel a booking.
     */
    
    public function cancelBooking(Booking $booking, string $reason = ''): bool
    {
        return DB::transaction(function () use ($booking, $reason) {
            if (!$booking->isActive()) {
                throw new \Exception('Only active bookings can be cancelled.');
            }

            // Update booking status
            $booking->update([
                'status' => 'cancelled',
            ]);

            // Free up the seat
            $booking->seat->update(['status' => 'available']);

            // Update schedule available seats
            $booking->schedule->update([
                'available_seats' => $booking->schedule->getAvailableSeatsCount(),
            ]);

            return true;
        });
    }

    /**
     * Get available seats for a schedule.
     */
    public function getAvailableSeats(Schedule $schedule): array
    {
        $bookedSeatIds = Booking::where('schedule_id', $schedule->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('seat_id')
            ->toArray();

        return $schedule->train->seats()
            ->whereNotIn('id', $bookedSeatIds)
            ->get()
            ->toArray();
    }

    /**
     * Check if a specific seat is available for a schedule.
     */
    public function isSeatAvailable(Schedule $schedule, Seat $seat): bool
    {
        $booking = Booking::where('schedule_id', $schedule->id)
            ->where('seat_id', $seat->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->first();

        return is_null($booking);
    }

    /**
     * Create a booking for a user (Staff/Admin function).
     * Allows staff to book tickets for customers.
     *
     * @param User $user
     * @param Schedule $schedule
     * @param Seat $seat
     * @param float $price
     * @return Booking
     * @throws \Exception
     */
    public function createBookingForUser(User $user, Schedule $schedule, Seat $seat, float $price): Booking
    {
        // Use the same logic as createBooking - validates everything
        return $this->createBooking($user, $schedule, $seat, $price);
    }
}
