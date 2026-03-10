<?php

namespace App\Http\Controllers;

use App\Helpers\StationHelper;
use App\Models\DisasterHistory;
use App\Models\IotData;
use App\Models\ObjectDetection;
use App\Models\Setting;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IotDataController extends Controller
{
    /**
     * Store new IoT history data
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'sf_front_distance' => 'nullable|numeric',
            'sf_side_distance' => 'nullable|numeric',
            't_front_distance' => 'nullable|numeric',
            'temperature' => 'nullable|numeric',
            'humidity' => 'nullable|numeric',
            'lux' => 'nullable|numeric',
            'rain_percentage' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
        ]);

        $iotData = IotData::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'IoT data recorded successfully.',
            'data' => $iotData,
        ], 201);
    }

    /**
     *  Display IoT history (admin view)
     */
    public function index(Request $request)
    {
        $query = IotData::query();

        // Apply filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $history = $query->latest()->paginate(25);

        return view('admin.history.index', compact('history'));
    }

    /**
     * Display Modern IoT Dashboard
     */
    public function dashboard()
    {
        $latest = IotData::latest()->first();
        $recent = IotData::latest()->limit(20)->get();

        $latestDetection = ObjectDetection::latest()->first();
        $latestDisaster = DisasterHistory::latest()->first();

        $streamUrl = Setting::where('key', 'iot_stream_url')->first()->value ?? env('IOT_STREAM_URL');
        $weather = $this->getWeatherData($latest->latitude ?? null, $latest->longitude ?? null);

        return view('admin.iot.dashboard', compact('latest', 'recent', 'streamUrl', 'weather', 'latestDetection', 'latestDisaster'));
    }

    /**
     * Get latest IoT data (API) including weather
     */
    public function latest(): JsonResponse
    {
        $latest = IotData::latest()->first();
        $weather = $this->getWeatherData($latest->latitude ?? null, $latest->longitude ?? null);

        $latestDetection = ObjectDetection::latest()->first();
        $latestDisaster = DisasterHistory::latest()->first();

        return response()->json([
            'latest' => $latest,
            'weather' => $weather,
            'latestDetection' => $latestDetection,
            'latestDisaster' => $latestDisaster,
        ]);
    }

    /**
     * Update system settings
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'key' => 'required|string',
            'value' => 'required|string',
        ]);

        Setting::updateOrCreate(['key' => $validated['key']], ['value' => $validated['value']]);

        return response()->json([
            'success' => true,
            'message' => 'Setting updated successfully.',
        ]);
    }

    /**
     * Get nearest station based on latest IoT data
     */
    public function getNearestStation()
    {
        $latest = IotData::latest()->first();

        $defaultStation = ['name' => 'Colombo Fort', 'latitude' => 6.9344, 'longitude' => 79.8501];

        if (! $latest || ! $latest->latitude || ! $latest->longitude) {
            return response()->json($defaultStation);
        }

        $nearest = StationHelper::getNearestStation($latest->latitude, $latest->longitude, 10);

        return response()->json($nearest ?: $defaultStation);
    }

    /**
     * Fetch weather data from OpenWeatherMap
     */
    private function getWeatherData($lat, $lng)
    {
        if (! $lat || ! $lng) {
            return null;
        }

        $apiKey = env('OPENWEATHER_API_KEY');
        if (! $apiKey) {
            return null;
        }

        try {
            $response = Http::get('https://api.openweathermap.org/data/2.5/weather', [
                'lat' => $lat,
                'lon' => $lng,
                'appid' => $apiKey,
                'units' => 'metric',
            ]);
            if ($response->successful()) {
                return $response->json();
            }
        } catch (Exception $e) {
            Log::error('Weather API Error: '.$e->getMessage());
        }

        return null;
    }
}
