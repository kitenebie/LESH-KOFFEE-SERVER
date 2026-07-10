<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Interfaces\RatingRepositoryInterface;

class RatingService
{
    public function __construct(
        protected RatingRepositoryInterface $ratingRepository
    ) {}

    /**
     * Submit rating for all products in an order.
     * Accepts: [ { product_id, rating, comment? }, ... ]
     */
    public function submitRatings(int $userId, int $orderId, array $ratings): array
    {
        $created = [];

        foreach ($ratings as $item) {
            $rating = $this->ratingRepository->create([
                'user_id' => $userId,
                'product_id' => $item['product_id'],
                'order_id' => $orderId,
                'rating' => $item['rating'],
                'comment' => $item['comment'] ?? null,
            ]);

            $created[] = $rating;

            // Recalculate product average rating
            $this->recalculateProductRating($item['product_id']);
        }

        return $created;
    }

    /**
     * Submit a single rating for an entire order (applied to all products in the order).
     */
    public function submitOrderRating(int $userId, int $orderId, int $rating, ?string $comment = null): array
    {
        // Get all product IDs from the order items
        $orderItems = OrderItem::where('order_id', $orderId)->get();

        if ($orderItems->isEmpty()) {
            return [];
        }

        $created = [];

        foreach ($orderItems as $item) {
            $ratingRecord = $this->ratingRepository->create([
                'user_id' => $userId,
                'product_id' => $item->product_id,
                'order_id' => $orderId,
                'rating' => $rating,
                'comment' => $comment,
            ]);

            $created[] = $ratingRecord;

            // Recalculate product average rating
            $this->recalculateProductRating($item->product_id);
        }

        return $created;
    }

    /**
     * Get all ratings for a product.
     */
    public function getProductRatings(int $productId)
    {
        return $this->ratingRepository->getByProduct($productId);
    }

    /**
     * Get ratings submitted for a specific order by the user.
     */
    public function getOrderRatings(int $userId, int $orderId)
    {
        return $this->ratingRepository->getByUserAndOrder($userId, $orderId);
    }

    /**
     * Check if user already rated this order.
     */
    public function hasRatedOrder(int $userId, int $orderId): bool
    {
        $ratings = $this->ratingRepository->getByUserAndOrder($userId, $orderId);
        return $ratings->isNotEmpty();
    }

    /**
     * Recalculate and update product's average rating and review count.
     */
    private function recalculateProductRating(int $productId): void
    {
        $stats = $this->ratingRepository->getAverageForProduct($productId);

        Product::where('id', $productId)->update([
            'rating' => $stats['average'],
            'reviews' => $stats['count'],
        ]);
    }
}
