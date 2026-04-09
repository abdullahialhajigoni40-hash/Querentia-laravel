<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'group_id',
        'user_id',
        'content',
        'type',
        'file_path',
        'file_name',
        'metadata',
        'status',
        'edited_at',
        'deleted_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'edited_at' => 'datetime',
        'deleted_at' => 'datetime',
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
        return $query->where('status', '!=', 'deleted');
    }

    public function scopeText($query)
    {
        return $query->where('type', 'text');
    }

    public function scopeFile($query)
    {
        return $query->where('type', 'file');
    }

    public function scopeImage($query)
    {
        return $query->where('type', 'image');
    }

    // Methods
    public function isText()
    {
        return $this->type === 'text';
    }

    public function isFile()
    {
        return $this->type === 'file';
    }
    
    public function isImage()
    {
        return $this->type === 'image';
    }

    public function isLink()
    {
        return $this->type === 'link';
    }

    public function isEdited()
    {
        return !is_null($this->edited_at);
    }

    public function isDeleted()
    {
        return $this->status === 'deleted';
    }

    public function getFormattedContentAttribute()
    {
        if ($this->isDeleted()) {
            return '[Message deleted]';
        }
        
        return nl2br(e($this->content));
    }

    public function getTimeAttribute()
    {
        return $this->created_at->format('g:i A');
    }

    public function getDateAttribute()
    {
        return $this->created_at->format('M j, Y');
    }

    public function getFullDateAttribute()
    {
        return $this->created_at->format('M j, Y \a\t g:i A');
    }

    public function getFileSizeAttribute()
    {
        if (!$this->file_path) {
            return null;
        }
        
        $path = storage_path('app/public/' . $this->file_path);
        
        if (file_exists($path)) {
            $bytes = filesize($path);
            
            if ($bytes >= 1073741824) {
                return number_format($bytes / 1073741824, 2) . ' GB';
            } elseif ($bytes >= 1048576) {
                return number_format($bytes / 1048576, 2) . ' MB';
            } elseif ($bytes >= 1024) {
                return number_format($bytes / 1024, 2) . ' KB';
            } else {
                return $bytes . ' bytes';
            }
        }
        
        return null;
    }

    public function getFileExtensionAttribute()
    {
        if (!$this->file_name) {
            return null;
        }
        
        return pathinfo($this->file_name, PATHINFO_EXTENSION);
    }

    public function canEdit($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // Can edit if it's your message and it's not deleted
        return $this->user_id === $userId && !$this->isDeleted();
    }

    public function canDelete($userId = null)
    {
        $userId = $userId ?? auth()->id();
        
        // Can delete if it's your message or you're a group admin/moderator
        if ($this->user_id === $userId) {
            return true;
        }
        
        $member = $this->group->members()->where('user_id', $userId)->first();
        
        return $member && $member->isModerator();
    }

    public function markAsDeleted()
    {
        $this->update([
            'status' => 'deleted',
            'deleted_at' => now(),
        ]);
        
        $this->group->updateMessageCount();
    }

    public function edit($content)
    {
        $this->update([
            'content' => $content,
            'edited_at' => now(),
        ]);
    }
}
