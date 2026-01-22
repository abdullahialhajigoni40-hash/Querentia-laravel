<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'bio',
        'website',
        'linkedin',
        'twitter',
        'google_scholar',
        'researchgate',
        'education',
        'experience',
        'publications',
        'skills',
        'awards',
        'total_connections',
        'total_publications',
        'total_reviews',
    ];

    protected $casts = [
        'education' => 'array',
        'experience' => 'array',
        'publications' => 'array',
        'skills' => 'array',
        'awards' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}