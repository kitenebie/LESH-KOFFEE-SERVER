<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPerk extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'perk_type',
        'category_id',
        'discount_type',
        'discount_value',
        'max_discount',
        'usage_limit_per_order',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit_per_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Calculate the discount for a given item price.
     * Returns the discount amount (not the final price).
     */
    public function calculateDiscount(float $itemPrice): float
    {
        if (!$this->is_active) return 0;

        if ($this->discount_type === 'percent') {
            $discount = $itemPrice * ($this->discount_value / 100);
            // Apply cap if set
            if ($this->max_discount && $discount > $this->max_discount) {
                $discount = (float) $this->max_discount;
            }
            return round($discount, 2);
        }

        // Fixed discount — can't exceed item price
        return min((float) $this->discount_value, $itemPrice);
    }

    /**
     * Calculate total perk discount for a list of cart items in the perk's category.
     * 
     * @param array $items [{product_id, category_id, price, quantity}]
     * @return float Total discount amount
     */
    public function calculateTotalDiscount(array $items): float
    {
        if (!$this->is_active || !$this->category_id) return 0;

        // Filter items to this perk's category
        $eligibleItems = array_filter($items, fn($item) => 
            ($item['category_id'] ?? null) == $this->category_id
        );

        if (empty($eligibleItems)) return 0;

        $totalDiscount = 0;
        $appliedCount = 0;

        foreach ($eligibleItems as $item) {
            $qty = (int) ($item['quantity'] ?? 1);
            for ($i = 0; $i < $qty; $i++) {
                // Check per-order usage limit
                if ($this->usage_limit_per_order && $appliedCount >= $this->usage_limit_per_order) {
                    break 2;
                }
                $totalDiscount += $this->calculateDiscount((float) $item['price']);
                $appliedCount++;
            }
        }

        return round($totalDiscount, 2);
    }
}
