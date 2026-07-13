<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use App\Models\Subscription;
use App\Models\Order;
use App\Models\LeshWallet;
use App\Models\UserSubscription;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubscriptionController extends Controller
{
    public function __construct(
        protected SubscriptionService $subscriptionService
    ) {}

    public function index(): JsonResponse
    {
        $subscriptions = $this->subscriptionService->getAllSubscriptions();

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }

    /**
     * POST /api/subscriptions/subscribe
     * 
     * User purchases a subscription plan.
     * Creates a UserSubscription with calculated expiry and drink allocation.
     */
    public function subscribe(Request $request): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'subscription_id' => 'required|integer|exists:subscriptions,id',
        ]);

        $plan = Subscription::available()->findOrFail($request->input('subscription_id'));

        // Check if user already has an active subscription for this plan
        $existing = UserSubscription::where('user_id', $userId)
            ->where('subscription_id', $plan->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'You already have an active subscription for this plan.',
            ], 422);
        }

        $amount = (float) $plan->price;

        try {
            $userSub = DB::transaction(function () use ($userId, $plan, $amount) {
                // Ensure wallet exists
                $wallet = LeshWallet::firstOrCreate(
                    ['user_id' => $userId],
                    ['balance' => 0, 'currency' => 'PHP', 'is_active' => true]
                );

                // 1. Credit — record payment received for subscription
                WalletTransaction::create([
                    'user_id' => $userId,
                    'wallet_id' => $wallet->id,
                    'type' => 'credit',
                    'amount' => $amount,
                    'description' => "Subscription payment: {$plan->name}",
                    'transaction_date' => now()->toDateString(),
                ]);

                // 2. Debit — record payment spent on subscription
                WalletTransaction::create([
                    'user_id' => $userId,
                    'wallet_id' => $wallet->id,
                    'type' => 'debit',
                    'amount' => $amount,
                    'description' => "Prepaid Subscription: {$plan->name} ({$plan->duration_days} days)",
                    'transaction_date' => now()->toDateString(),
                ]);

                // 3. Create UserSubscription record
                $userSub = UserSubscription::subscribe($userId, $plan);

                Log::info('[Subscription] User subscribed', [
                    'user_id' => $userId,
                    'plan' => $plan->name,
                    'amount' => $amount,
                    'user_subscription_id' => $userSub->id,
                ]);

                return $userSub;
            });

            return response()->json([
                'success' => true,
                'message' => "Subscribed to {$plan->name}! Valid for {$plan->duration_days} days.",
                'data' => [
                    'id' => $userSub->id,
                    'subscription_name' => $plan->name,
                    'starts_at' => $userSub->starts_at->format('Y-m-d'),
                    'expires_at' => $userSub->expires_at->format('Y-m-d'),
                    'drinks_remaining' => $userSub->drinks_remaining,
                    'drinks_per_week' => $plan->drinks_per_week,
                    'duration_days' => $plan->duration_days,
                    'status' => $userSub->status,
                ],
            ], 201);

        } catch (\Exception $e) {
            Log::error('[Subscription] Subscribe failed', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Subscription failed. Please try again.',
            ], 500);
        }
    }

    /**
     * GET /api/subscriptions/my
     * 
     * Get the user's active subscription(s).
     */
    public function mySubscriptions(): JsonResponse
    {
        $userId = Auth::id();
        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $subs = UserSubscription::with('subscription')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subs->map(fn($s) => [
                'id' => $s->id,
                'subscription_name' => $s->subscription->name ?? 'Unknown',
                'subscription_icon' => $s->subscription->icon ?? 'cafe-outline',
                'starts_at' => $s->starts_at->format('Y-m-d'),
                'expires_at' => $s->expires_at->format('Y-m-d'),
                'drinks_remaining' => $s->drinks_remaining,
                'drinks_used' => $s->drinks_used,
                'drinks_per_week' => $s->subscription->drinks_per_week ?? 0,
                'duration_days' => $s->subscription->duration_days ?? 30,
                'status' => $s->status,
                'is_expired' => $s->is_expired,
                'is_usable' => $s->is_usable,
            ]),
        ]);
    }

    /**
     * POST /api/subscriptions/redeem
     * 
     * Redeem a drink from a user's active subscription.
     * Called by the Filament QR scanner after scanning the user's QR code.
     */
    public function redeem(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'subscription_id' => 'required|integer|exists:subscriptions,id',
            'user_subscription_id' => 'required|integer|exists:user_subscriptions,id',
        ]);

        $userId = $request->input('user_id');
        $subscriptionId = $request->input('subscription_id');
        $userSubscriptionId = $request->input('user_subscription_id');

        // Find the UserSubscription record
        $userSub = UserSubscription::with('subscription')
            ->where('id', $userSubscriptionId)
            ->where('user_id', $userId)
            ->where('subscription_id', $subscriptionId)
            ->first();

        if (!$userSub) {
            return response()->json([
                'success' => false,
                'message' => 'Subscription record not found.',
            ], 404);
        }

        // Verify it's active and not expired
        if (!$userSub->is_usable) {
            $reason = $userSub->is_expired
                ? 'This subscription has expired.'
                : ($userSub->drinks_remaining <= 0
                    ? 'No drinks remaining on this subscription.'
                    : 'This subscription is not active.');

            return response()->json([
                'success' => false,
                'message' => $reason,
            ], 422);
        }

        // Decrement drinks_remaining
        $redeemed = $userSub->useDrink('Subscription drink redeemed via QR');

        if (!$redeemed) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to redeem drink. Please try again.',
            ], 422);
        }

        // Create an Order record
        $subscriptionName = $userSub->subscription->name ?? 'Subscription';
        $orderNumber = str_replace(' ', '', $subscriptionName) . '-' . now()->format('Ymd') . '-' . str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $order = Order::create([
            'user_id' => $userId,
            'order_number' => $orderNumber,
            'date' => now()->toDateString(),
            'time' => now()->format('H:i'),
            'status' => 'Completed',
            'total' => 0,
            'subtotal' => 0,
            'delivery_fee' => 0,
            'discount' => 0,
            'payment_method' => 'subscription',
        ]);

        // Create a WalletTransaction for audit trail
        $wallet = LeshWallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0, 'currency' => 'PHP', 'is_active' => true]
        );

        $wallet->transactions()->create([
            'user_id' => $userId,
            'type' => 'debit',
            'amount' => 0,
            'description' => "Subscription redemption: {$subscriptionName}",
            'transaction_date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Drink redeemed successfully! ({$userSub->drinks_remaining} remaining)",
            'data' => [
                'order_number' => $order->order_number,
                'subscription_name' => $subscriptionName,
                'drinks_remaining' => $userSub->drinks_remaining,
                'drinks_used' => $userSub->drinks_used,
            ],
        ]);
    }

    /**
     * GET /api/subscriptions/{id}/eligible-products
     * 
     * Returns the products a subscription can be redeemed for.
     * Based on redemption_type: 'all' = all products, 'category' = products in linked categories,
     * 'products' = only specific linked products.
     */
    public function eligibleProducts(int $id): JsonResponse
    {
        $subscription = Subscription::with(['eligibleCategories', 'eligibleProducts.customization'])
            ->find($id);

        if (!$subscription) {
            return response()->json(['success' => false, 'message' => 'Subscription not found'], 404);
        }

        $products = collect();

        switch ($subscription->redemption_type) {
            case 'products':
                // Only specific products linked to this subscription
                $products = $subscription->eligibleProducts;
                break;

            case 'category':
                // All products in the linked categories
                $categoryIds = $subscription->eligibleCategories->pluck('id');
                $products = Product::with('customization')
                    ->whereIn('category_id', $categoryIds)
                    ->where('is_active', true)
                    ->get();
                break;

            case 'all':
            default:
                // All active products
                $products = Product::with('customization')
                    ->where('is_active', true)
                    ->get();
                break;
        }

        return response()->json([
            'success' => true,
            'data' => $products,
            'redemption_type' => $subscription->redemption_type,
            'subscription_name' => $subscription->name,
        ]);
    }
}
