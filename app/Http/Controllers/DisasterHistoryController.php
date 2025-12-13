<?php

namespace App\Http\Controllers;

use App\Models\DisasterHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisasterHistoryController extends Controller
{
    /**
     * Display a listing of disaster history (Web).
     */
    public function index(Request $request)
    {
        $query = DisasterHistory::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('city')) {
            $query->where('city', 'like', '%'.$request->city.'%');
        }

        if ($request->filled('risk_level')) {
            $query->where('risk_level', $request->risk_level);
        }

        $history = $query->latest()->paginate(25);

        return view('admin.disaster-history.index', compact('history'));
    }

    /**
     * Get disaster history data (API).
     */
    public function apiIndex(Request $request): JsonResponse
    {
        $query = DisasterHistory::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $data = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Store a new disaster history record (API).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'city' => 'required|string|max:255',
            'risk_level' => 'required|in:No,Low,Moderate,High',
        ]);

        $disasterHistory = DisasterHistory::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Disaster history recorded successfully.',
            'data' => $disasterHistory,
        ], 201);
    }
}
