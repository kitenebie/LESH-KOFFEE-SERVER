<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use App\Models\Subscription;
use App\Models\UserSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Create user subscription
        $userSub = UserSubscription::subscribe($userId, $plan);

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
}
