<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Return latest notifications for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $notifications = $user->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(function (UserNotification $notification) {
                return [
                    'id' => $notification->id,
                    'read' => $notification->read_at !== null,
                    'type' => $notification->type,
                    'message' => $notification->message,
                    'created_at' => optional($notification->created_at)->toIso8601String(),
                    'data' => $notification->data,
                ];
            });

        return response()->json($notifications);
    }

    /**
     * Mark all notifications as read for the authenticated user.
     */
    public function markAllRead(Request $request): JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = $request->user();

        $user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['status' => 'ok']);
    }
}

