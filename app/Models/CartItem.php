<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'customization',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'customization' => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Get the real unit price (base + customization extras from DB).
     */
    public function getCalculatedPriceAttribute(): float
    {
        $basePrice = (float) ($this->product?->price ?? 0);
        $extraPrice = 0;

        if ($this->customization && isset($this->customization['selections'])) {
            $productCustomization = ProductCustomization::where('product_id', $this->product_id)->first();
            $customData = $productCustomization?->customizations ?? [];

            foreach ($this->customization['selections'] as $group => $selectedOptions) {
                $groupConfig = $customData[$group] ?? null;
                if (!$groupConfig || !isset($groupConfig['options'])) continue;

                $selectedList = is_array($selectedOptions) ? $selectedOptions : [$selectedOptions];
                foreach ($selectedList as $selectedName) {
                    foreach ($groupConfig['options'] as $option) {
                        if (($option['name'] ?? '') === $selectedName) {
                            $extraPrice += (float) ($option['price'] ?? 0);
                        }
                    }
                }
            }
        }

        return $basePrice + $extraPrice;
    }

    /**
     * Get line total (unit_price × quantity).
     */
    public function getLineTotalAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }
}
