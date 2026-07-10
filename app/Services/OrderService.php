<?php

namespace App\Services;

use App\Repositories\Interfaces\OrderRepositoryInterface;

class OrderService
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
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

    public function createOrder(array $data)
    {
        return $this->orderRepository->create($data);
    }
}
