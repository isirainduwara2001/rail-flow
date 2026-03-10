<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Booking;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PassengerInformController extends Controller
{
    /**
     * Display the passenger informs dashboard.
     */
    public function index(Request $request)
    {
        $query = Schedule::with('train')
            ->withCount(['bookings as passenger_count' => function ($q) {
                $q->whereIn('status', ['confirmed', 'pending']);
            }]);

        // Apply filters
        if ($request->filled('train_id')) {
            $query->where('train_id', $request->train_id);
        }
        if ($request->filled('from')) {
            $query->where('from', 'like', '%' . $request->from . '%');
        }
        if ($request->filled('to')) {
            $query->where('to', 'like', '%' . $request->to . '%');
        }

        $schedules = $query->orderBy('departure', 'desc')->get();
        $trains = \App\Models\Train::all();

        return view('admin.passenger-informs.index', compact('schedules', 'trains'));
    }

    /**
     * Send a notification to passengers of a specific schedule.
     */
    public function send(Request $request, SmsService $smsService): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'message' => 'required|string|max:160',
        ]);

        $schedule = Schedule::with('train')->findOrFail($validated['schedule_id']);
        $bookings = $schedule->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->with('user')
            ->get();

        $successCount = 0;
        $failCount = 0;

        foreach ($bookings as $booking) {
            if ($booking->user && $booking->user->phone) {
                if ($smsService->sendSms($booking->user->phone, $validated['message'])) {
                    $successCount++;
                } else {
                    $failCount++;
                }
            }
        }

        if ($successCount === 0 && $bookings->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => "Failed to send any messages. Check your SMS configuration.",
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => "Notification process completed. Sent: {$successCount}, Failed: {$failCount}.",
        ]);
    }
}
