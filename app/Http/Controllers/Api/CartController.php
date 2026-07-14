<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\UserCartMeta;
use App\Models\Product;
use App\Models\ProductCustomization;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\CartCalculationService;

class CartController extends Controller
{
    public function __construct(
        protected CartCalculationService $cartCalc
    ) {}

    /**
     * GET /api/cart
     * Returns full cart state: items + meta + computed totals.
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * POST /api/cart/add
     * Add item to cart (or increment if same product + customization exists).
     */
    public function add(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'customization' => 'nullable|array',
        ]);

        $product = Product::find($validated['product_id']);
        $unitPrice = $this->calculateUnitPrice($product, $validated['customization'] ?? null);

        // Check if same product + same customization already in cart
        $existing = CartItem::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->get()
            ->first(function ($item) use ($validated) {
                return json_encode($item->customization) === json_encode($validated['customization'] ?? null);
            });

        if ($existing) {
            $existing->increment('quantity', $validated['quantity']);
            $existing->update(['unit_price' => $unitPrice]);
            $item = $existing->fresh();
        } else {
            $item = CartItem::create([
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
                'customization' => $validated['customization'] ?? null,
                'unit_price' => $unitPrice,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * PUT /api/cart/{id}
     * Update quantity of a cart item.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $item = CartItem::where('id', $id)->where('user_id', $userId)->first();
        if (!$item) return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:1',
        ]);

        $item->update(['quantity' => $validated['quantity']]);

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * DELETE /api/cart/{id}
     * Remove item from cart.
     */
    public function destroy(int $id): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $item = CartItem::where('id', $id)->where('user_id', $userId)->first();
        if (!$item) return response()->json(['success' => false, 'message' => 'Cart item not found'], 404);

        $item->delete();

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * DELETE /api/cart
     * Clear entire cart for the user.
     */
    public function clear(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        CartItem::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * POST /api/cart/sync
     * Full cart sync — replaces server cart with frontend cart state.
     * Used on app startup or after offline edits.
     */
    public function sync(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.customization' => 'nullable|array',
        ]);

        // Clear existing cart
        CartItem::where('user_id', $userId)->delete();

        // Insert new items
        foreach ($validated['items'] as $itemData) {
            $product = Product::find($itemData['product_id']);
            $unitPrice = $this->calculateUnitPrice($product, $itemData['customization'] ?? null);

            CartItem::create([
                'user_id' => $userId,
                'product_id' => $itemData['product_id'],
                'quantity' => $itemData['quantity'],
                'customization' => $itemData['customization'] ?? null,
                'unit_price' => $unitPrice,
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * GET /api/cart/meta
     * Get cart meta (applied vouchers, subscription preference, fulfillment).
     */
    public function getMeta(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $meta = UserCartMeta::firstOrCreate(
            ['user_id' => $userId],
            ['fulfillment_mode' => 'DineIn', 'applied_voucher_codes' => [], 'use_subscription' => false, 'subscription_items_to_use' => 0]
        );

        return response()->json([
            'success' => true,
            'data' => $meta,
        ]);
    }

    /**
     * PUT /api/cart/meta
     * Update cart meta.
     */
    public function updateMeta(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $validated = $request->validate([
            'fulfillment_mode' => 'nullable|string|in:DineIn,Delivery',
            'applied_voucher_codes' => 'nullable|array',
            'applied_voucher_codes.*' => 'string',
            'use_subscription' => 'nullable|boolean',
            'subscription_items_to_use' => 'nullable|integer|min:0',
        ]);

        $meta = UserCartMeta::updateOrCreate(
            ['user_id' => $userId],
            collect($validated)->filter(fn ($v) => !is_null($v))->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $this->cartCalc->calculate($userId),
        ]);
    }

    /**
     * POST /api/cart/apply-voucher
     * Add a voucher code to cart meta.
     */
    public function applyVoucher(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $validated = $request->validate(['code' => 'required|string']);

        $meta = UserCartMeta::firstOrCreate(
            ['user_id' => $userId],
            ['fulfillment_mode' => 'DineIn', 'applied_voucher_codes' => [], 'use_subscription' => false, 'subscription_items_to_use' => 0]
        );

        $codes = $meta->applied_voucher_codes ?? [];
        if (!in_array($validated['code'], $codes)) {
            $codes[] = $validated['code'];
            $meta->update(['applied_voucher_codes' => $codes]);
        }

        return response()->json(['success' => true, 'data' => $this->cartCalc->calculate($userId)]);
    }

    /**
     * POST /api/cart/remove-voucher
     * Remove a voucher code from cart meta.
     */
    public function removeVoucher(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $validated = $request->validate(['code' => 'required|string']);

        $meta = UserCartMeta::where('user_id', $userId)->first();
        if ($meta) {
            $codes = array_values(array_filter($meta->applied_voucher_codes ?? [], fn ($c) => $c !== $validated['code']));
            $meta->update(['applied_voucher_codes' => $codes]);
        }

        return response()->json(['success' => true, 'data' => $this->cartCalc->calculate($userId)]);
    }

    // ─── Private Helpers ───────────────────────────────────────────────

    /**
     * Calculate unit price from DB (base product price + customization extras).
     */
    private function calculateUnitPrice(?Product $product, ?array $customization): float
    {
        if (!$product) return 0;

        $basePrice = (float) $product->price;
        $extraPrice = 0;

        if ($customization && isset($customization['selections'])) {
            $productCustomization = ProductCustomization::where('product_id', $product->id)->first();
            $customData = $productCustomization?->customizations ?? [];

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
        }

        return round($basePrice + $extraPrice, 2);
    }
}
