<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserConnection extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'connected_user_id',
        'status',
        'message',
        'connected_at',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
    ];

    // User who sent the request
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // User who received the request
    public function connectedUser()
    {
        return $this->belongsTo(User::class, 'connected_user_id');
    }

    // Accept connection
    public function accept()
    {
        $this->update([
            'status' => 'accepted',
            'connected_at' => now(),
        ]);

        return $this;
    }

    // Reject connection
    public function reject()
    {
        $this->update(['status' => 'rejected']);
        return $this;
    }

    // Check if connection is pending
    public function isPending()
    {
        return $this->status === 'pending';
    }

    // Check if connection is accepted
    public function isAccepted()
    {
        return $this->status === 'accepted';
    }
}