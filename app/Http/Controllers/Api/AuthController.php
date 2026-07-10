<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        Auth::login($user, true);
        Log:info('User logged in.', ['user_id' => Auth::user()]);
        if (Auth::check()) {
            return response()->json([
                'success' => true,
                'message' => 'Login successful.',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'first_name' => $user->first_name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'avatar' => $user->avatar,
                        'member_level' => $user->member_level,
                        'member_level_label' => $user->member_level_label,
                        'wallet_balance' => $user->wallet_balance,
                        'loyalty_points' => $user->loyalty_points,
                        'stamps_collected' => $user->stamps_collected,
                        'stamps_required' => $user->stamps_required,
                        'joined_date' => $user->joined_date?->format('Y-m-d'),
                    ],
                ],
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Invalid email or password.',
        ], 401);
    }

    /**
     * POST /api/auth/register
     */
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'first_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'first_name' => $request->input('first_name', $request->input('name')),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'member_level' => 'Silver',
            'member_level_label' => 'Lesh Kaffe Silver Member',
            'wallet_balance' => 0,
            'loyalty_points' => 0,
            'stamps_collected' => 0,
            'stamps_required' => 8,
            'joined_date' => now()->toDateString(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful.',
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'first_name' => $user->first_name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'avatar' => $user->avatar,
                    'member_level' => $user->member_level,
                    'member_level_label' => $user->member_level_label,
                    'wallet_balance' => $user->wallet_balance,
                    'loyalty_points' => $user->loyalty_points,
                    'joined_date' => $user->joined_date?->format('Y-m-d'),
                ],
            ],
        ], 201);
    }

    /**
     * POST /api/auth/logout
     */
    public function logout(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }
}
