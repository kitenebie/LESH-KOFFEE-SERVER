<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\UserCartMeta;
use App\Models\Product;
use App\Models\ProductCustomization;
use App\Models\Subscription;
use App\Models\UserSubscription;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Support\Facades\Log;

class CartCalculationService
{
    /**
     * Calculate the full cart state for a user.
     * Returns items, meta, and computed totals (subscription + voucher + perk discounts).
     */
    public function calculate(int $userId): array
    {
        $items = CartItem::where('user_id', $userId)
            ->with('product.category')
            ->get();

        $meta = UserCartMeta::firstOrCreate(
            ['user_id' => $userId],
            ['fulfillment_mode' => 'DineIn', 'applied_voucher_codes' => [], 'use_subscription' => false, 'subscription_items_to_use' => 0]
        );

        // ─── RECALCULATE ITEM PRICES FROM DB ─────────────────────────────
        $cartItems = [];
        $subtotal = 0;

        foreach ($items as $item) {
            $basePrice = (float) ($item->product?->price ?? 0);
            $extraPrice = $this->calculateCustomizationExtra($item->product_id, $item->customization);
            $unitPrice = $basePrice + $extraPrice;
            $lineTotal = $unitPrice * $item->quantity;
            $subtotal += $lineTotal;

            $cartItems[] = [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product' => [
                    'id' => $item->product->id,
                    'name' => $item->product->name,
                    'description' => $item->product->description ?? '',
                    'price' => $basePrice,
                    'image' => $item->product->image,
                    'categoryId' => (string) $item->product->category_id,
                    'categorySlug' => $item->product->category?->slug ?? '',
                    'categoryName' => $item->product->category?->name ?? '',
                    'is_customizable' => (bool) $item->product->is_customizable,
                ],
                'quantity' => $item->quantity,
                'customization' => $item->customization,
                'base_price' => $basePrice,
                'extra_price' => $extraPrice,
                'unit_price' => $unitPrice,
                'line_total' => round($lineTotal, 2),
            ];
        }

        // ─── SUBSCRIPTION DISCOUNT ───────────────────────────────────────
        $subscriptionDiscount = 0;
        $subscriptionItemsCovered = 0;
        $activeSubscriptionName = null;

        try {
            $activeSub = UserSubscription::where('user_id', $userId)
                ->active()
                ->where('items_remaining', '>', 0)
                ->latest()
                ->first();

            if ($activeSub) { // Always apply subscription if user has active one
                $activeSubscriptionName = $activeSub->subscription?->name;
                $itemsAvailable = $activeSub->items_available_now;

                if ($itemsAvailable > 0) {
                    // Get eligible items (from subscription's redemption_type)
                    $subscription = Subscription::with(['eligibleCategories'])->find($activeSub->subscription_id);
                    $eligibleCategoryIds = null;

                    if ($subscription && $subscription->redemption_type === 'category') {
                        $eligibleCategoryIds = $subscription->eligibleCategories->pluck('id')->toArray();
                    }

                    // Collect base prices of eligible items, sort highest first
                    $eligiblePrices = [];
                    foreach ($cartItems as $ci) {
                        $isEligible = true;
                        if ($eligibleCategoryIds !== null) {
                            $isEligible = in_array((int) $ci['product']['categoryId'], $eligibleCategoryIds);
                        }
                        if ($isEligible) {
                            for ($i = 0; $i < $ci['quantity']; $i++) {
                                $eligiblePrices[] = $ci['base_price'];
                            }
                        }
                    }

                    rsort($eligiblePrices); // highest first
                    $covered = min($itemsAvailable, count($eligiblePrices));
                    $subscriptionDiscount = round(array_sum(array_slice($eligiblePrices, 0, $covered)), 2);
                    $subscriptionItemsCovered = $covered;
                }
            }
        } catch (\Exception $e) {
            Log::warning('[CartCalc] Subscription discount failed', ['error' => $e->getMessage()]);
        }

        // ─── VOUCHER DISCOUNT ────────────────────────────────────────────
        $voucherDiscount = 0;
        $appliedVouchers = [];
        $eligibleSubtotal = max(0, $subtotal - $subscriptionDiscount);

        $voucherCodes = $meta->applied_voucher_codes ?? [];

        foreach ($voucherCodes as $code) {
            try {
                $voucher = Voucher::where('code', $code)->where('is_active', true)->first();
                if (!$voucher) continue;

                // Check user hasn't already used it
                $userVoucher = UserVoucher::where('user_id', $userId)
                    ->where('voucher_id', $voucher->id)
                    ->first();

                if (!$userVoucher || $userVoucher->is_used) continue;

                // Calculate discount
                $discount = 0;
                if ($voucher->discount_type === 'percent') {
                    $discount = round($eligibleSubtotal * ($voucher->discount_value / 100), 2);
                    if ($voucher->max_discount && $discount > $voucher->max_discount) {
                        $discount = $voucher->max_discount;
                    }
                } else {
                    $discount = min($voucher->discount_value, $eligibleSubtotal);
                }

                if ($discount > 0) {
                    $voucherDiscount += $discount;
                    $eligibleSubtotal = max(0, $eligibleSubtotal - $discount);
                    $appliedVouchers[] = [
                        'code' => $code,
                        'voucher_id' => $voucher->id,
                        'discount_type' => $voucher->discount_type,
                        'discount_value' => $voucher->discount_value,
                        'applied_discount' => $discount,
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('[CartCalc] Voucher calc failed', ['code' => $code, 'error' => $e->getMessage()]);
            }
        }

        // ─── SUBSCRIPTION PERK DISCOUNTS ────────────────────────────────────
        $perkDiscount = 0;
        $perksApplied = [];

        try {
            $perkService = new SubscriptionPerkService();
            $cartItemsForPerk = array_map(function ($ci) {
                return [
                    'product_id' => $ci['product_id'],
                    'quantity' => $ci['quantity'],
                    'price' => $ci['base_price'],
                ];
            }, $cartItems);
            $perkResult = $perkService->calculatePerksForCart($userId, $cartItemsForPerk);
            $perkDiscount = $perkResult['total_discount'] ?? 0;
            $perksApplied = $perkResult['perks_applied'] ?? [];
        } catch (\Exception $e) {
            Log::warning('[CartCalc] Perk calculation failed', ['error' => $e->getMessage()]);
        }

        // ─── DELIVERY FEE ────────────────────────────────────────────────
        $deliveryFee = $meta->fulfillment_mode === 'Delivery' ? 49 : 0;

        // ─── TOTALS ──────────────────────────────────────────────────────
        $totalDiscount = $subscriptionDiscount + $voucherDiscount + $perkDiscount;
        $total = max(0, round($subtotal + $deliveryFee - $totalDiscount, 2));

        return [
            'items' => $cartItems,
            'meta' => [
                'fulfillment_mode' => $meta->fulfillment_mode,
                'applied_voucher_codes' => $meta->applied_voucher_codes ?? [],
                'use_subscription' => $meta->use_subscription,
                'subscription_items_to_use' => $meta->subscription_items_to_use,
            ],
            'computed' => [
                'subtotal' => round($subtotal, 2),
                'delivery_fee' => $deliveryFee,
                'subscription_discount' => $subscriptionDiscount,
                'subscription_items_covered' => $subscriptionItemsCovered,
                'subscription_name' => $activeSubscriptionName,
                'voucher_discount' => $voucherDiscount,
                'applied_vouchers' => $appliedVouchers,
                'perk_discount' => $perkDiscount,
                'perks_applied' => $perksApplied,
                'total_discount' => $totalDiscount,
                'total' => $total,
            ],
        ];
    }

    /**
     * Calculate customization extra price from DB.
     */
    private function calculateCustomizationExtra(int $productId, ?array $customization): float
    {
        if (!$customization || !isset($customization['selections'])) {
            return 0;
        }

        $productCustomization = ProductCustomization::where('product_id', $productId)->first();
        $customData = $productCustomization?->customizations ?? [];
        $extraPrice = 0;

        foreach ($customization['selections'] as $group => $selectedOptions) {
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

        return round($extraPrice, 2);
    }
}
