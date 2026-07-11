<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'code',
        'description',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Check if the voucher has expired.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false;
        }
        return now()->greaterThan($this->expires_at);
    }

    /**
     * Check if the voucher is usable for a given order subtotal.
     * Must be: not expired, not used, and meets min_order_amount.
     */
    public function isUsable(float $orderSubtotal = 0): bool
    {
        if ($this->is_used) return false;
        if ($this->isExpired()) return false;

        $voucher = $this->voucher;
        if ($voucher && !$voucher->isApplicableTo($orderSubtotal)) {
            return false;
        }

        return true;
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    /**
     * Filter to only non-expired, non-used vouchers.
     */
    public function scopeActive($query)
    {
        return $query->where('is_used', false)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }
}
