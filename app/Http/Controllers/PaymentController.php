<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Seat;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }
    

    /**
     * Show the checkout page.
     */
    public function checkout(Request $request)
    {
        $scheduleId = $request->query('schedule_id');
        $seatIds = explode(',', $request->query('seat_ids', ''));

        if (!$scheduleId || empty($seatIds)) {
            return redirect()->route('booking.search')->with('error', 'Invalid booking request.');
        }

        $schedule = Schedule::with('train')->findOrFail($scheduleId);
        $seats = Seat::whereIn('id', $seatIds)->get();

        $totalPrice = 0;
        $seatClasses = $schedule->train->seatClasses;
        $seatClassPrices = [];
        foreach ($seatClasses as $class) {
            $seatClassPrices[strtolower($class->class_name)] = $class->price;
            $seatClassPrices[$class->class_name] = $class->price;
        }

        // Calculate total and prepare seat summaries
        $seatSummaries = $seats->map(function($seat) use ($seatClassPrices, &$totalPrice) {
            $price = $seatClassPrices[strtolower($seat->class)] ?? $seatClassPrices[$seat->class] ?? 0;
            $totalPrice += $price;
            return [
                'id' => $seat->id,
                'number' => $seat->seat_number,
                'class' => $seat->class,
                'price' => $price
            ];
        });

        return view('payment.checkout', compact('schedule', 'seatSummaries', 'totalPrice', 'scheduleId', 'seatIds'));
    }

    /**
     * Process the simulated payment.
     */
    public function process(Request $request)
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'seat_ids' => 'required|string', // Comma separated IDs
            'card_name' => 'required|string',
            'card_number' => 'required|string',
        ]);

        $scheduleId = $validated['schedule_id'];
        $seatIds = explode(',', $validated['seat_ids']);
        $schedule = Schedule::findOrFail($scheduleId);
        $seats = Seat::whereIn('id', $seatIds)->get();
        $seatClasses = $schedule->train->seatClasses;
        $seatClassPrices = [];
        foreach ($seatClasses as $class) {
            $seatClassPrices[strtolower($class->class_name)] = $class->price;
            $seatClassPrices[$class->class_name] = $class->price;
        }

        try {
            return DB::transaction(function () use ($schedule, $seats, $seatClassPrices) {
                $bookings = [];

                foreach ($seats as $seat) {
                    $price = $seatClassPrices[strtolower($seat->class)] ?? $seatClassPrices[$seat->class] ?? 0;

                    // Verify availability again
                    if (!$this->bookingService->isSeatAvailable($schedule, $seat)) {
                        throw new \Exception("Seat {$seat->seat_number} is no longer available.");
                    }

                    // Create booking
                    $booking = $this->bookingService->createBooking(
                        Auth::user(),
                        $schedule,
                        $seat,
                        $price
                    );
                    $bookings[] = $booking;
                }

                // Store booking IDs in session for the success page
                session(['last_bookings' => collect($bookings)->pluck('id')->toArray()]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful! Redirecting...',
                    'redirect' => route('payment.success')
                ]);
            });
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment failed: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Show the success page.
     */
    public function success()
    {
        $bookingIds = session('last_bookings', []);
        
        if (empty($bookingIds)) {
            return redirect()->route('booking.my-bookings');
        }

        $bookings = Booking::with(['schedule.train', 'seat'])->whereIn('id', $bookingIds)->get();
        
        // Clear the session after viewing
        // session()->forget('last_bookings');

        return view('payment.success', compact('bookings'));
    }
}
