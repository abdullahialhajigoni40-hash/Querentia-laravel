<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Journal;
use App\Models\User;
use App\Models\UserConnection;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Check if user exists
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Load user journals with fallback
        $recentJournals = $user->journals()->latest()->take(5)->get() ?? collect([]);
        
        // FIX: Use correct method name - pendingConnectionRequests()
        $pendingConnections = $user->pendingConnectionRequests()->with('user')->take(5)->get() ?? collect([]);
        
        $data = [
            'user' => $user,
            'recentJournals' => $recentJournals,
            'pendingConnections' => $pendingConnections,
            'totalConnections' => $user->connection_count ?? 0,
            'totalPublications' => $user->journals()->count() ?? 0,
            'recentActivity' => $this->getRecentActivity($user),
            'suggestedConnections' => $this->getSuggestedConnections($user),
        ];
        
        return view('dashboard', $data);
    }
    
    private function getRecentActivity($user)
    {
        // This would be replaced with actual activity feed
        return [
            [
                'type' => 'journal_published',
                'user' => $user,
                'message' => 'published a new journal',
                'journal_title' => 'Impact of AI on Academic Research',
                'time' => '2 hours ago',
            ],
            [
                'type' => 'connection_added',
                'user' => $user,
                'message' => 'connected with Dr. Sarah Johnson',
                'time' => '1 day ago',
            ],
        ];
    }
    
    private function getSuggestedConnections($user)
    {
        try {
            // Get users from same institution or with similar research interests
            // Exclude users already connected or with pending requests
            $connectedUserIds = $user->acceptedConnections()
                ->get()
                ->map(function($connection) use ($user) {
                    return $connection->user_id == $user->id ? $connection->connected_user_id : $connection->user_id;
                })
                ->toArray();

            $pendingUserIds = $user->pendingConnectionRequests()
                ->pluck('user_id')
                ->merge($user->sentPendingConnections()->pluck('connected_user_id'))
                ->toArray();

            $excludedUserIds = array_merge([$user->id], $connectedUserIds, $pendingUserIds);

            $suggested = User::whereNotIn('id', $excludedUserIds)
                ->where(function($query) use ($user) {
                    $query->where('institution', $user->institution);
                    
                    // Check if research interests overlap
                    if ($user->research_interests && is_array($user->research_interests)) {
                        foreach ($user->research_interests as $interest) {
                            $query->orWhere('research_interests', 'like', '%' . $interest . '%');
                        }
                    }
                })
                ->limit(5)
                ->get();

            return $suggested;
        } catch (\Exception $e) {
            // Return empty collection if error
            return collect([]);
        }
    }
    
    public function myWritings()
    {
        $journals = Auth::user()->journals()->latest()->paginate(10);
        return view('journals.index', compact('journals'));
    }
    
    public function myReviews()
    {
        // Will implement later
        return view('reviews.index');
    }
    
    public function myConnections()
    {
        $user = Auth::user();
        
        // Get accepted connections
        $connections = UserConnection::where(function($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhere('connected_user_id', $user->id);
        })
        ->where('status', 'accepted')
        ->with(['user', 'connectedUser'])
        ->paginate(20);
            
        return view('connections.index', compact('connections', 'user'));
    }
}