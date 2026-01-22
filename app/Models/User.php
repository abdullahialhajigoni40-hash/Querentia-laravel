<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Journal;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'institution',
        'department',
        'position',
        'research_interests',
        'profile_picture',
        'bio',
        'subscription_tier',
        'subscription_ends_at',
        'email_verified_at',
        'is_verified_researcher',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'research_interests' => 'array',
        'is_verified_researcher' => 'boolean',
    ];

    protected $appends = ['full_name', 'connection_count'];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getConnectionCountAttribute()
    {
        return $this->connections()->count();
    }

    // Profile relationship
    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    // Journals relationship
    public function journals()
    {
        return $this->hasMany(Journal::class);
    }

    // Reviews given by user
    public function reviews()
    {
        return $this->hasMany(Review::class, 'reviewer_id');
    }

    // Posts made by user
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // ========== CONNECTION RELATIONSHIPS ==========

    // Sent connection requests (where user initiated)
    public function sentConnections()
    {
        return $this->hasMany(UserConnection::class, 'user_id');
    }

    // Received connection requests (where user is the target)
    public function receivedConnections()
    {
        return $this->hasMany(UserConnection::class, 'connected_user_id');
    }

    // All connections (both sent and received that are accepted)
    public function connections()
    {
        return $this->belongsToMany(User::class, 'user_connections', 'user_id', 'connected_user_id')
                    ->wherePivot('status', 'accepted')
                    ->withTimestamps();
    }

    // Pending connection requests received (others want to connect with this user)
    public function pendingConnections()
    {
        return $this->receivedConnections()->where('status', 'pending');
    }

    // Pending connection requests sent (user wants to connect with others)
    public function pendingSentConnections()
    {
        return $this->sentConnections()->where('status', 'pending');
    }

    // Check if connected to another user
    public function isConnectedTo($userId)
    {
        return $this->connections()->where('connected_user_id', $userId)->exists();
    }

    // Check if has pending connection with another user
    public function hasPendingConnectionWith($userId)
    {
        return $this->sentConnections()
            ->where('connected_user_id', $userId)
            ->where('status', 'pending')
            ->exists();
    }

   // Add these methods to User model
public function subscriptions()
{
    return $this->hasMany(Subscription::class);
}

public function transactions()
{
    return $this->hasMany(Transaction::class);
}

public function activeSubscription()
{
    return $this->subscriptions()
        ->where('status', 'active')
        ->where('ends_at', '>', now())
        ->first();
}

public function isSubscribed()
{
    return $this->subscription_tier !== 'free' && 
           $this->subscription_ends_at && 
           $this->subscription_ends_at->isFuture();
}

public function isPro()
{
    return $this->subscription_tier === 'pro' && $this->isSubscribed();
}
    
}