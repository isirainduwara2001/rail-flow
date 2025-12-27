<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Schedule;
use App\Models\Train;
use App\Models\User;

class DashboardController extends Controller
{

    /**
     * Display the application dashboard.
     */
    public function index()
    {
        $user = auth()->user();
        $isAdminOrStaff = $user->hasAnyRole(['admin', 'staff']);

        
        // 7-day booking trend
        $days = [];
        $bookingCounts = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $days[] = now()->subDays($i)->format('D');

            $query = Booking::whereDate('created_at', $date);
            if (! $isAdminOrStaff) {
                $query->where('user_id', $user->id);
            }
            $bookingCounts[] = $query->count();
        }

        // Stats Query
        $bookingQuery = Booking::query();
        if (! $isAdminOrStaff) {
            $bookingQuery->where('user_id', $user->id);
        }

        $dashboardData = [
            'is_admin' => $isAdminOrStaff,
            'total_users' => $isAdminOrStaff ? User::count() : 0,
            'total_trains' => $isAdminOrStaff ? Train::count() : Train::count(), // Everyone can see total trains
            'active_schedules' => Schedule::where('departure', '>', now())->count(),
            'total_bookings' => (clone $bookingQuery)->count(),
            'confirmed_bookings' => (clone $bookingQuery)->where('status', 'confirmed')->count(),
            'total_revenue' => (clone $bookingQuery)->where('status', 'confirmed')->sum('price'),
            'latest_iot' => $isAdminOrStaff ? \App\Models\IotData::latest()->first() : null,

            // Recent History
            'recent_bookings' => (clone $bookingQuery)->with(['user', 'schedule.train'])->latest()->limit(5)->get(),
            'recent_detections' => $isAdminOrStaff ? \App\Models\ObjectDetection::latest()->limit(5)->get() : collect(),
            'recent_disasters' => $isAdminOrStaff ? \App\Models\DisasterHistory::latest()->limit(5)->get() : collect(),

            // Chart Data
            'chart_labels' => $days,
            'chart_data' => $bookingCounts,
        ];

        return view('dashboard', $dashboardData);
    }
}
