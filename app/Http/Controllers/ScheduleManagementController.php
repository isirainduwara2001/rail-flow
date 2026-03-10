<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\Train;
use App\Models\Seat;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class ScheduleManagementController extends Controller
{
    /**
     * Display schedule management dashboard.
     */
    public function index()
    {
        $trains = Train::all(['id', 'name', 'train_number']);
        return view('admin.schedules.index', compact('trains'));
    }

    /**
     * Get schedules data for DataTables.
     */
    public function getSchedulesData(Request $request): JsonResponse
    {
        $query = Schedule::with('train')
            ->withCount(['bookings as confirmed_bookings' => function ($q) {
                $q->whereIn('status', ['confirmed', 'pending']);
            }]);

        // Filter by train if provided
        if ($request->has('train_id') && $request->train_id !== null) {
            $query->where('train_id', $request->train_id);
        }

        return DataTables::of($query)
            ->addColumn('action', function (Schedule $schedule) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary edit-schedule" data-id="' . $schedule->id . '" title="Edit">
                            <i class="material-icons">edit</i>
                        </button>
                        <button class="btn btn-outline-danger delete-schedule" data-id="' . $schedule->id . '" title="Delete">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('train_name', function (Schedule $schedule) {
                return $schedule->train->name . ' (' . $schedule->train->train_number . ')';
            })
            ->addColumn('route', function (Schedule $schedule) {
                return $schedule->from . ' → ' . $schedule->to;
            })
            ->addColumn('available_seats', function (Schedule $schedule) {
                $available = $schedule->getAvailableSeatsCount();
                $total = $schedule->train->total_seats;
                return $available . ' / ' . $total;
            })
            ->editColumn('departure', function (Schedule $schedule) {
                return $schedule->departure->format('Y-m-d H:i');
            })
            ->editColumn('arrival', function (Schedule $schedule) {
                return $schedule->arrival->format('Y-m-d H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show schedule creation form.
     */
    public function create()
    {
        $trains = Train::all(['id', 'name', 'train_number']);
        return view('admin.schedules.create', compact('trains'));
    }

    /**
     * Store a new schedule.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'train_id' => 'required|exists:trains,id',
            'from' => 'required|string|max:100',
            'to' => 'required|string|max:100|different:from',
            'departure' => 'required|date_format:Y-m-d H:i|after:now',
            'arrival' => 'required|date_format:Y-m-d H:i|after:departure',
        ]);

        $train = Train::findOrFail($validated['train_id']);

        $schedule = Schedule::create([
            'train_id' => $train->id,
            'from' => $validated['from'],
            'to' => $validated['to'],
            'departure' => $validated['departure'],
            'arrival' => $validated['arrival'],
            'available_seats' => $train->total_seats,
            'status' => Schedule::STATUS_SCHEDULED,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Schedule created successfully.',
            'schedule' => $schedule->load('train'),
        ]);
    }

    /**
     * Show schedule edit form.
     */
    public function edit(Schedule $schedule)
    {
        $trains = Train::all(['id', 'name', 'train_number']);
        return view('admin.schedules.edit', compact('schedule', 'trains'));
    }

    /**
     * Update schedule details.
     */
    public function update(Request $request, Schedule $schedule, SmsService $smsService): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'required|string|max:100',
            'to' => 'required|string|max:100|different:from',
            'departure' => 'required|date_format:Y-m-d H:i',
            'arrival' => 'required|date_format:Y-m-d H:i|after:departure',
            'status' => 'required|string|in:scheduled,delayed,departed,arrived,cancelled',
        ]);

        $oldStatus = $schedule->status;

        // If bookings exist, only allow status updates
        if ($schedule->bookings()->count() > 0) {
            $onlyStatus = ['status' => $validated['status']];
            $schedule->update($onlyStatus);

            // Trigger notifications if status changed to delayed
            if ($validated['status'] === Schedule::STATUS_DELAYED && $oldStatus !== Schedule::STATUS_DELAYED) {
                $this->notifyPassengersOfDelay($schedule, $smsService);
            }

            return response()->json([
                'success' => true,
                'message' => 'Schedule status updated. Detailed info cannot be changed as bookings exist.',
                'schedule' => $schedule->load('train'),
            ]);
        }

        $schedule->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully.',
            'schedule' => $schedule->load('train'),
        ]);
    }

    /**
     * Notify passengers of train delay.
     */
    protected function notifyPassengersOfDelay(Schedule $schedule, SmsService $smsService): void
    {
        $bookings = $schedule->bookings()->whereIn('status', ['confirmed', 'pending'])->with('user')->get();

        foreach ($bookings as $booking) {
            if ($booking->user && $booking->user->phone) {
                $message = "RailFlow Alert: Your train {$schedule->train->name} (Departure: {$schedule->departure->format('H:i')}) from {$schedule->from} to {$schedule->to} has been DELAYED. We apologize for the inconvenience.";
                $smsService->sendSms($booking->user->phone, $message);
            }
        }
    }

    /**
     * Delete a schedule.
     */
    public function destroy(Schedule $schedule): JsonResponse
    {
        // Only allow deletion if no bookings exist
        if ($schedule->bookings()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a schedule with existing bookings.',
            ], 422);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully.',
        ]);
    }

    /**
     * Get schedule details with seat availability.
     */
    public function getScheduleDetails(Schedule $schedule): JsonResponse
    {
        $bookedSeats = $schedule->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('seat_id')
            ->toArray();

        $availableSeats = $schedule->getAvailableSeatsCount();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $schedule->id,
                'train_id' => $schedule->train_id,
                'from' => $schedule->from,
                'to' => $schedule->to,
                'departure' => $schedule->departure->format('Y-m-d H:i'),
                'arrival' => $schedule->arrival->format('Y-m-d H:i'),
            ],
            'booked_seats' => $bookedSeats,
            'available_seats' => $availableSeats,
        ]);
    }
}
