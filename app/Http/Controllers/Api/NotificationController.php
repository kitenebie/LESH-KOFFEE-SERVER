<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NotificationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notifications = $this->notificationService->getUserNotifications($userId);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $notification = $this->notificationService->markAsRead($id);

        return response()->json([
            'success' => true,
            'data' => $notification,
            'message' => 'Notification marked as read.',
        ]);
    }
}
