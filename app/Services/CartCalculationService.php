<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\UserCartMeta;
use App\Models\UserSubscription;
use App\Models\Product;
use App\Models\ProductCustomization;
use App\Models\Voucher;
use App\Models\UserVoucher;
use App\Models\Subscription;
use Illuminate\Support\Facades\Log;

class CartCalculationService
{
    /**
     * Calculate the full cart breakdown for a user.
     * Auto-applies subscription, vouchers, and perks.
     * 
     * Returns complete computed state for the frontend to display directly.
     */
    public function calculate(int $userId): array
    {
        $items = CartItem::with('product.category')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
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

            if ($activeSub && $meta->use_subscription) {
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
            $userVoucher = UserVoucher::where('user_id', $userId)
                ->whereHas('voucher', fn ($q) => $q->where('code', $code))
                ->where('is_used', false)
                ->with('voucher')
                ->first();

            if (!$userVoucher || !$userVoucher->voucher) continue;

            $voucher = $userVoucher->voucher;

            // Check min order
            if ($voucher->min_order_amount && $eligibleSubtotal < $voucher->min_order_amount) continue;

            // Calculate discount
            $disc = 0;
            if ($voucher->type === 'fixed') {
                if ($eligibleSubtotal >= $voucher->discount) {
                    $disc = (float) $voucher->discount;
                }
            } else {
                // percent
                $disc = $eligibleSubtotal * ((float) $voucher->discount / 100);
                if ($voucher->max_discount && $disc > $voucher->max_discount) {
                    $disc = (float) $voucher->max_discount;
                }
            }

            if ($disc > 0) {
                $voucherDiscount += $disc;
                $appliedVouchers[] = [
                    'code' => $code,
                    'discount' => round($disc, 2),
                    'type' => $voucher->type,
                    'label' => $voucher->name ?? $code,
                    'min_order_amount' => $voucher->min_order_amount,
                    'max_discount' => $voucher->max_discount,
                ];
            }
        }
        $voucherDiscount = round($voucherDiscount, 2);

        // ─── PERK DISCOUNT ───────────────────────────────────────────────
        $perkDiscount = 0;
        $perksApplied = [];

        try {
            $activeSub = UserSubscription::where('user_id', $userId)->active()->latest()->first();

            if ($activeSub) {
                $subscription = Subscription::with(['perks' => fn ($q) => $q->where('is_active', true), 'perks.category'])
                    ->find($activeSub->subscription_id);

                if ($subscription && $subscription->perks->isNotEmpty()) {
                    foreach ($subscription->perks as $perk) {
                        if ($perk->perk_type !== 'category_discount' || !$perk->category_id) continue;

                        // Sum prices of items in this perk's category
                        $categoryItems = array_filter($cartItems, fn ($ci) => (int) $ci['product']['categoryId'] === $perk->category_id);
                        if (empty($categoryItems)) continue;

                        $perkAmount = 0;
                        foreach ($categoryItems as $ci) {
                            $itemTotal = $ci['unit_price'] * $ci['quantity'];
                            if ($perk->discount_type === 'percent') {
                                $d = $itemTotal * ((float) $perk->discount_value / 100);
                                $perkAmount += $perk->max_discount ? min($d, (float) $perk->max_discount) : $d;
                            } else {
                                $perkAmount += min((float) $perk->discount_value, $ci['unit_price']) * $ci['quantity'];
                            }
                        }

                        if ($perkAmount > 0) {
                            $perkDiscount += $perkAmount;
                            $perksApplied[] = [
                                'category_name' => $perk->category?->name ?? '',
                                'discount_type' => $perk->discount_type,
                                'discount_value' => (float) $perk->discount_value,
                                'applied_discount' => round($perkAmount, 2),
                            ];
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            Log::warning('[CartCalc] Perk discount failed', ['error' => $e->getMessage()]);
        }
        $perkDiscount = round($perkDiscount, 2);

        // ─── DELIVERY FEE ────────────────────────────────────────────────
        $deliveryFee = $meta->fulfillment_mode === 'Delivery' ? 49 : 0;

        // ─── TOTAL ───────────────────────────────────────────────────────
        $totalDiscount = $subscriptionDiscount + $voucherDiscount + $perkDiscount;
        $total = max(0, round($subtotal - $totalDiscount + $deliveryFee, 2));

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
                'total_discount' => round($totalDiscount, 2),
                'total' => $total,
                'item_count' => array_sum(array_column($cartItems, 'quantity')),
            ],
        ];
    }

    /**
     * Calculate customization extra price from DB.
     */
    private function calculateCustomizationExtra(int $productId, ?array $customization): float
    {
        if (!$customization || !isset($customization['selections'])) return 0;

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

        return $extraPrice;
    }
}
