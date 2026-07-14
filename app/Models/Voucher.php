<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'code',
        'discount',
        'label',
        'type',
        'is_active',
        'min_order_amount',
        'max_discount',
        'valid_hours',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'valid_hours' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function promo()
    {
        return $this->hasOne(Promo::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Calculate actual discount amount for a given order subtotal.
     * 
     * - type='percent': subtotal × discount, capped at max_discount
     * - type='fixed': straight PHP amount
     */
    public function getDiscountAmount(float $orderSubtotal): float
    {
        if ($this->type === 'percent') {
            $raw = $orderSubtotal * (float) $this->discount;
            $cap = $this->max_discount ? (float) $this->max_discount : PHP_FLOAT_MAX;
            return min($raw, $cap);
        }

        // Fixed discount
        return (float) $this->discount;
    }

    /**
     * Check if this voucher is applicable to the given order subtotal.
     */
    public function isApplicableTo(float $orderSubtotal): bool
    {
        if ($this->min_order_amount && $orderSubtotal < (float) $this->min_order_amount) {
            return false;
        }
        return true;
    }

    // ─── Scopes ───────────────────────────────────────────────────────

    /**
     * Filter vouchers applicable to a given order subtotal (min_order_amount check).
     */
    public function scopeApplicableTo($query, float $orderSubtotal)
    {
        return $query->where(function ($q) use ($orderSubtotal) {
            $q->whereNull('min_order_amount')
              ->orWhere('min_order_amount', '<=', $orderSubtotal);
        });
    }
}
