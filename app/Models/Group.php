<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Group extends Model
{
    use HasFactory;

    protected $fillable = [
        'creator_id',
        'name',
        'slug',
        'description',
        'avatar',
        'type',
        'status',
        'members_count',
        'messages_count',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    public function activeMembers()
    {
        return $this->hasMany(GroupMember::class)->where('status', 'active');
    }

    public function messages()
    {
        return $this->hasMany(GroupMessage::class)->orderBy('created_at', 'asc');
    }

    public function recentMessages()
    {
        return $this->hasMany(GroupMessage::class)
            ->where('status', '!=', 'deleted')
            ->orderBy('created_at', 'desc')
            ->limit(50);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePublic($query)
    {
        return $query->where('type', 'public');
    }

    public function scopePrivate($query)
    {
        return $query->where('type', 'private');
    }

    // Methods
    public function isMember($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->members()->where('user_id', $userId)->exists();
    }

    public function getMemberRole($userId = null)
    {
        $userId = $userId ?? auth()->id();
        $member = $this->members()->where('user_id', $userId)->first();
        return $member ? $member->role : null;
    }

    public function isAdmin($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return $this->getMemberRole($userId) === 'admin';
    }

    public function isModerator($userId = null)
    {
        $userId = $userId ?? auth()->id();
        return in_array($this->getMemberRole($userId), ['admin', 'moderator']);
    }

    public function canUserJoin($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // Can't join if already a member
        if ($this->isMember($userId)) {
            return false;
        }
        
        // Public groups can be joined by anyone
        if ($this->type === 'public') {
            return true;
        }
        
        // Private groups need invitation (for now, only creator can add)
        return false;
    }

    public function addMember($userId, $role = 'member')
    {
        if ($this->isMember($userId)) {
            return false;
        }
        
        $member = $this->members()->create([
            'user_id' => $userId,
            'role' => $role,
        ]);
        
        $this->increment('members_count');
        
        return $member;
    }

    public function removeMember($userId)
    {
        $member = $this->members()->where('user_id', $userId)->first();
        
        if (!$member) {
            return false;
        }
        
        $member->delete();
        $this->decrement('members_count');
        
        return true;
    }

    public function updateMemberRole($userId, $role)
    {
        $member = $this->members()->where('user_id', $userId)->first();
        
        if (!$member) {
            return false;
        }
        
        $member->update(['role' => $role]);
        
        return true;
    }

    public function getUnreadMessageCount($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        $member = $this->members()->where('user_id', $userId)->first();
        
        if (!$member || !$member->last_read_at) {
            return $this->messages_count;
        }
        
        return $this->messages()
            ->where('created_at', '>', $member->last_read_at)
            ->where('status', '!=', 'deleted')
            ->count();
    }

    public function markMessagesAsRead($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        $member = $this->members()->where('user_id', $userId)->first();
        
        if ($member) {
            $member->update(['last_read_at' => now()]);
        }
    }

    public function updateMessageCount()
    {
        $this->update([
            'messages_count' => $this->messages()->where('status', '!=', 'deleted')->count(),
            'last_message_at' => $this->messages()->where('status', '!=', 'deleted')->latest()->first()?->created_at,
        ]);
    }

    // Events
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($group) {
            if (empty($group->slug)) {
                $group->slug = Str::slug($group->name);
            }
        });

        static::created(function ($group) {
            // Add creator as admin member
            $group->members()->create([
                'user_id' => $group->creator_id,
                'role' => 'admin',
            ]);
        });
    }
}
