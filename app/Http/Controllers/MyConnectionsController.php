<?php

namespace App\Http\Controllers;

use App\Models\UserConnection;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MyConnectionsController extends Controller
{
    /**
     * Display the user's connections dashboard
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get connection statistics
        $stats = $this->getConnectionStats($user);
        
        // Get pending connection requests
        $pendingRequests = $this->getPendingRequests($user);
        
        // Get accepted connections with search and filter
        $connections = $this->getConnections($user);
        
        // Get suggested connections
        $suggestedConnections = $this->getSuggestedConnections($user);
        
        return view('network.my-connections', compact(
            'stats',
            'pendingRequests',
            'connections',
            'suggestedConnections'
        ));
    }
    
    /**
     * Send connection request
     */
    public function sendRequest(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id|different:' . Auth::id(),
            'message' => 'nullable|string|max:500'
        ]);
        
        $sender = Auth::user();
        $receiver = User::findOrFail($request->user_id);
        
        // Check if connection already exists
        $existingConnection = UserConnection::where(function($query) use ($sender, $receiver) {
            $query->where('user_id', $sender->id)
                  ->where('connected_user_id', $receiver->id);
        })->orWhere(function($query) use ($sender, $receiver) {
            $query->where('user_id', $receiver->id)
                  ->where('connected_user_id', $sender->id);
        })->first();
        
        if ($existingConnection) {
            return response()->json([
                'success' => false,
                'message' => 'Connection request already exists'
            ], 400);
        }
        
        // Create connection request
        $connection = UserConnection::create([
            'user_id' => $sender->id,
            'connected_user_id' => $receiver->id,
            'status' => 'pending',
            'message' => $request->message,
        ]);
        
        // Create notification for receiver
        $receiver->notifications()->create([
            'type' => 'connection_request',
            'title' => 'New Connection Request',
            'message' => "{$sender->full_name} wants to connect with you",
            'data' => [
                'connection_id' => $connection->id,
                'sender_id' => $sender->id,
            ],
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Connection request sent successfully',
            'connection' => $connection
        ]);
    }
    
    /**
     * Accept connection request
     */
    public function acceptRequest(UserConnection $connection)
    {
        $user = Auth::user();
        
        // Check if user is the receiver of the request
        if ($connection->connected_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        if ($connection->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Connection request is no longer pending'
            ], 400);
        }
        
        try {
            DB::beginTransaction();
            
            $connection->accept();
            
            // Create notification for sender
            $connection->user->notifications()->create([
                'type' => 'connection_accepted',
                'title' => 'Connection Accepted',
                'message' => "{$user->full_name} accepted your connection request",
                'data' => [
                    'connection_id' => $connection->id,
                    'receiver_id' => $user->id,
                ],
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Connection accepted successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to accept connection: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reject connection request
     */
    public function rejectRequest(UserConnection $connection)
    {
        $user = Auth::user();
        
        // Check if user is the receiver of the request
        if ($connection->connected_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        if ($connection->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Connection request is no longer pending'
            ], 400);
        }
        
        $connection->reject();
        
        return response()->json([
            'success' => true,
            'message' => 'Connection request rejected'
        ]);
    }
    
    /**
     * Remove connection
     */
    public function removeConnection(UserConnection $connection)
    {
        $user = Auth::user();
        
        // Check if user is part of this connection
        if ($connection->user_id !== $user->id && $connection->connected_user_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }
        
        $connection->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Connection removed successfully'
        ]);
    }
    
    /**
     * Search for researchers to connect with
     */
    public function findResearchers(Request $request)
    {
        $user = Auth::user();
        
        $query = User::where('id', '!=', $user->id)
            ->whereDoesntHave('sentConnections', function($query) use ($user) {
                $query->where('connected_user_id', $user->id);
            })
            ->whereDoesntHave('receivedConnections', function($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        
        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%")
                  ->orWhere('area_of_study', 'like', "%{$search}%");
            });
        }
        
        // Filter by area of study
        if ($request->filled('area_of_study')) {
            $query->where('area_of_study', $request->input('area_of_study'));
        }
        
        // Filter by institution
        if ($request->filled('institution')) {
            $query->where('institution', 'like', "%{$request->input('institution')}%");
        }
        
        $researchers = $query->withCount(['connections' => function($q) {
                $q->where('status', 'accepted');
            }])
            ->orderBy('connections_count', 'desc')
            ->paginate(12);
        
        return response()->json([
            'success' => true,
            'researchers' => $researchers
        ]);
    }
    
    /**
     * Get connection statistics
     */
    private function getConnectionStats(User $user)
    {
        $connections = $user->connections();
        
        return [
            'total_connections' => $connections->count(),
            'pending_requests' => $user->pendingConnections()->count(),
            'countries' => $connections->with('connectedUser')
                ->get()
                ->pluck('connectedUser.institution')
                ->unique()
                ->count(),
            'research_areas' => $connections->with('connectedUser')
                ->get()
                ->pluck('connectedUser.area_of_study')
                ->filter()
                ->unique()
                ->count(),
        ];
    }
    
    /**
     * Get pending connection requests
     */
    private function getPendingRequests(User $user)
    {
        return $user->pendingConnections()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
    }
    
    /**
     * Get accepted connections with search and filter
     */
    private function getConnections(User $user, Request $request = null)
    {
        $query = $user->connections()->where('status', 'accepted');
        
        // Apply search filter
        if ($request && $request->filled('search')) {
            $search = $request->input('search');
            $query->whereHas('connectedUser', function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('institution', 'like', "%{$search}%")
                  ->orWhere('area_of_study', 'like', "%{$search}%");
            });
        }
        
        // Apply area of study filter
        if ($request && $request->filled('area_of_study')) {
            $query->whereHas('connectedUser', function($q) use ($request) {
                $q->where('area_of_study', $request->input('area_of_study'));
            });
        }
        
        return $query->with('connectedUser')
            ->orderBy('connected_at', 'desc')
            ->paginate(12);
    }
    
    /**
     * Get suggested connections based on user's profile
     */
    private function getSuggestedConnections(User $user, $limit = 6)
    {
        // Get users not connected with current user
        $connectedUserIds = $user->connections()
            ->where('status', 'accepted')
            ->pluck('connected_user_id')
            ->push($user->id)
            ->toArray();
        
        $suggested = User::whereNotIn('id', $connectedUserIds)
            ->where(function($query) use ($user) {
                // Match by area of study
                if ($user->area_of_study) {
                    $query->where('area_of_study', $user->area_of_study)
                          ->orWhereIn('area_of_study', $this->getRelatedFields($user->area_of_study));
                }
            })
            ->orWhere(function($query) use ($user) {
                // Match by institution
                if ($user->institution) {
                    $query->where('institution', $user->institution);
                }
            })
            ->withCount(['connections' => function($q) {
                $q->where('status', 'accepted');
            }])
            ->orderBy('connections_count', 'desc')
            ->limit($limit)
            ->get();
        
        // Add mutual connections count
        foreach ($suggested as $suggestion) {
            $suggestion->mutual_connections = $this->getMutualConnectionsCount($user, $suggestion);
        }
        
        return $suggested->sortByDesc('mutual_connections')->values();
    }
    
    /**
     * Get count of mutual connections between two users
     */
    private function getMutualConnectionsCount(User $user1, User $user2)
    {
        $user1Connections = $user1->connections()
            ->where('status', 'accepted')
            ->pluck('connected_user_id')
            ->toArray();
            
        $user2Connections = $user2->connections()
            ->where('status', 'accepted')
            ->pluck('connected_user_id')
            ->toArray();
            
        return count(array_intersect($user1Connections, $user2Connections));
    }
    
    /**
     * Get related fields based on user's area of study
     */
    private function getRelatedFields($areaOfStudy)
    {
        $relatedFields = [
            'Computer Science' => ['Artificial Intelligence', 'Machine Learning', 'Data Science', 'Software Engineering'],
            'Medicine' => ['Healthcare', 'Medical Science', 'Pharmacology', 'Biology'],
            'Physics' => ['Mathematics', 'Engineering', 'Astronomy', 'Chemistry'],
            'Biology' => ['Medicine', 'Genetics', 'Ecology', 'Biochemistry'],
            'Chemistry' => ['Physics', 'Biology', 'Materials Science', 'Pharmacology'],
        ];
        
        return $relatedFields[$areaOfStudy] ?? [];
    }
}
