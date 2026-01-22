<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'reference',
        'type',
        'status',
        'amount',
        'amount_paid',
        'currency',
        'channel',
        'email',
        'metadata',
        'paystack_response',
        'paid_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'paystack_response' => 'array',
        'paid_at' => 'datetime',
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function isSuccessful()
    {
        return $this->status === 'success';
    }

    public function markAsPaid($amountPaid, $channel, $response = null)
    {
        $this->update([
            'status' => 'success',
            'amount_paid' => $amountPaid,
            'channel' => $channel,
            'paystack_response' => $response,
            'paid_at' => now(),
        ]);
    }
}