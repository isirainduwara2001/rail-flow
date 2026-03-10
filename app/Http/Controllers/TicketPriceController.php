<?php

namespace App\Http\Controllers;

use App\Models\Train;
use App\Models\SeatClass;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class TicketPriceController extends Controller
{
    /**
     * Display ticket prices management for a train.
     */
    public function index(Train $train)
    {
        return view('admin.trains.ticket-prices', compact('train'));
    }

    /**
     * Get ticket prices data for DataTables.
     */
    public function getData(Train $train): JsonResponse
    {
        $query = SeatClass::where('train_id', $train->id);

        return DataTables::of($query)
            ->addColumn('action', function (SeatClass $seatClass) {
                return '
                    <div class="btn-group btn-group-sm" role="group">
                        <button class="btn btn-outline-primary edit-price" data-id="' . $seatClass->id . '" title="Edit">
                            <i class="material-icons">edit</i>
                        </button>
                        <button class="btn btn-outline-danger delete-price" data-id="' . $seatClass->id . '" title="Delete">
                            <i class="material-icons">delete</i>
                        </button>
                    </div>
                ';
            })
            ->addColumn('formatted_price', function (SeatClass $seatClass) {
                return 'LKR ' . number_format($seatClass->price, 2);
            })
            ->editColumn('created_at', function (SeatClass $seatClass) {
                return $seatClass->created_at->format('Y-m-d H:i');
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Store a new ticket price (seat class).
     */
    public function store(Request $request, Train $train): JsonResponse
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:100',
            'seat_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if total seats don't exceed train capacity
        $existingCount = SeatClass::where('train_id', $train->id)
            ->where('class_name', '!=', $validated['class_name'])
            ->sum('seat_count');

        if ($existingCount + $validated['seat_count'] > $train->total_seats) {
            return response()->json([
                'success' => false,
                'message' => 'Total seats exceed train capacity (' . $train->total_seats . ' seats)',
            ], 422);
        }

        $seatClass = SeatClass::create([
            'train_id' => $train->id,
            'class_name' => $validated['class_name'],
            'seat_count' => $validated['seat_count'],
            'price' => $validated['price'],
            'description' => $validated['description'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Ticket price added successfully.',
            'data' => $seatClass,
        ]);
    }

    /**
     * Update ticket price.
     */
    public function update(Request $request, SeatClass $seatClass): JsonResponse
    {
        $validated = $request->validate([
            'class_name' => 'required|string|max:100',
            'seat_count' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:500',
        ]);

        // Check if total seats don't exceed train capacity
        $existingCount = SeatClass::where('train_id', $seatClass->train_id)
            ->where('id', '!=', $seatClass->id)
            ->sum('seat_count');

        if ($existingCount + $validated['seat_count'] > $seatClass->train->total_seats) {
            return response()->json([
                'success' => false,
                'message' => 'Total seats exceed train capacity (' . $seatClass->train->total_seats . ' seats)',
            ], 422);
        }

        $seatClass->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Ticket price updated successfully.',
            'data' => $seatClass,
        ]);
    }

    /**
     * Delete ticket price.
     */
    public function destroy(SeatClass $seatClass): JsonResponse
    {
        $seatClass->delete();

        return response()->json([
            'success' => true,
            'message' => 'Ticket price deleted successfully.',
        ]);
    }
}
