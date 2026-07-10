<?php

namespace App\Repositories\Interfaces;

interface RatingRepositoryInterface
{
    public function create(array $data);

    public function getByProduct(int $productId);

    public function getByOrder(int $orderId);

    public function getByUserAndOrder(int $userId, int $orderId);

    public function getAverageForProduct(int $productId): array;
}
