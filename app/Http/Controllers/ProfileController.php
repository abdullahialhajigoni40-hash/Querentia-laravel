<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    // View profile
    public function show(User $user = null)
    {
        $profileUser = $user ?? Auth::user();
        
        // Load profile with connections count
        $profileUser->load(['profile', 'journals']);
        
        return view('profile.show', [
            'user' => $profileUser,
            'isOwnProfile' => !$user || $user->id === Auth::id(),
            'connectionStatus' => $this->getConnectionStatus($profileUser)
        ]);
    }

    // Edit profile page
    public function edit()
    {
        $user = Auth::user();
        $user->load('profile');
        
        return view('profile.edit', [
            'user' => $user,
            'profile' => $user->profile
        ]);
    }

    // Update profile
    public function update(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:2000',
            'institution' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'position' => 'required|in:student,researcher,lecturer,professor,phd,other',
            'research_interests' => 'nullable|string',
            'website' => 'nullable|url',
            'linkedin' => 'nullable|string',
            'twitter' => 'nullable|string',
            'google_scholar' => 'nullable|string',
            'researchgate' => 'nullable|string',
            'profile_picture' => 'nullable|image|max:2048',
            'education' => 'nullable|array',
            'experience' => 'nullable|array',
            'skills' => 'nullable|array',
        ]);

        // Update user basic info
        $user->update([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'institution' => $validated['institution'],
            'department' => $validated['department'],
            'position' => $validated['position'],
            'research_interests' => $validated['research_interests'] ?? null,
        ]);

        // Handle profile picture upload
        if ($request->hasFile('profile_picture')) {
            $path = $request->file('profile_picture')->store('profile-pictures', 'public');
            $user->profile_picture = $path;
            $user->save();
        }

        // Update or create profile
        $profileData = [
            'title' => $validated['title'] ?? null,
            'bio' => $validated['bio'] ?? null,
            'website' => $validated['website'] ?? null,
            'linkedin' => $validated['linkedin'] ?? null,
            'twitter' => $validated['twitter'] ?? null,
            'google_scholar' => $validated['google_scholar'] ?? null,
            'researchgate' => $validated['researchgate'] ?? null,
            'education' => $validated['education'] ?? [],
            'experience' => $validated['experience'] ?? [],
            'skills' => $validated['skills'] ?? [],
        ];

        if ($user->profile) {
            $user->profile->update($profileData);
        } else {
            $user->profile()->create($profileData);
        }

        return redirect()->route('profile.show')
            ->with('success', 'Profile updated successfully.');
    }

    // Get connection status between auth user and another user
    private function getConnectionStatus(User $targetUser)
    {
        $authUser = Auth::user();
        
        if ($authUser->id === $targetUser->id) {
            return 'self';
        }

        if ($authUser->isConnectedTo($targetUser->id)) {
            return 'connected';
        }

        if ($authUser->hasPendingConnectionWith($targetUser->id)) {
            return 'pending_sent';
        }

        if ($targetUser->hasPendingConnectionWith($authUser->id)) {
            return 'pending_received';
        }

        return 'not_connected';
    }

    // Get profile data for API
    public function getProfileData(User $user = null)
    {
        $profileUser = $user ?? Auth::user();
        $profileUser->load('profile');
        
        return response()->json([
            'success' => true,
            'user' => $profileUser,
            'profile' => $profileUser->profile,
            'connection_count' => $profileUser->connection_count,
            'journal_count' => $profileUser->journals()->count(),
            'is_connected' => Auth::user()->isConnectedTo($profileUser->id)
        ]);
    }
}