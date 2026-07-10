<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\RatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function __construct(
        protected RatingService $ratingService
    ) {}

    /**
     * POST /api/ratings
     * 
     * Submit ratings for an order (by order_number).
     * Body: { order_id, ratings: [{ product_id, rating, comment? }] }
     */
    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'order_id' => 'required|string',
            'ratings' => 'required|array|min:1',
            'ratings.*.product_id' => 'required|integer|exists:products,id',
            'ratings.*.rating' => 'required|integer|min:1|max:5',
            'ratings.*.comment' => 'nullable|string|max:500',
        ]);

        // Resolve order by order_number
        $order = Order::where('order_number', $request->order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Check if already rated
        if ($this->ratingService->hasRatedOrder($userId, $order->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this order.',
            ], 422);
        }

        $ratings = $this->ratingService->submitRatings(
            $userId,
            $order->id,
            $request->ratings
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully.',
            'data' => $ratings,
        ], 201);
    }

    /**
     * GET /api/ratings/product/{id}
     * 
     * Get all ratings for a specific product.
     */
    public function productRatings(int $id): JsonResponse
    {
        $ratings = $this->ratingService->getProductRatings($id);

        return response()->json([
            'success' => true,
            'data' => $ratings,
        ]);
    }

    /**
     * GET /api/ratings/order/{orderId}
     * 
     * Get ratings submitted for a specific order by the authenticated user.
     */
    public function orderRatings(string $orderNumber): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        $ratings = $this->ratingService->getOrderRatings($userId, $order->id);

        return response()->json([
            'success' => true,
            'data' => $ratings,
        ]);
    }

    /**
     * POST /api/ratings/order
     * 
     * Submit a single rating for an entire order (applies to all products).
     * Body: { order_id (order_number string), rating (1-5), comment? }
     */
    public function rateOrder(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'order_id' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        // Resolve order by order_number
        $order = Order::where('order_number', $request->order_id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found.'], 404);
        }

        // Check if already rated
        if ($this->ratingService->hasRatedOrder($userId, $order->id)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already rated this order.',
            ], 422);
        }

        $ratings = $this->ratingService->submitOrderRating(
            $userId,
            $order->id,
            $request->rating,
            $request->comment
        );

        return response()->json([
            'success' => true,
            'message' => 'Rating submitted successfully. Thank you for your feedback!',
            'data' => $ratings,
        ], 201);
    }
}
