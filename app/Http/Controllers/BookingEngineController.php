<?php

namespace App\Http\Controllers;

use App\Helpers\StationHelper;
use App\Models\Booking;
use App\Models\IotData;
use App\Models\Schedule;
use App\Models\Seat;
use App\Models\User;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class BookingEngineController extends Controller
{
    protected BookingService $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    /**
     * Display booking search page.
     */
    public function search()
    {
        return view('booking.search');
    }

    /**
     * Search available schedules.
     */
    public function searchSchedules(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from' => 'required|string|max:100',
            'to' => 'required|string|max:100|different:from',
            'departure_date' => 'required|date|after:today',
            'passengers' => 'required|integer|min:1|max:10',
        ]);

        $schedules = Schedule::where('from', 'LIKE', '%' . $validated['from'] . '%')
            ->where('to', 'LIKE', '%' . $validated['to'] . '%')
            ->whereDate('departure', $validated['departure_date'])
            ->where('available_seats', '>=', $validated['passengers'])
            ->with(['train.seatClasses', 'bookings' => function ($q) {
                $q->whereIn('status', ['confirmed', 'pending']);
            }])
            ->get()
            ->map(function ($schedule) {
                // Get min and max prices from seat classes
                $prices = $schedule->train->seatClasses()->pluck('price');
                $minPrice = $prices->min() ?? 0;
                $maxPrice = $prices->max() ?? 0;

                return [
                    'id' => $schedule->id,
                    'train_name' => $schedule->train->name,
                    'train_number' => $schedule->train->train_number,
                    'from' => $schedule->from,
                    'to' => $schedule->to,
                    'departure' => $schedule->departure->format('Y-m-d H:i'),
                    'arrival' => $schedule->arrival->format('Y-m-d H:i'),
                    'min_price' => $minPrice,
                    'max_price' => $maxPrice,
                    'available_seats' => $schedule->getAvailableSeatsCount(),
                    'total_seats' => $schedule->train->total_seats,
                ];
            });

        return response()->json([
            'success' => true,
            'schedules' => $schedules,
        ]);
    }

    /**
     * Display seat picker for a schedule.
     */
    public function selectSeats(Schedule $schedule)
    {
        $bookedSeats = $schedule->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('seat_id')
            ->toArray();

        $seats = $schedule->train->seats()
            ->orderBy('seat_number')
            ->get()
            ->map(function ($seat) use ($bookedSeats) {
                return [
                    'id' => $seat->id,
                    'number' => $seat->seat_number,
                    'class' => $seat->class,
                    'available' => !in_array($seat->id, $bookedSeats),
                    'facilities' => $seat->facilities,
                ];
            });

        return view('booking.seat-picker', [
            'schedule' => $schedule,
            'seats' => $seats,
            'booked_seats' => $bookedSeats,
        ]);
    }

    /**
     * Get seat data for interactive picker (AJAX).
     */
    public function getSeatData(Schedule $schedule): JsonResponse
    {
        $bookedSeats = $schedule->bookings()
            ->whereIn('status', ['confirmed', 'pending'])
            ->pluck('seat_id')
            ->toArray();

        $seatClasses = $schedule->train->seatClasses()->get();
        $seatClassPrices = [];
        foreach ($seatClasses as $class) {
            // Map both the class_name and its lowercase version for lookup
            $seatClassPrices[strtolower($class->class_name)] = $class->price;
            $seatClassPrices[$class->class_name] = $class->price;
        }

        $seats = $schedule->train->seats()
            ->orderBy('seat_number')
            ->get()
            ->map(function ($seat) use ($bookedSeats, $seatClassPrices) {
                $classKey = strtolower($seat->class);
                $price = $seatClassPrices[$classKey] ?? $seatClassPrices[$seat->class] ?? 0;
                return [
                    'id' => $seat->id,
                    'number' => $seat->seat_number,
                    'class' => ucfirst($seat->class),
                    'price' => $price,
                    'available' => !in_array($seat->id, $bookedSeats),
                    'facilities' => $seat->facilities,
                ];
            });

        return response()->json([
            'success' => true,
            'schedule' => [
                'id' => $schedule->id,
                'from' => $schedule->from,
                'to' => $schedule->to,
                'departure' => $schedule->departure->format('Y-m-d H:i'),
                'arrival' => $schedule->arrival->format('Y-m-d H:i'),
            ],
            'seat_classes' => $seatClasses->map(fn($class) => [
                'class_name' => $class->class_name,
                'price' => $class->price,
                'description' => $class->description,
            ])->toArray(),
            'seats' => $seats,
        ]);
    }

    /**
     * Book a ticket (create booking).
     */
    public function bookTicket(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'schedule_id' => 'required|exists:schedules,id',
            'seat_id' => 'required|exists:seats,id',
            'price' => 'required|numeric|min:0.01',
        ]);

        try {
            $schedule = Schedule::findOrFail($validated['schedule_id']);
            $seat = Seat::findOrFail($validated['seat_id']);

            // Validate seat availability one final time
            if (!$this->bookingService->isSeatAvailable($schedule, $seat)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This seat has just been booked. Please select another.',
                ], 422);
            }

            // Create booking
            $booking = $this->bookingService->createBooking(
                Auth::user(),
                $schedule,
                $seat,
                $validated['price']
            );

            return response()->json([
                'success' => true,
                'message' => 'Ticket booked successfully!',
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'seat' => $booking->seat->seat_number,
                    'price' => $booking->price,
                    'schedule' => [
                        'from' => $booking->schedule->from,
                        'to' => $booking->schedule->to,
                        'departure' => $booking->schedule->departure->format('Y-m-d H:i'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Display my bookings.
     */
    public function myBookings()
    {
        $bookings = Auth::user()->bookingHistory()->get();

        return view('booking.my-bookings', compact('bookings'));
    }

    /**
     * Display all bookings (admin/staff view).
     */
    public function allBookings()
    {
        return view('admin.bookings.index');
    }

    /**
     * Get all bookings data for DataTables (admin/staff).
     */
    public function getAllBookingsData(): JsonResponse
    {
        $query = Booking::with(['user', 'schedule.train', 'seat']);

        // Apply filters
        if (request()->has('train_id') && request()->train_id) {
            $query->whereHas('schedule', function($q) {
                $q->where('train_id', request()->train_id);
            });
        }

        if (request()->has('status') && request()->status) {
            $query->where('status', request()->status);
        }

        return DataTables::of($query)
            ->addColumn('reference', function (Booking $booking) {
                return $booking->booking_reference;
            })
            ->addColumn('user_name', function (Booking $booking) {
                return $booking->user->name;
            })
            ->addColumn('train_name', function (Booking $booking) {
                return $booking->schedule->train->name;
            })
            ->addColumn('route', function (Booking $booking) {
                return $booking->schedule->from . ' → ' . $booking->schedule->to;
            })
            ->addColumn('seat_number', function (Booking $booking) {
                return $booking->seat->seat_number;
            })
            ->addColumn('amount', function (Booking $booking) {
                return 'LKR ' . number_format($booking->price, 2);
            })
            ->editColumn('status', function (Booking $booking) {
                $badge = $booking->status === 'confirmed' ? 'bg-success' : 'bg-danger';
                $icon = $booking->status === 'confirmed' ? 'check_circle' : 'cancel';
                return '<span class="badge ' . $badge . '"><i class="material-icons align-middle">' . $icon . '</i> ' . ucfirst($booking->status) . '</span>';
            })
            ->editColumn('created_at', function (Booking $booking) {
                return $booking->created_at->format('Y-m-d H:i');
            })
            ->addColumn('action', function (Booking $booking) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-info view-booking" data-id="' . $booking->id . '" title="View Details">
                            <i class="material-icons">visibility</i>
                        </button>
                        <button class="btn btn-outline-danger cancel-booking" data-id="' . $booking->id . '" title="Cancel" ' . ($booking->status !== 'confirmed' ? 'disabled' : '') . '>
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                ';
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

    /**
     * Get user's bookings (AJAX).
     */
    public function getUserBookings(): JsonResponse
    {
        $bookings = Auth::user()->bookingHistory()
            ->get()
            ->map(function ($booking) {
                return [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'status' => $booking->status,
                    'train' => $booking->schedule->train->name,
                    'seat' => $booking->seat->seat_number,
                    'from' => $booking->schedule->from,
                    'to' => $booking->schedule->to,
                    'departure' => $booking->schedule->departure->format('Y-m-d H:i'),
                    'arrival' => $booking->schedule->arrival->format('Y-m-d H:i'),
                    'price' => $booking->price,
                    'booked_at' => $booking->booked_at->format('Y-m-d H:i'),
                ];
            });

        return response()->json([
            'success' => true,
            'bookings' => $bookings,
        ]);
    }

    /**
     * Cancel a booking.
     */
    public function cancelBooking(Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->user_id !== auth()->id() && !Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to cancel this booking.',
            ], 403);
        }

        try {
            $this->bookingService->cancelBooking($booking);

            return response()->json([
                'success' => true,
                'message' => 'Booking cancelled successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get booking details.
     */
    public function getBookingDetails(Booking $booking): JsonResponse
    {
        // Check authorization
        if ($booking->user_id !== auth()->id() && !Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this booking.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'reference' => $booking->booking_reference,
                'status' => $booking->status,
                'train' => [
                    'name' => $booking->schedule->train->name,
                    'number' => $booking->schedule->train->train_number,
                ],
                'seat' => [
                    'number' => $booking->seat->seat_number,
                    'class' => $booking->seat->class,
                    'facilities' => $booking->seat->facilities,
                ],
                'schedule' => [
                    'from' => $booking->schedule->from,
                    'to' => $booking->schedule->to,
                    'departure' => $booking->schedule->departure->format('Y-m-d H:i'),
                    'arrival' => $booking->schedule->arrival->format('Y-m-d H:i'),
                ],
                'price' => $booking->price,
                'booked_at' => $booking->booked_at->format('Y-m-d H:i'),
            ],
        ]);
    }

    /**
     * Get live tracking data for a booking (single train scenario).
     */
    public function trackBooking(Booking $booking): JsonResponse
    {
        // Check authorization: owner of booking or admin/staff
        if ($booking->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to track this booking.',
            ], 403);
        }

        $schedule = $booking->schedule;

        // Build route between schedule from/to using StationHelper
        $stations = StationHelper::getStations();
        $fromIndex = null;
        $toIndex = null;

        foreach ($stations as $index => $station) {
            if ($fromIndex === null && strcasecmp($station['name'], $schedule->from) === 0) {
                $fromIndex = $index;
            }

            if ($toIndex === null && strcasecmp($station['name'], $schedule->to) === 0) {
                $toIndex = $index;
            }
        }

        if ($fromIndex !== null && $toIndex !== null) {
            if ($fromIndex <= $toIndex) {
                $route = array_slice($stations, $fromIndex, $toIndex - $fromIndex + 1);
            } else {
                $route = array_reverse(array_slice($stations, $toIndex, $fromIndex - $toIndex + 1));
            }
        } else {
            // Fallback: full station list if exact match is not found
            $route = $stations;
        }

        // Since there is only one train, use the latest IoT data as the current train position
        $latestIot = IotData::latest()->first();

        // Derive speed from last two IoT points if possible
        $computedSpeed = null;
        if ($latestIot && $latestIot->latitude && $latestIot->longitude) {
            $previousIot = IotData::whereNotNull('latitude')
                ->whereNotNull('longitude')
                ->where('id', '<', $latestIot->id)
                ->orderByDesc('id')
                ->first();

            if ($previousIot) {
                $distanceKm = StationHelper::calculateDistance(
                    $previousIot->latitude,
                    $previousIot->longitude,
                    $latestIot->latitude,
                    $latestIot->longitude
                );

                $seconds = $latestIot->created_at->diffInSeconds($previousIot->created_at);
                if ($seconds > 0) {
                    $hours = $seconds / 3600;
                    $computedSpeed = $hours > 0 ? $distanceKm / $hours : null;
                }
            }
        }

        // Distance metrics relative to departure and destination
        $distanceFromDeparture = null;
        $distanceToDestination = null;
        $etaArrival = null;

        if ($latestIot && $latestIot->latitude && $latestIot->longitude) {
            // Resolve departure and destination coordinates from station list
            $fromStation = null;
            $toStation = null;
            foreach ($stations as $station) {
                if (!$fromStation && strcasecmp($station['name'], $schedule->from) === 0) {
                    $fromStation = $station;
                }
                if (!$toStation && strcasecmp($station['name'], $schedule->to) === 0) {
                    $toStation = $station;
                }
            }

            if ($fromStation) {
                $distanceFromDeparture = StationHelper::calculateDistance(
                    $fromStation['latitude'],
                    $fromStation['longitude'],
                    $latestIot->latitude,
                    $latestIot->longitude
                );
            }

            if ($toStation) {
                $distanceToDestination = StationHelper::calculateDistance(
                    $latestIot->latitude,
                    $latestIot->longitude,
                    $toStation['latitude'],
                    $toStation['longitude']
                );
            }

            // Basic ETA using distance to destination and current speed (km/h)
            $speedForEta = $computedSpeed ?? $latestIot->speed;
            if ($distanceToDestination !== null && $speedForEta && $speedForEta > 1) {
                $hoursRemaining = $distanceToDestination / $speedForEta;
                $minutesRemaining = (int) round($hoursRemaining * 60);
                $etaArrival = now()->addMinutes($minutesRemaining)->format('Y-m-d H:i');
            }
        }

        return response()->json([
            'success' => true,
            'booking' => [
                'id' => $booking->id,
                'reference' => $booking->booking_reference,
                'from' => $schedule->from,
                'to' => $schedule->to,
                'departure' => $schedule->departure->format('Y-m-d H:i'),
                'arrival' => $schedule->arrival->format('Y-m-d H:i'),
                'estimated_arrival' => $etaArrival,
            ],
            'route' => $route,
            'location' => $latestIot ? [
                'latitude' => $latestIot->latitude,
                'longitude' => $latestIot->longitude,
                'speed' => $computedSpeed ?? $latestIot->speed,
                'distance_from_departure_km' => $distanceFromDeparture,
                'distance_to_destination_km' => $distanceToDestination,
                'updated_at' => $latestIot->created_at->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }

    /**
     * Export bookings to CSV.
     */
    public function exportBookings()
    {
        // Check authorization - admin/staff only
        if (!Auth::user()->hasAnyRole(['admin', 'staff'])) {
            abort(403, 'Unauthorized to export bookings.');
        }

        $bookings = Booking::with(['user', 'schedule.train', 'seat'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = 'bookings_' . date('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($bookings) {
            $file = fopen('php://output', 'w');

            // Write header row
            fputcsv($file, [
                'Booking ID',
                'Reference',
                'Passenger Name',
                'Email',
                'Train',
                'Route',
                'Seat Number',
                'Amount (LKR)',
                'Status',
                'Booked On',
            ]);

            // Write data rows
            foreach ($bookings as $booking) {
                fputcsv($file, [
                    $booking->id,
                    $booking->booking_reference,
                    $booking->user->name,
                    $booking->user->email,
                    $booking->schedule->train->name,
                    $booking->schedule->from . ' → ' . $booking->schedule->to,
                    $booking->seat->seat_number,
                    $booking->amount,
                    $booking->status,
                    $booking->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Show booking details for admin.
     */
    public function show(Booking $booking): JsonResponse
    {
        // Check authorization - admin/staff only
        if (!Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view this booking.',
            ], 403);
        }

        return response()->json([
            'id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'status' => $booking->status,
            'amount' => $booking->price,
            'created_at' => $booking->created_at,
            'user' => [
                'id' => $booking->user->id,
                'name' => $booking->user->name,
                'email' => $booking->user->email,
                'phone' => $booking->user->phone ?? 'N/A',
            ],
            'seat' => [
                'id' => $booking->seat->id,
                'seat_number' => $booking->seat->seat_number,
                'class' => $booking->seat->class,
            ],
            'schedule' => [
                'id' => $booking->schedule->id,
                'from_station' => $booking->schedule->from,
                'to_station' => $booking->schedule->to,
                'departure_time' => $booking->schedule->departure,
                'arrival_time' => $booking->schedule->arrival,
                'train' => [
                    'id' => $booking->schedule->train->id,
                    'name' => $booking->schedule->train->name,
                    'train_number' => $booking->schedule->train->train_number,
                ],
            ],
        ]);
    }

    /**
     * Generate PDF ticket (simple implementation).
     */
    public function generateTicketPDF(Booking $booking)
    {
        // Check authorization
        if ($booking->user_id !== Auth::id() && !Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to generate this ticket.',
            ], 403);
        }

        // Generate PDF using a library like TCPDF or mPDF
        // Example: return PDF::loadView('booking.ticket-pdf', ['booking' => $booking])->download(...);

        return response()->json([
            'success' => true,
            'message' => 'Ticket PDF generation to be implemented with TCPDF/mPDF',
        ]);
    }

    /**
     * Display staff booking page.
     */
    public function staffCreateBooking()
    {
        // Check authorization - admin/staff only
        if (!Auth::user()->hasAnyRole(['admin', 'staff'])) {
            abort(403, 'Unauthorized to create bookings for users.');
        }

        return view('booking.staff-create-booking');
    }

    /**
     * Search users by email (for staff booking creation).
     */
    public function searchUsersByEmail(Request $request): JsonResponse
    {
        // Check authorization - admin/staff only
        if (!Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
            ], 403);
        }

        $validated = $request->validate([
            'email' => 'required|string|min:2|max:255',
        ]);

        $users = User::where('email', 'LIKE', '%' . $validated['email'] . '%')
            ->select('id', 'name', 'email')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'users' => $users,
        ]);
    }

    /**
     * Create booking for user (Staff function).
     */
    public function bookTicketForUser(Request $request): JsonResponse
    {
        // Check authorization - admin/staff only
        if (!Auth::user()->hasAnyRole(['admin', 'staff'])) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to create bookings for users.',
            ], 403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'schedule_id' => 'required|exists:schedules,id',
            'seat_id' => 'required|exists:seats,id',
            'price' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = User::findOrFail($validated['user_id']);
            $schedule = Schedule::findOrFail($validated['schedule_id']);
            $seat = Seat::findOrFail($validated['seat_id']);

            // Validate seat availability one final time
            if (!$this->bookingService->isSeatAvailable($schedule, $seat)) {
                return response()->json([
                    'success' => false,
                    'message' => 'This seat has just been booked. Please select another.',
                ], 422);
            }

            // Create booking for the selected user
            $booking = $this->bookingService->createBookingForUser(
                $user,
                $schedule,
                $seat,
                $validated['price']
            );

            return response()->json([
                'success' => true,
                'message' => 'Ticket booked successfully for ' . $user->name . '!',
                'booking' => [
                    'id' => $booking->id,
                    'reference' => $booking->booking_reference,
                    'user' => $user->name,
                    'seat' => $booking->seat->seat_number,
                    'price' => $booking->price,
                    'schedule' => [
                        'from' => $booking->schedule->from,
                        'to' => $booking->schedule->to,
                        'departure' => $booking->schedule->departure->format('Y-m-d H:i'),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Get all unique stations (for dropdown selection).
     */
    public function getStations(): JsonResponse
    {
        $fromStations = Schedule::distinct('from')->pluck('from')->sort()->values();
        $toStations = Schedule::distinct('to')->pluck('to')->sort()->values();

        // Get all unique stations
        $allStations = collect($fromStations)->merge($toStations)->unique()->sort()->values();

        return response()->json([
            'success' => true,
            'stations' => $allStations,
        ]);
    }
}
