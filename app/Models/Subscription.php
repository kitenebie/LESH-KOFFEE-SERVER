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
        'items_limit',        // Total maximum items over the entire subscription
        'items_per_week',     // Weekly limit of free items
        'expiration_days',    // How many days this subscription lasts
        'duration_days',      // Legacy — same as expiration_days
        'redemption_type',    // 'all', 'category', 'products'
        'loyalty_points',
        'icon',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'items_limit' => 'integer',
        'items_per_week' => 'integer',
        'expiration_days' => 'integer',
        'duration_days' => 'integer',
        'loyalty_points' => 'integer',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────────

    public function eligibleCategories()
    {
        return $this->belongsToMany(Category::class, 'subscription_categories');
    }

    public function eligibleProducts()
    {
        return $this->belongsToMany(Product::class, 'subscription_products');
    }

    public function perks()
    {
        return $this->hasMany(SubscriptionPerk::class);
    }

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    public function subscribers()
    {
        return $this->hasMany(User::class, 'active_subscription_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────────

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }
}
