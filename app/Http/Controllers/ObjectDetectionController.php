<?php

namespace App\Http\Controllers;

use App\Models\IotData;
use App\Models\ObjectDetection;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ObjectDetectionController extends Controller
{
    
    /**
     * Display a listing of detection history (Web)
     */

    public function index(Request $request)
    {
        $query = ObjectDetection::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        if ($request->filled('type')) {
            $query->where('type', 'like', '%'.$request->type.'%');
        }

        $history = $query->latest()->paginate(25);

        return view('admin.detections.index', compact('history'));
    }

    /**
     * Store a new object detection record (API)
     */

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'image' => 'required|string',
            'type' => 'required|string|max:255',
            'distance' => 'required|numeric',
            'size' => 'required|string|max:255',
        ]);

        // Get coordinates from latest IoT data
        $latestIot = IotData::latest()->first();
        
        $validated['latitude'] = $latestIot->latitude ?? null;
        $validated['longitude'] = $latestIot->longitude ?? null;

        $detection = ObjectDetection::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Object detection recorded successfully.',
            'data' => $detection,
        ], 201);
    }
}