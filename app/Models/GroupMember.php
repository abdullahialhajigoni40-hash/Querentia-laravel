<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'status',
        'joined_at',
        'last_read_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'last_read_at' => 'datetime',
    ];

    // Relationships
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAdmin($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeModerator($query)
    {
        return $query->whereIn('role', ['admin', 'moderator']);
    }

    // Methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isModerator()
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function canManageGroup()
    {
        return $this->isModerator();
    }

    public function canManageMembers()
    {
        return $this->isAdmin();
    }

    public function canPostMessages()
    {
        return $this->status === 'active';
    }

    public function getFormattedRoleAttribute()
    {
        return ucfirst($this->role);
    }

    public function getFormattedStatusAttribute()
    {
        return ucfirst($this->status);
    }
}
