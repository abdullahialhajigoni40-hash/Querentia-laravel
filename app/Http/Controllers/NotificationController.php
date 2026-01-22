<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Get user notifications
    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 20);
        $unreadOnly = $request->input('unread_only', false);
        
        $query = Notification::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc');
        
        if ($unreadOnly) {
            $query->whereNull('read_at');
        }
        
        $notifications = $query->paginate($perPage);
        
        return response()->json([
            'success' => true,
            'notifications' => $notifications,
            'unread_count' => Notification::where('user_id', Auth::id())
                ->whereNull('read_at')
                ->count()
        ]);
    }

    // Mark notification as read
    public function markAsRead(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $notification->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.'
        ]);
    }

    // Mark all notifications as read
    public function markAllAsRead()
    {
        Notification::where('user_id', Auth::id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'All notifications marked as read.'
        ]);
    }

    // Delete notification
    public function destroy(Notification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.'
            ], 403);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted.'
        ]);
    }
}