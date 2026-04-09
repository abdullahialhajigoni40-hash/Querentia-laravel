<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\GroupMember;
use App\Models\GroupMessage;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get user's groups
        $myGroups = $user->joinedGroups()->with(['creator', 'activeMembers'])->get();
        
        // Get discoverable public groups (not joined by user)
        $discoverableGroups = Group::active()
            ->public()
            ->whereNotIn('id', $myGroups->pluck('id'))
            ->with('creator')
            ->orderBy('members_count', 'desc')
            ->limit(12)
            ->get();
        
        // Get unread message count for each group
        foreach ($myGroups as $group) {
            $group->unread_count = $group->getUnreadMessageCount($user->id);
        }
        
        return view('groups.index', compact('myGroups', 'discoverableGroups'));
    }
    
    public function create()
    {
        $userConnections = Auth::user()->connections()->where('status', 'accepted')->get();
        
        return view('groups.create', compact('userConnections'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:public,private',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'members' => 'nullable|array',
            'members.*' => 'exists:users,id',
        ]);
        
        DB::beginTransaction();
        
        try {
            $group = Group::create([
                'creator_id' => Auth::id(),
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'description' => $request->description,
                'type' => $request->type,
            ]);
            
            // Handle avatar upload
            if ($request->hasFile('avatar')) {
                $avatarPath = $request->file('avatar')->store('groups/avatars', 'public');
                $group->update(['avatar' => $avatarPath]);
            }
            
            // Add initial members
            if ($request->has('members')) {
                foreach ($request->members as $memberId) {
                    // Only add if user is connected to current user
                    if (Auth::user()->connections()->where('connected_user_id', $memberId)->where('status', 'accepted')->exists()) {
                        $group->addMember($memberId, 'member');
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('groups.show', $group->slug)
                ->with('success', 'Group created successfully!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            return back()->withInput()
                ->with('error', 'Failed to create group. Please try again.');
        }
    }
    
    public function show($slug)
    {
        $group = Group::where('slug', $slug)
            ->with(['creator', 'activeMembers.user', 'recentMessages.user'])
            ->firstOrFail();
        
        $user = Auth::user();
        
        // Check if user is member or if group is public
        if (!$group->isMember($user->id) && $group->type === 'private') {
            abort(403, 'This is a private group.');
        }
        
        // Get messages
        $messages = $group->recentMessages()->get();
        
        // Get user's role in group
        $userRole = $group->getMemberRole($user->id);
        
        // Mark messages as read
        if ($group->isMember($user->id)) {
            $group->markMessagesAsRead($user->id);
        }
        
        return view('groups.show', compact('group', 'messages', 'userRole'));
    }
    
    public function edit($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'Only group admins can edit the group.');
        }
        
        return view('groups.edit', compact('group'));
    }
    
    public function update(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'Only group admins can edit the group.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        
        $group->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
        ]);
        
        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $avatarPath = $request->file('avatar')->store('groups/avatars', 'public');
            $group->update(['avatar' => $avatarPath]);
        }
        
        return redirect()->route('groups.show', $group->slug)
            ->with('success', 'Group updated successfully!');
    }
    
    public function destroy($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'Only group admins can delete the group.');
        }
        
        $group->delete();
        
        return redirect()->route('groups.index')
            ->with('success', 'Group deleted successfully!');
    }
    
    // Member Management
    public function join($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->canUserJoin()) {
            return back()->with('error', 'You cannot join this group.');
        }
        
        $group->addMember(Auth::id(), 'member');
        
        return back()->with('success', 'You have joined the group!');
    }
    
    public function leave($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isMember(Auth::id())) {
            return back()->with('error', 'You are not a member of this group.');
        }
        
        // Creator cannot leave their own group
        if ($group->creator_id === Auth::id()) {
            return back()->with('error', 'Group creator cannot leave the group.');
        }
        
        $group->removeMember(Auth::id());
        
        return redirect()->route('groups.index')
            ->with('success', 'You have left the group.');
    }
    
    public function addMember(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'Only group admins can add members.');
        }
        
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|in:member,moderator',
        ]);
        
        // Check if user is connected to group creator
        if (!Auth::user()->connections()->where('connected_user_id', $request->user_id)->where('status', 'accepted')->exists()) {
            return back()->with('error', 'You can only add users who are in your connections.');
        }
        
        $member = $group->addMember($request->user_id, $request->role);
        
        if ($member) {
            return back()->with('success', 'Member added successfully!');
        } else {
            return back()->with('error', 'User is already a member of this group.');
        }
    }
    
    public function removeMember($slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'Only group admins can remove members.');
        }
        
        // Cannot remove the creator
        if ($group->creator_id == $userId) {
            return back()->with('error', 'Cannot remove the group creator.');
        }
        
        $group->removeMember($userId);
        
        return back()->with('success', 'Member removed successfully!');
    }
    
    public function updateMemberRole(Request $request, $slug, $userId)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isAdmin(Auth::id())) {
            abort(403, 'Only group admins can update member roles.');
        }
        
        $request->validate([
            'role' => 'required|in:member,moderator,admin',
        ]);
        
        // Cannot change creator's role
        if ($group->creator_id == $userId) {
            return back()->with('error', 'Cannot change the group creator\'s role.');
        }
        
        $group->updateMemberRole($userId, $request->role);
        
        return back()->with('success', 'Member role updated successfully!');
    }
    
    // Chat functionality
    public function sendMessage(Request $request, $slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isMember(Auth::id())) {
            return response()->json(['error' => 'You are not a member of this group.'], 403);
        }
        
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);
        
        $message = $group->messages()->create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'type' => 'text',
        ]);
        
        $group->updateMessageCount();
        
        // Load message with user relationship
        $message->load('user');
        
        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $message->formatted_content,
                'time' => $message->time,
                'user' => [
                    'name' => $message->user->full_name,
                    'avatar' => $message->user->profile_picture,
                ],
                'is_own' => $message->user_id === Auth::id(),
            ],
        ]);
    }
    
    public function getMessages($slug)
    {
        $group = Group::where('slug', $slug)->firstOrFail();
        
        if (!$group->isMember(Auth::id())) {
            return response()->json(['error' => 'You are not a member of this group.'], 403);
        }
        
        $messages = $group->recentMessages()
            ->with('user')
            ->get()
            ->map(function ($message) {
                return [
                    'id' => $message->id,
                    'content' => $message->formatted_content,
                    'time' => $message->time,
                    'date' => $message->date,
                    'user' => [
                        'name' => $message->user->full_name,
                        'avatar' => $message->user->profile_picture,
                    ],
                    'is_own' => $message->user_id === Auth::id(),
                    'is_edited' => $message->isEdited(),
                ];
            });
        
        return response()->json([
            'messages' => $messages,
        ]);
    }
}
