<?php

namespace App\Repositories;

use App\Models\Order;
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
        $order = Order::create([
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
        ]);

        if (isset($data['items']) && is_array($data['items'])) {
            foreach ($data['items'] as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'customization' => $item['customization'] ?? null,
                ]);
            }
        }

        return $order->load('items.product');
    }
}
