<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Subscription;
use App\Models\UserSubscription;

class SubscriptionPerkService
{
    /**
     * Calculate perk discounts for a user's cart items based on their active subscription.
     * 
     * @param int $userId
     * @param array $cartItems [{product_id, quantity, price}]
     * @return array {total_discount, perks_applied: [{perk_id, category_name, discount_type, discount_value, applied_discount}]}
     */
    public function calculatePerksForCart(int $userId, array $cartItems): array
    {
        // Get user's active subscription
        $userSub = UserSubscription::where('user_id', $userId)
            ->active()
            ->latest()
            ->first();

        if (!$userSub) {
            return ['total_discount' => 0, 'perks_applied' => []];
        }

        $subscription = Subscription::with(['perks' => function ($q) {
            $q->where('is_active', true);
        }, 'perks.category'])->find($userSub->subscription_id);

        if (!$subscription || $subscription->perks->isEmpty()) {
            return ['total_discount' => 0, 'perks_applied' => []];
        }

        // Enrich cart items with category_id from products
        $productIds = array_column($cartItems, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $enrichedItems = array_map(function ($item) use ($products) {
            $product = $products->get($item['product_id']);
            return array_merge($item, [
                'category_id' => $product?->category_id ?? null,
                'price' => $item['price'] ?? $product?->price ?? 0,
            ]);
        }, $cartItems);

        // Apply each perk
        $totalDiscount = 0;
        $perksApplied = [];

        foreach ($subscription->perks as $perk) {
            if ($perk->perk_type !== 'category_discount') continue;

            $perkDiscount = $perk->calculateTotalDiscount($enrichedItems);

            if ($perkDiscount > 0) {
                $totalDiscount += $perkDiscount;
                $perksApplied[] = [
                    'perk_id' => $perk->id,
                    'category_name' => $perk->category?->name ?? 'Unknown',
                    'discount_type' => $perk->discount_type,
                    'discount_value' => $perk->discount_value,
                    'max_discount' => $perk->max_discount,
                    'applied_discount' => $perkDiscount,
                ];
            }
        }

        return [
            'total_discount' => round($totalDiscount, 2),
            'perks_applied' => $perksApplied,
        ];
    }

    /**
     * Get the perks description for a subscription (for frontend display).
     * 
     * @param int $subscriptionId
     * @return array [{category_name, discount_type, discount_value, description}]
     */
    public function getPerksDescription(int $subscriptionId): array
    {
        $subscription = Subscription::with(['perks' => function ($q) {
            $q->where('is_active', true);
        }, 'perks.category'])->find($subscriptionId);

        if (!$subscription || $subscription->perks->isEmpty()) {
            return [];
        }

        return $subscription->perks->map(function ($perk) {
            $categoryName = $perk->category?->name ?? 'All';
            $desc = '';

            if ($perk->discount_type === 'percent') {
                $desc = intval($perk->discount_value) . "% {$categoryName} discount";
                if ($perk->max_discount) {
                    $desc .= " (max ₱" . number_format($perk->max_discount, 0) . ")";
                }
            } else {
                $desc = "₱" . number_format($perk->discount_value, 2) . " {$categoryName} discount";
            }

            return [
                'perk_id' => $perk->id,
                'category_id' => $perk->category_id,
                'category_name' => $categoryName,
                'discount_type' => $perk->discount_type,
                'discount_value' => (float) $perk->discount_value,
                'max_discount' => $perk->max_discount ? (float) $perk->max_discount : null,
                'description' => $desc,
            ];
        })->toArray();
    }
}
