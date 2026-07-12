<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'starts_at',
        'expires_at',
        'drinks_remaining',
        'drinks_used',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'drinks_remaining' => 'integer',
        'drinks_used' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────

    /**
     * Only active (non-expired, non-cancelled) subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Check if this user subscription has expired.
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if this subscription is still active and has drinks remaining.
     */
    public function getIsUsableAttribute(): bool
    {
        return $this->status === 'active'
            && !$this->is_expired
            && $this->drinks_remaining > 0;
    }

    /**
     * Use a drink from this subscription.
     */
    public function useDrink(string $description = 'Subscription drink redeemed'): bool
    {
        if (!$this->is_usable) {
            return false;
        }

        $this->decrement('drinks_remaining');
        $this->increment('drinks_used');

        // Auto-complete if all drinks used
        if ($this->drinks_remaining <= 0) {
            $this->update(['status' => 'completed']);
        }

        return true;
    }

    /**
     * Mark subscription as expired (called by scheduler or on check).
     */
    public function markExpired(): void
    {
        if ($this->status === 'active' && $this->is_expired) {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * Create a new user subscription from a subscription plan.
     */
    public static function subscribe(int $userId, Subscription $plan): self
    {
        $durationDays = $plan->duration_days ?? 30;
        $drinksPerWeek = $plan->drinks_per_week ?? $plan->drinks;
        $totalWeeks = (int) ceil($durationDays / 7);
        $totalDrinks = $drinksPerWeek * $totalWeeks;

        return self::create([
            'user_id' => $userId,
            'subscription_id' => $plan->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays($durationDays),
            'drinks_remaining' => $totalDrinks,
            'drinks_used' => 0,
            'status' => 'active',
        ]);
    }
}
