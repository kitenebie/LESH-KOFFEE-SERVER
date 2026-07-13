<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ) {}

    public function profile(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $user = $this->userService->getProfile($userId);

        // Override the role column with the actual Spatie role
        $userData = $user->toArray();
        $userData['role'] = $user->roles->pluck('name')->first() ?? 'user';

        return response()->json([
            'success' => true,
            'data' => $userData,
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'name' => 'nullable|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|string',
        ]);

        $user = $this->userService->updateProfile($userId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $user,
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function addresses(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $addresses = $this->userService->getAddresses($userId);

        return response()->json([
            'success' => true,
            'data' => $addresses,
        ]);
    }

    public function addAddress(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'label' => 'required|string|max:50',
            'address' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'is_default' => 'nullable|boolean',
        ]);

        $address = $this->userService->addAddress($userId, $request->all());

        return response()->json([
            'success' => true,
            'data' => $address,
            'message' => 'Address added successfully.',
        ], 201);
    }
}
