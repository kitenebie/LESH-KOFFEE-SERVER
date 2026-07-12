<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'drinks',
        'drinks_per_week',
        'duration_days',
        'loyalty_points',
        'icon',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'drinks' => 'integer',
        'drinks_per_week' => 'integer',
        'duration_days' => 'integer',
        'loyalty_points' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function subscribers()
    {
        return $this->hasMany(User::class, 'active_subscription_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    /**
     * Only return subscriptions that haven't expired (or have no expiry).
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Check if this subscription offer has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
