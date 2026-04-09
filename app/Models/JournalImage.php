<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JournalImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'journal_id',
        'kind',
        'sort_order',
        'disk',
        'path',
        'url',
        'original_name',
        'caption',
        'source',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function journal()
    {
        return $this->belongsTo(Journal::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
