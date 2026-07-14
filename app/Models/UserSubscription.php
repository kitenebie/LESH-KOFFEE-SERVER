<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'user_id',
        'subscription_id',
        'starts_at',
        'expires_at',
        'items_remaining',
        'items_used',
        'items_limit',
        'items_per_week',
        'items_used_this_week',
        'week_started_at',
        'status',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'week_started_at' => 'datetime',
        'items_remaining' => 'integer',
        'items_used' => 'integer',
        'items_limit' => 'integer',
        'items_per_week' => 'integer',
        'items_used_this_week' => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    // ─── Computed ─────────────────────────────────────────────────────────────────

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if this week's window has passed and reset if needed.
     */
    public function resetWeekIfNeeded(): void
    {
        if (!$this->week_started_at || $this->week_started_at->diffInDays(now()) >= 7) {
            $this->update([
                'items_used_this_week' => 0,
                'week_started_at' => now(),
            ]);
        }
    }

    /**
     * Can the user still redeem items?
     * Checks: active status, not expired, items remaining > 0, weekly limit not reached.
     */
    public function getIsUsableAttribute(): bool
    {
        if ($this->status !== 'active' || $this->is_expired) return false;
        if ($this->items_remaining <= 0) return false;

        // Check weekly limit
        $this->resetWeekIfNeeded();
        if ($this->items_per_week > 0 && $this->items_used_this_week >= $this->items_per_week) {
            return false;
        }

        return true;
    }

    /**
     * How many items can the user redeem right now (considering weekly limit).
     */
    public function getItemsAvailableNowAttribute(): int
    {
        if (!$this->is_usable) return 0;

        $this->resetWeekIfNeeded();

        $remainingTotal = $this->items_remaining;
        $remainingThisWeek = $this->items_per_week > 0
            ? max(0, $this->items_per_week - $this->items_used_this_week)
            : $remainingTotal; // no weekly limit

        return min($remainingTotal, $remainingThisWeek);
    }

    /**
     * Use item(s) from this subscription.
     */
    public function useItems(int $count = 1): bool
    {
        if (!$this->is_usable) return false;

        $this->resetWeekIfNeeded();

        // Validate weekly limit
        if ($this->items_per_week > 0) {
            $availableThisWeek = $this->items_per_week - $this->items_used_this_week;
            if ($count > $availableThisWeek) return false;
        }

        // Validate total remaining
        if ($count > $this->items_remaining) return false;

        $this->decrement('items_remaining', $count);
        $this->increment('items_used', $count);
        $this->increment('items_used_this_week', $count);

        // Auto-complete if all items used (hit items_limit)
        if ($this->items_remaining <= 0) {
            $this->update(['status' => 'completed']);
        }

        return true;
    }

    /**
     * Mark subscription as expired.
     */
    public function markExpired(): void
    {
        if ($this->status === 'active' && $this->is_expired) {
            $this->update(['status' => 'expired']);
        }
    }

    /**
     * End subscription early (user cancellation).
     */
    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    /**
     * Create a new user subscription from a subscription plan.
     */
    public static function subscribe(int $userId, Subscription $plan): self
    {
        $expirationDays = $plan->expiration_days ?? $plan->duration_days ?? 360;

        return self::create([
            'user_id' => $userId,
            'subscription_id' => $plan->id,
            'starts_at' => now(),
            'expires_at' => now()->addDays($expirationDays),
            'items_remaining' => $plan->items_limit,
            'items_used' => 0,
            'items_limit' => $plan->items_limit,
            'items_per_week' => $plan->items_per_week,
            'items_used_this_week' => 0,
            'week_started_at' => now(),
            'status' => 'active',
        ]);
    }
}
