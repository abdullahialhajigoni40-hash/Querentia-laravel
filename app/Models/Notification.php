<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Relationship with user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mark notification as read
    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }

    // Check if notification is unread
    public function isUnread()
    {
        return is_null($this->read_at);
    }

    // Check if notification is read
    public function isRead()
    {
        return !is_null($this->read_at);
    }

    // Get notification icon based on type
    public function getIconAttribute()
    {
        $icons = [
            'connection_request' => 'fas fa-user-plus',
            'connection_accepted' => 'fas fa-user-check',
            'review_completed' => 'fas fa-star',
            'review_requested' => 'fas fa-clipboard-check',
            'ai_processing_complete' => 'fas fa-robot',
            'journal_published' => 'fas fa-book',
            'comment_received' => 'fas fa-comment',
            'like_received' => 'fas fa-heart',
        ];

        return $icons[$this->type] ?? 'fas fa-bell';
    }

    // Get notification color based on type
    public function getColorAttribute()
    {
        $colors = [
            'connection_request' => 'from-purple-400 to-blue-500',
            'connection_accepted' => 'from-green-400 to-teal-500',
            'review_completed' => 'from-yellow-400 to-orange-500',
            'review_requested' => 'from-blue-400 to-indigo-500',
            'ai_processing_complete' => 'from-pink-400 to-purple-500',
            'journal_published' => 'from-indigo-400 to-purple-500',
            'comment_received' => 'from-cyan-400 to-blue-500',
            'like_received' => 'from-red-400 to-pink-500',
        ];

        return $colors[$this->type] ?? 'from-gray-400 to-gray-500';
    }

    // Get action URL for notification
    public function getActionUrlAttribute()
    {
        switch ($this->type) {
            case 'connection_request':
                return route('my-connections');
            case 'connection_accepted':
                return route('my-connections');
            case 'review_completed':
                if (isset($this->data['journal_id'])) {
                    return route('journal.show', $this->data['journal_id']);
                }
                break;
            case 'review_requested':
                if (isset($this->data['journal_id'])) {
                    return route('journal.show', $this->data['journal_id']);
                }
                break;
            case 'ai_processing_complete':
                if (isset($this->data['journal_id'])) {
                    return route('journal.edit', $this->data['journal_id']);
                }
                break;
        }

        return '#';
    }
}
