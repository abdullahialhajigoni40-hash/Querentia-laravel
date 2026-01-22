<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserConnection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ConnectionController extends Controller
{
    // Send connection request
    public function sendRequest(Request $request, User $user)
    {
        // Can't connect to yourself
        if (Auth::id() === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot connect with yourself.'
            ], 400);
        }

        // Check if already connected
        if (Auth::user()->isConnectedTo($user->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You are already connected with this user.'
            ], 400);
        }

        // Check if request already exists
        $existingRequest = UserConnection::where('user_id', Auth::id())
            ->where('connected_user_id', $user->id)
            ->first();

        if ($existingRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Connection request already sent.'
            ], 400);
        }

        // Create connection request
        $connection = UserConnection::create([
            'user_id' => Auth::id(),
            'connected_user_id' => $user->id,
            'status' => 'pending',
            'message' => $request->input('message', ''),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Connection request sent successfully.',
            'connection' => $connection
        ]);
    }

    // Accept connection request
    public function acceptRequest(UserConnection $connection)
    {
        // Ensure user is the receiver of the request
        if ($connection->connected_user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $connection->accept();

        return response()->json([
            'success' => true,
            'message' => 'Connection request accepted.',
            'connection' => $connection
        ]);
    }

    // Reject connection request
    public function rejectRequest(UserConnection $connection)
    {
        if ($connection->connected_user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized action.'
            ], 403);
        }

        $connection->reject();

        return response()->json([
            'success' => true,
            'message' => 'Connection request rejected.'
        ]);
    }

    // Remove connection
    public function removeConnection(User $user)
    {
        $connection = UserConnection::where(function($query) use ($user) {
            $query->where('user_id', Auth::id())
                  ->where('connected_user_id', $user->id);
        })->orWhere(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('connected_user_id', Auth::id());
        })->first();

        if (!$connection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection not found.'
            ], 404);
        }

        $connection->delete();

        return response()->json([
            'success' => true,
            'message' => 'Connection removed successfully.'
        ]);
    }

    // Get user's connections
    public function getConnections(Request $request)
{
    $user = Auth::user();
    $type = $request->input('type', 'all'); // all, pending, sent

    $connections = [];

    if ($type === 'pending') {
        // Pending requests received
        $connections = $user->receivedConnections()
            ->where('status', 'pending')
            ->with('user')
            ->paginate(20);
    } elseif ($type === 'sent') {
        // Sent requests
        $connections = $user->sentConnections()
            ->where('status', 'pending')
            ->with('connectedUser')
            ->paginate(20);
    } else {
        // All accepted connections
        $connections = $user->connections()
            ->paginate(20);
    }

    return response()->json([
        'success' => true,
        'connections' => $connections
    ]);
}

    // Search connections
    public function searchConnections(Request $request)
    {
        $search = $request->input('search', '');

        $connections = Auth::user()->connections()
            ->whereHas('user', function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->orWhereHas('connectedUser', function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->with(['user', 'connectedUser'])
            ->paginate(20);

        return response()->json([
            'success' => true,
            'connections' => $connections
        ]);
    }
    
}