<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->limit(20)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at,
                'data' => $notification->data,
            ]);

        return response()->json([
            'data' => $notifications,
            'unread' => $request->user()->unreadNotifications()->count(),
        ]);
    }

    public function read(Request $request, string $notification): JsonResponse
    {
        $entry = $request->user()->notifications()->where('id', $notification)->firstOrFail();
        $entry->markAsRead();

        return response()->json(null, 204);
    }

    public function readAll(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(null, 204);
    }
}
