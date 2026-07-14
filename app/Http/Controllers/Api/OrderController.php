<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $orders = $this->orderService->getUserOrders($userId);

        return response()->json([
            'success' => true,
            'data' => $orders,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = array_merge($request->all(), ['user_id' => $userId]);

        try {
            $order = $this->orderService->createOrder($data);

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Order created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Admin-only: Create an order on behalf of a walk-in customer.
     * Only super-admin can use this endpoint.
     */
    public function adminCreate(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user || !$user->hasRole('super_admin')) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'customer_name' => 'nullable|string|max:255',
            'table_no' => 'nullable|string|max:10',
            'type' => 'required|string|in:dine-in,takeout,delivery',
            'payment_method' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.customization' => 'nullable|array',
            'subtotal' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ]);

        try {
            $data = array_merge($validated, [
                'user_id' => $user->id,
                'is_admin_order' => true,
                'fulfillment' => 'DineIn',
                'ref_no' => $validated['table_no'] ?? null,
            ]);

            $order = $this->orderService->createOrder($data);

            return response()->json([
                'success' => true,
                'data' => $order,
                'message' => 'Admin order created successfully.',
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/orders/{orderNumber}/mark-paid
     * Called by frontend when online payment WebView returns success.
     * Only updates if order belongs to the user and hasn't been paid yet.
     */
    public function markPaid(string $orderNumber): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);

        $order = \App\Models\Order::where('order_number', $orderNumber)
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Only mark as paid if not already paid/completed (idempotent)
        if (!in_array($order->status, ['Paid', 'Preparing', 'Ready', 'Completed'])) {
            $order->update([
                'status' => 'Paid',
                'current_step' => 'queue',
                'paid_at' => $order->paid_at ?? now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $order->fresh(),
        ]);
    }
}
