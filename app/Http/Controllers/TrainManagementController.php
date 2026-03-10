<?php

namespace App\Http\Controllers;

use App\Models\Train;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class TrainManagementController extends Controller
{
    /**
     * Display train management dashboard.
     */
    public function index()
    {
        return view('admin.trains.index');
    }

    /**
     * Get trains data for DataTables.
     */
    public function getTrainsData(Request $request): JsonResponse
    {
        $query = Train::select('trains.*')
            ->withCount('seats')
            ->with('schedules');

        return DataTables::of($query)
            ->addColumn('action', function (Train $train) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary edit-train" data-id="' . $train->id . '" title="Edit">
                            <i class="material-icons">edit</i>
                        </button>
                        <button class="btn btn-outline-danger delete-train" data-id="' . $train->id . '" title="Delete">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('available_seats', function (Train $train) {
                return $train->getAvailableSeatsCount() . ' / ' . $train->total_seats;
            })
            ->addColumn('total_schedules', function (Train $train) {
                return $train->schedules()->count();
            })
            ->editColumn('created_at', function (Train $train) {
                return $train->created_at->format('Y-m-d H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Show train creation form.
     */
    public function create()
    {
        return view('admin.trains.create');
    }

    /**
     * Store a new train.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'train_number' => 'required|string|unique:trains|max:50',
            'total_seats' => 'required|integer|min:10|max:1000',
            'description' => 'nullable|string',
        ]);

        $train = Train::create($validated);

        // Generate seats for the train
        $this->generateSeats($train);

        return response()->json([
            'success' => true,
            'message' => 'Train created successfully.',
            'train' => $train,
        ]);
    }

    /**
     * Show train edit form.
     */
    public function edit(Train $train)
    {
        return view('admin.trains.edit', compact('train'));
    }

    /**
     * Update train details.
     */
    public function update(Request $request, Train $train): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'train_number' => 'required|string|unique:trains,train_number,' . $train->id . '|max:50',
            'total_seats' => 'required|integer|min:10|max:1000',
            'description' => 'nullable|string',
        ]);

        $train->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Train updated successfully.',
            'train' => $train,
        ]);
    }

    /**
     * Delete a train.
     */
    public function destroy(Train $train): JsonResponse
    {
        // Check if train has active schedules/bookings
        if ($train->schedules()->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete a train with active schedules.',
            ], 422);
        }

        $train->delete();

        return response()->json([
            'success' => true,
            'message' => 'Train deleted successfully.',
        ]);
    }

    /**
     * Get seat configuration for a train.
     */
    public function getSeats(Train $train): JsonResponse
    {
        $seats = $train->seats()->get();

        return response()->json([
            'success' => true,
            'seats' => $seats,
        ]);
    }

    /**
     * Update seat configuration.
     */
    public function updateSeat(Request $request, Seat $seat): JsonResponse
    {
        $validated = $request->validate([
            'class' => 'required|in:economy,business,first',
            'facilities' => 'nullable|array',
        ]);

        $seat->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Seat updated successfully.',
            'seat' => $seat,
        ]);
    }

    /**
     * Generate seats for a train.
     * Example: 100 seats = 10 rows x 10 columns (A1-J10)
     */
    private function generateSeats(Train $train): void
    {
        $totalSeats = $train->total_seats;
        $rows = ceil(sqrt($totalSeats));
        $cols = ceil($totalSeats / $rows);

        $seatNumber = 1;
        for ($row = 0; $row < $rows; $row++) {
            for ($col = 0; $col < $cols; $col++) {
                if ($seatNumber > $totalSeats) {
                    break;
                }

                $rowLabel = chr(65 + $row); // A, B, C, ...
                $colLabel = $col + 1;

                // Assign class based on position (example logic)
                $class = 'economy';
                if ($col === 0 || $col === $cols - 1) {
                    $class = 'business';
                }
                if ($row === 0) {
                    $class = 'first';
                }

                Seat::create([
                    'train_id' => $train->id,
                    'seat_number' => $rowLabel . $colLabel,
                    'class' => $class,
                    'facilities' => json_encode([
                        'ac' => $class !== 'economy',
                        'plug' => true,
                        'meal' => $class === 'first',
                        'window' => $col === 0 || $col === $cols - 1,
                    ]),
                    'status' => 'available',
                ]);

                $seatNumber++;
            }
        }
    }

    /**
     * Get all trains as JSON (for select dropdowns and AJAX).
     */
    public function getTrainsList(): JsonResponse
    {
        $trains = Train::select('id', 'name', 'train_number')->get();

        return response()->json([
            'success' => true,
            'data' => $trains
        ]);
    }
}
