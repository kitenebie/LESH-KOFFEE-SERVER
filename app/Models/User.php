<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'first_name',
        'email',
        'phone',
        'password',
        'avatar',
        'member_level',
        'member_level_label',
        'stamps_collected',
        'stamps_required',
        'subscription_balance',
        'active_subscription_id',
        'joined_date',
        'latitude',
        'longitude',
    ];

    /**
     * Get loyalty points from the LeshPoints relationship (source of truth).
     * Overrides the stale `loyalty_points` column on the users table.
     */
    public function getLoyaltyPointsAttribute(): int
    {
        // If leshPoints relationship is loaded, use it; otherwise query fresh
        if ($this->relationLoaded('leshPoints') && $this->leshPoints) {
            return (int) $this->leshPoints->balance;
        }
        return (int) ($this->leshPoints()?->first()?->balance ?? 0);
    }

    /**
     * Get wallet balance from the LeshWallet relationship (source of truth).
     * Overrides the stale `wallet_balance` column on the users table.
     */
    public function getWalletBalanceAttribute(): float
    {
        if ($this->relationLoaded('leshWallet') && $this->leshWallet) {
            return (float) $this->leshWallet->balance;
        }
        return (float) ($this->leshWallet()?->first()?->balance ?? 0);
    }

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'stamps_collected' => 'integer',
        'stamps_required' => 'integer',
        'subscription_balance' => 'integer',
        'joined_date' => 'date',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function leshWallet()
    {
        return $this->hasOne(LeshWallet::class);
    }

    public function leshPoints()
    {
        return $this->hasOne(LeshPoints::class);
    }

    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function walletTransactions()
    {
        return $this->hasMany(WalletTransaction::class);
    }

    public function loyaltyTransactions()
    {
        return $this->hasMany(LoyaltyTransaction::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function stampAchievements()
    {
        return $this->hasMany(StampAchievement::class);
    }

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function activeSubscription()
    {
        return $this->belongsTo(Subscription::class, 'active_subscription_id');
    }

    public function deliveryTrackings()
    {
        return $this->hasMany(DeliveryTracking::class);
    }

    public function productRatings()
    {
        return $this->hasMany(ProductRating::class);
    }
}
