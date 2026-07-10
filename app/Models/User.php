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
        'wallet_balance',
        'loyalty_points',
        'stamps_collected',
        'stamps_required',
        'subscription_balance',
        'active_subscription_id',
        'joined_date',
        'latitude',
        'longitude',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'wallet_balance' => 'decimal:2',
        'loyalty_points' => 'integer',
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
