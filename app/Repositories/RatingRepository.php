<?php

namespace App\Repositories;

use App\Models\ProductRating;
use App\Repositories\Interfaces\RatingRepositoryInterface;

class RatingRepository implements RatingRepositoryInterface
{
    public function create(array $data)
    {
        return ProductRating::create($data);
    }

    public function getByProduct(int $productId)
    {
        return ProductRating::where('product_id', $productId)
            ->with('user:id,name,avatar')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function getByOrder(int $orderId)
    {
        return ProductRating::where('order_id', $orderId)->get();
    }

    public function getByUserAndOrder(int $userId, int $orderId)
    {
        return ProductRating::where('user_id', $userId)
            ->where('order_id', $orderId)
            ->get();
    }

    public function getAverageForProduct(int $productId): array
    {
        $ratings = ProductRating::where('product_id', $productId);

        return [
            'average' => round($ratings->avg('rating'), 1) ?: 0,
            'count' => $ratings->count(),
        ];
    }
}
