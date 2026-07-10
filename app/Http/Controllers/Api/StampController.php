<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\StampService;
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
}
