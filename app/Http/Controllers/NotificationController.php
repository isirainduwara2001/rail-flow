<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch all recent notifications.
     */
    public function index(): JsonResponse
    {
        $notifications = Notification::orderBy('created_at', 'desc')->whereNull('read_at')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    /**
     * Store a new global notification.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => 'required|string',
            'type' => 'nullable|string|in:info,warning,success,danger',
        ]);

        $notification = Notification::create([
            'message' => $validated['message'],
            'type' => $validated['type'] ?? 'info',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification broadcasted successfully!',
            'data' => $notification,
        ]);
    }

    /**
     * Mark notification as read (optional/global).
     */
    public function markAsRead($id): JsonResponse
    {
        $notification = Notification::findOrFail($id);
        $notification->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
