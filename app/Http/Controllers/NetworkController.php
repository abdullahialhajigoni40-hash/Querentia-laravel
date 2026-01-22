<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Journal;

class NetworkController extends Controller
{
    public function home()
    {
        $user = Auth::user();
        
        $data = [
            'suggestedConnections' => $this->getSuggestedConnections($user),
            'trendingJournals' => $this->getTrendingJournals(),
            'recentActivity' => $this->getRecentActivity(),
        ];
        
        return view('network.home', $data);
    }
    
    private function getSuggestedConnections($user)
    {
        return User::where('id', '!=', $user->id)
            ->where(function($query) use ($user) {
                $query->where('institution', $user->institution)
                      ->orWhere('department', $user->department)
                      ->orWhereJsonContains('research_interests', $user->research_interests);
            })
            ->whereNotIn('id', function($query) use ($user) {
                $query->select('connected_user_id')
                      ->from('user_connections')
                      ->where('user_id', $user->id);
            })
            ->limit(5)
            ->get();
    }
    
    private function getTrendingJournals()
    {
        // Temporarily return empty array until we have reviews
        return Journal::where('status', 'published')
            ->with('user')
            ->latest()
            ->limit(5)
            ->get();
    }
    
    private function getRecentActivity()
    {
        // This would be replaced with actual activity feed logic
        return collect([]);
    }
    
    public function myNetwork()
    {
        $user = Auth::user();
        $connections = $user->connections()
            ->with(['user', 'connectedUser'])
            ->paginate(20);
            
        return view('network.my-network', compact('connections'));
    }
    
    public function journals()
    {
        $journals = Journal::where('status', 'published')
            ->with('user')
            ->latest()
            ->paginate(20);
            
        return view('network.journals', compact('journals'));
    }
    
    public function reviews()
    {
        return view('network.reviews');
    }
    
    public function groups()
    {
        return view('network.groups');
    }
    
    public function events()
    {
        return view('network.events');
    }
    
    public function jobs()
    {
        return view('network.jobs');
    }
}