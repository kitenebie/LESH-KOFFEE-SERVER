<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;

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
}
