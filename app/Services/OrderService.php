<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;
use App\Repositories\Interfaces\WalletRepositoryInterface;
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
        $total = (float) ($data['total'] ?? 0);
        $paymentMethod = $data['payment_method'] ?? null;

        // ─── NON-WALLET PAYMENT: Just create the order ──────────────────────
        if ($paymentMethod !== 'wallet' || $total <= 0) {
            return $this->orderRepository->create($data);
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

            Log::info('[Order] Wallet payment successful', [
                'user_id' => $userId,
                'order_number' => $orderNumber,
                'amount' => $total,
                'new_balance' => (float) $wallet->balance - $total,
            ]);

            return $order;
        });
    }
}
