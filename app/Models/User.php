<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles, HasPanelShield;

    /**
     * Temporary storage for raw CVV (NOT persisted to DB).
     * Used only during registration to return CVV once to user.
     */
    public ?string $tempRawCvv = null;

    // ─── Auto-generate Lesh Account on creation ──────────────────────────────────

    protected static function booted(): void
    {
        static::created(function (User $user) {
            MembershipCard::createForUser($user);
        });

        static::creating(function (User $user) {
            if (empty($user->lesh_acc)) {
                $user->lesh_acc = self::generateLeshAccount();
            }
            if (empty($user->lesh_exp)) {
                // Expires 5 years from now
                $user->lesh_exp = now()->addYears(5)->format('m/y');
            }
            if (empty($user->lesh_cvv)) {
                $rawCvv = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);
                $user->lesh_cvv = bcrypt($rawCvv);
                // Store raw CVV in non-persisted property (returned ONCE during registration)
                $user->tempRawCvv = $rawCvv;
            }
        });
    }

    /**
     * Generate a unique 16-digit Lesh account number (formatted XXXX-XXXX-XXXX-XXXX).
     */
    private static function generateLeshAccount(): string
    {
        do {
            $digits = '';
            for ($i = 0; $i < 16; $i++) {
                $digits .= random_int(0, 9);
            }
            $formatted = implode('-', str_split($digits, 4)); // XXXX-XXXX-XXXX-XXXX
        } while (self::where('lesh_acc', $formatted)->exists());

        return $formatted;
    }

    protected $fillable = [
        'name',
        'first_name',
        'email',
        'phone',
        'password',
        'avatar',
        'lesh_acc',
        'lesh_exp',
        'lesh_cvv',
        'member_level',
        'member_level_label',
        'stamps_collected',
        'stamps_required',
        'active_subscription_id',
        'stamp_quota_category_id',
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

    /**
     * Get subscription balance from active UserSubscription (source of truth).
     */
    public function getSubscriptionBalanceAttribute(): int
    {
        // Check for active user subscription
        $activeSub = $this->userSubscriptions()
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->where('drinks_remaining', '>', 0)
            ->latest()
            ->first();

        return $activeSub ? (int) $activeSub->drinks_remaining : 0;
    }

    protected $hidden = [
        'password',
        'remember_token',
        'lesh_cvv',
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

    public function membershipCard()
    {
        return $this->hasOne(MembershipCard::class);
    }

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

    public function userSubscriptions()
    {
        return $this->hasMany(UserSubscription::class);
    }

    /**
     * Get the user's currently active (non-expired) subscription.
     */
    public function activeUserSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->latest('starts_at');
    }

    public function stampQuotaTier()
    {
        return $this->belongsTo(StampQuotaCategory::class, 'stamp_quota_category_id');
    }

    // ─── Role Helpers ────────────────────────────────────────────────────────────

    /**
     * Filament: Only admin/super-admin can access the admin panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole(['admin', 'super_admin']);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(['admin', 'super_admin']);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isUser(): bool
    {
        return !$this->hasRole(['admin', 'super_admin']);
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
