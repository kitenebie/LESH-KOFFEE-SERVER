<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCustomization;
use App\Models\Voucher;
use App\Models\UserVoucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdminScanController extends Controller
{
    /**
     * Process a scanned order QR code.
     * 
     * Flow:
     * 1. Validate admin permission
     * 2. Validate request data
     * 3. Check for duplicate order_number (server-side dedup)
     * 4. Recalculate prices server-side (never trust client)
     * 5. Create order + order_items atomically
     * 6. Return success with order details
     */
    public function processOrderQR(Request $request): JsonResponse
    {
        $admin = Auth::user();

        if (!$admin || !$admin->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        // Validate incoming data
        $validated = $request->validate([
            'order_number' => 'required|string|max:50',
            'user_id' => 'required|integer|exists:users,id',
            'fulfillment' => 'required|string|in:DineIn,Delivery',
            'ref_no' => 'nullable|string|max:10',
            'payment_method' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.customization' => 'nullable|array',
            'subtotal' => 'required|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'voucherCode' => 'nullable|string|max:100',
            'total' => 'required|numeric|min:0',
        ]);

        // ─── SERVER-SIDE DUPLICATE CHECK ─────────────────────────────────────
        $existingOrder = Order::where('order_number', $validated['order_number'])->first();
        if ($existingOrder) {
            return response()->json([
                'success' => false,
                'message' => "Order {$validated['order_number']} has already been processed.",
                'duplicate' => true,
            ], 409);
        }

        // ─── SERVER-SIDE PRICE RECALCULATION ─────────────────────────────────
        $recalculated = $this->recalculateFromItems($validated['items'], (float) ($validated['delivery_fee'] ?? 0));

        // ─── VOUCHER VALIDATION (if present) ─────────────────────────────────
        $discount = 0;
        $usedVoucherIds = [];
        if (!empty($validated['voucherCode'])) {
            $voucherResult = $this->validateVoucher(
                $validated['user_id'],
                $validated['voucherCode'],
                $recalculated['subtotal']
            );
            $discount = $voucherResult['discount'];
            $usedVoucherIds = $voucherResult['used_voucher_ids'];
        }

        $total = max(0, round($recalculated['subtotal'] + $recalculated['delivery_fee'] - $discount, 2));

        // ─── CREATE ORDER ATOMICALLY ─────────────────────────────────────────
        try {
            $order = DB::transaction(function () use ($validated, $recalculated, $discount, $total, $admin, $usedVoucherIds) {
                $order = Order::create([
                    'user_id' => $validated['user_id'],
                    'order_number' => $validated['order_number'],
                    'date' => now()->toDateString(),
                    'time' => now()->format('H:i'),
                    'status' => 'Queued',
                    'current_step' => 'queue',
                    'fulfillment' => $validated['fulfillment'],
                    'ref_no' => $validated['ref_no'] ?? null,
                    'payment_method' => $validated['payment_method'],
                    'cashier' => $admin->name ?? 'Admin',
                    'subtotal' => $recalculated['subtotal'],
                    'delivery_fee' => $recalculated['delivery_fee'],
                    'discount' => $discount,
                    'total' => $total,
                ]);

                // Create order items with server-verified prices
                foreach ($recalculated['items'] as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'name' => $item['name'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'customization' => $item['customization'] ?? null,
                    ]);
                }

                // Mark vouchers as used
                if (!empty($usedVoucherIds)) {
                    UserVoucher::whereIn('id', $usedVoucherIds)->update(['is_used' => true]);
                }

                return $order->load('items.product');
            });

            Log::info('[AdminScan] Order created from QR scan', [
                'order_number' => $order->order_number,
                'user_id' => $order->user_id,
                'total' => $order->total,
                'scanned_by' => $admin->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Order {$order->order_number} saved successfully!",
                'data' => [
                    'order_number' => $order->order_number,
                    'fulfillment' => $order->fulfillment,
                    'ref_no' => $order->ref_no,
                    'status' => $order->status,
                    'total' => $order->total,
                    'items_count' => $order->items->count(),
                    'customer_name' => $order->user?->name ?? 'Customer',
                ],
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle race condition: unique constraint violation on order_number
            if (str_contains($e->getMessage(), 'Duplicate entry') || str_contains($e->getMessage(), 'UNIQUE constraint failed')) {
                return response()->json([
                    'success' => false,
                    'message' => "Order {$validated['order_number']} has already been processed.",
                    'duplicate' => true,
                ], 409);
            }
            throw $e;
        }
    }

    /**
     * Recalculate order prices from DB (never trust QR-embedded prices).
     */
    private function recalculateFromItems(array $items, float $deliveryFee): array
    {
        $productIds = array_column($items, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');
        $customizations = ProductCustomization::whereIn('product_id', $productIds)->get()->keyBy('product_id');

        $subtotal = 0;
        $verifiedItems = [];

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) continue;

            $unitPrice = (float) $product->price;

            // Add customization addon prices from DB
            $customization = $item['customization'] ?? null;
            if ($customization && isset($customization['selections'])) {
                $productCustomization = $customizations->get($item['product_id']);
                $customizationData = $productCustomization?->customizations ?? [];

                foreach ($customization['selections'] as $group => $selectedOptions) {
                    $groupConfig = $customizationData[$group] ?? null;
                    if (!$groupConfig || !isset($groupConfig['options'])) continue;

                    $selectedList = is_array($selectedOptions) ? $selectedOptions : [$selectedOptions];
                    foreach ($selectedList as $selectedName) {
                        foreach ($groupConfig['options'] as $option) {
                            if (($option['name'] ?? '') === $selectedName) {
                                $unitPrice += (float) ($option['price'] ?? 0);
                            }
                        }
                    }
                }
            }

            $quantity = (int) $item['quantity'];
            $subtotal += $unitPrice * $quantity;

            $verifiedItems[] = [
                'product_id' => $item['product_id'],
                'name' => $product->name, // use DB name, not client-provided
                'quantity' => $quantity,
                'price' => $unitPrice,
                'customization' => $customization,
            ];
        }

        return [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => $deliveryFee,
            'items' => $verifiedItems,
        ];
    }

    /**
     * Validate and compute voucher discount.
     */
    private function validateVoucher(int $userId, string $voucherCode, float $subtotal): array
    {
        $result = ['discount' => 0, 'used_voucher_ids' => []];

        $codes = array_filter(array_map('trim', explode(',', $voucherCode)));

        foreach ($codes as $code) {
            $code = strtoupper($code);

            $voucher = Voucher::where('code', $code)->where('is_active', true)->first();
            if (!$voucher) continue;

            $userVoucher = UserVoucher::where('user_id', $userId)
                ->where('voucher_id', $voucher->id)
                ->where('is_used', false)
                ->first();

            if (!$userVoucher || $userVoucher->isExpired()) continue;
            if (!$voucher->isApplicableTo($subtotal)) continue;

            $discount = $voucher->getDiscountAmount($subtotal);
            $result['discount'] += $discount;
            $result['used_voucher_ids'][] = $userVoucher->id;
        }

        $result['discount'] = round($result['discount'], 2);
        return $result;
    }

    /**
     * Verify an existing order by order_number (legacy scanner flow).
     */
    public function verifyOrder(Request $request): JsonResponse
    {
        $admin = Auth::user();

        if (!$admin || !$admin->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate(['order_number' => 'required|string']);

        $order = Order::where('order_number', $request->order_number)
            ->with(['user', 'items.product'])
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order verified.',
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total' => $order->total,
                'customer_name' => $order->user?->name ?? 'Unknown',
                'fulfillment' => $order->fulfillment,
                'ref_no' => $order->ref_no,
                'items_count' => $order->items->count(),
            ],
        ]);
    }
}
