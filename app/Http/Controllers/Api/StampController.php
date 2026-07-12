<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StampService;
use App\Services\StampQuotaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class StampController extends Controller
{
    public function __construct(
        protected StampService $stampService
    ) {}

    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $achievements = $this->stampService->getAchievements($userId);

        return response()->json([
            'success' => true,
            'data' => $achievements,
        ]);
    }

    /**
     * GET /api/stamps/quota-progress
     * Returns the user's current tier and stamp progress toward next tier.
     */
    public function quotaProgress(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $service = new StampQuotaService();
        $progress = $service->getUserProgress($userId);

        return response()->json([
            'success' => true,
            'data' => $progress,
        ]);
    }
}
