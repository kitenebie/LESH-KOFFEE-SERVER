<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Product;
use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderRepository implements OrderRepositoryInterface
{
    public function getByUser(int $userId)
    {
        return Order::with(['items.product'])
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getById(int $id)
    {
        return Order::with(['items.product', 'deliveryTracking'])->findOrFail($id);
    }

    public function create(array $data)
    {
        $orderData = [
            'user_id' => $data['user_id'],
            'order_number' => $data['order_number'] ?? 'ORD-' . strtoupper(uniqid()),
            'date' => now()->toDateString(),
            'time' => now()->format('H:i'),
            'status' => $data['status'] ?? 'pending',
            'current_step' => $data['current_step'] ?? 0,
            'fulfillment' => $data['fulfillment'] ?? 'delivery',
            'ref_no' => $data['ref_no'] ?? $data['table_no'] ?? null,
            'req_id' => $data['req_id'] ?? null,
            'cashier' => $data['cashier'] ?? null,
            'payment_method' => $data['payment_method'] ?? null,
            'paid_at' => $data['paid_at'] ?? null,
            'subtotal' => $data['subtotal'],
            'delivery_fee' => $data['delivery_fee'] ?? 0,
            'discount' => $data['discount'] ?? 0,
            'total' => $data['total'],
        ];

        // Add breakdown columns if they exist in the table (migration may not have run)
        try {
            $orderData['subscription_discount'] = $data['subscription_discount'] ?? 0;
            $orderData['voucher_discount'] = $data['voucher_discount'] ?? 0;
            $orderData['perk_discount'] = $data['perk_discount'] ?? 0;
            $orderData['voucher_codes'] = $data['voucher_codes'] ?? $data['voucherCode'] ?? null;
            $orderData['subscription_id'] = $data['subscription_id'] ?? null;
            $orderData['subscription_items_used'] = $data['subscription_items_used'] ?? 0;
        } catch (\Exception $e) {}

        try {
            $order = Order::create($orderData);
        } catch (\Illuminate\Database\QueryException $e) {
            // If columns don't exist yet, retry without breakdown fields
            unset($orderData['subscription_discount'], $orderData['voucher_discount'], $orderData['perk_discount'], $orderData['voucher_codes'], $orderData['subscription_id'], $orderData['subscription_items_used']);
            $order = Order::create($orderData);
        }

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                // Use server-verified price if available, otherwise look up from DB
                $product = Product::find($item['product_id']);
                $price = $item['price'] ?? $product?->price ?? 0;

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'name' => $item['name'] ?? $product?->name ?? 'Unknown',
                    'quantity' => $item['quantity'],
                    'price' => $price,
                    'customization' => $item['customization'] ?? null,
                ]);
            }
        }

        return $order->load('items.product');
    }
}
