<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
use App\Models\Product;
use App\Models\LeshPoints;
use App\Models\ProductCustomization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository,
        protected WalletRepositoryInterface $walletRepository
    ) {}

    public function getUserOrders(int $userId)
    {
        $orders = $this->orderRepository->getByUser($userId);

        $inactiveStatuses = ['Completed'];

        return [
            'active' => $orders->filter(fn ($order) => !in_array($order->status, $inactiveStatuses))->values(),
            'past' => $orders->filter(fn ($order) => in_array($order->status, $inactiveStatuses))->values(),
        ];
    }

    public function getOrder(int $id)
    {
        return $this->orderRepository->getById($id);
    }

    /**
     * Create an order with secure wallet payment processing.
     * 
     * Uses a DB transaction to guarantee atomicity:
     * - If wallet debit succeeds but order creation fails → rollback (money returned)
     * - If order creation succeeds but debit failed → rollback (no free orders)
     * - Both must succeed for the transaction to commit.
     * 
     * Also uses SELECT ... FOR UPDATE to lock the wallet row,
     * preventing race conditions (double-spend from concurrent requests).
     */
    public function createOrder(array $data)
    {
        $userId = $data['user_id'];
        $paymentMethod = $data['payment_method'] ?? null;
        $items = $data['items'] ?? [];

        // ─── SERVER-SIDE PRICE RECALCULATION ───────────────────────────────
        $recalculated = $this->recalculateOrderTotal($items, (float) ($data['delivery_fee'] ?? 0), (float) ($data['discount'] ?? 0));
        $data['subtotal'] = $recalculated['subtotal'];
        $data['total'] = $recalculated['total'];
        $total = $recalculated['total'];

        // ─── NON-WALLET PAYMENT: Just create the order ──────────────────────
        if ($paymentMethod !== 'wallet' || $total <= 0) {
            $order = $this->orderRepository->create($data);
            $this->awardLoyaltyPointsForOrder($userId, $items);
            return $order;
        }

        // ─── WALLET PAYMENT: Atomic debit + order creation ──────────────────
        return DB::transaction(function () use ($data, $userId, $total) {

            // Lock the wallet row to prevent race conditions (double-spend)
            $wallet = DB::table('lesh_wallets')
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$wallet) {
                throw new \Exception('Wallet not found.');
            }

            if ((float) $wallet->balance < $total) {
                throw new \Exception('Insufficient wallet balance.');
            }

            // Generate order number
            $orderNumber = $data['order_number'] ?? 'ORD-' . strtoupper(uniqid());
            $data['order_number'] = $orderNumber;

            // Debit the wallet (within the transaction)
            $this->walletRepository->debit(
                $userId,
                $total,
                "Payment for order {$orderNumber}"
            );

            // Mark order as paid
            $data['status'] = 'Paid';
            $data['current_step'] = 'queue';
            $data['paid_at'] = now();

            // Create the order
            $order = $this->orderRepository->create($data);

            // Award loyalty points
            $this->awardLoyaltyPointsForOrder($userId, $items);

            Log::info('[Order] Wallet payment successful', [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'amount' => $total,
                'new_balance' => (float) $wallet->balance - $total,
            ]);

            return $order;
        });
    }

    /**
     * Award loyalty points for each product in the order.
     * Uses the product's `loyalty_points` column (set by admin).
     */
    private function awardLoyaltyPointsForOrder(int $userId, array $items): void
    {
        if (empty($items)) return;

        $productIds = array_filter(array_column($items, 'product_id'));
        if (empty($productIds)) return;

        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $totalPoints = 0;
        foreach ($items as $item) {
            $product = $products->get($item['product_id'] ?? null);
            if (!$product || $product->loyalty_points <= 0) continue;

            $quantity = (int) ($item['quantity'] ?? 1);
            $totalPoints += $product->loyalty_points * $quantity;
        }

        if ($totalPoints <= 0) return;

        // Award points
        $leshPoints = LeshPoints::firstOrCreate(['user_id' => $userId], ['balance' => 0, 'is_active' => true]);
        $leshPoints->earn($totalPoints, "Order purchase reward (+{$totalPoints} pts)");

        Log::info("[Loyalty] Points awarded for order", [
            'user_id' => $userId,
            'points' => $totalPoints,
        ]);
    }

    /**
     * Recalculate order subtotal and total from actual DB product prices.
     * 
     * This prevents price manipulation — even if the client sends a tampered total,
     * we always compute from the source-of-truth (products table + customization options).
     */
    private function recalculateOrderTotal(array $items, float $deliveryFee = 0, float $discount = 0): array
    {
        if (empty($items)) {
            return ['subtotal' => 0, 'delivery_fee' => $deliveryFee, 'discount' => $discount, 'total' => 0];
        }

        // Fetch all referenced products in one query
        $productIds = array_filter(array_column($items, 'product_id'));
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        // Fetch all customizations for those products
        $customizations = ProductCustomization::whereIn('product_id', $productIds)->get()->keyBy('product_id');

        $subtotal = 0;

        foreach ($items as $item) {
            $productId = $item['product_id'] ?? null;
            $quantity = (int) ($item['quantity'] ?? 1);

            $product = $products->get($productId);
            if (!$product) {
                Log::warning("[Order] Product not found during recalculation", ['product_id' => $productId]);
                continue;
            }

            // Base price from DB (source of truth)
            $unitPrice = (float) $product->price;

            // Add customization option prices from DB
            $customization = $item['customization'] ?? null;
            if ($customization && isset($customization['selections'])) {
                $productCustomization = $customizations->get($productId);
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

            $subtotal += $unitPrice * $quantity;
        }

        $total = max(0, round($subtotal + $deliveryFee - $discount, 2));

        return [
            'subtotal' => round($subtotal, 2),
            'delivery_fee' => $deliveryFee,
            'discount' => $discount,
            'total' => $total,
        ];
    }
}
