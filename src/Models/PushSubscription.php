<?php

namespace LaravelWudel\LaravelWudelNotif\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PushSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'endpoint',
        'p256dh',
        'auth',
        'topic',
        'device_info',
        'expires_at',
        'last_used_at'
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'device_info' => 'array',
        'expires_at' => 'datetime',
        'last_used_at' => 'datetime'
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
    }

    /**
     * Scope a query to only include subscriptions for a specific topic.
     */
    public function scopeForTopic($query, $topic)
    {
        return $query->where('topic', $topic);
    }

    /**
     * Check if the subscription is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Update the last used timestamp.
     */
    public function markAsUsed(): void
    {
        $this->update(['last_used_at' => now()]);
    }
}
