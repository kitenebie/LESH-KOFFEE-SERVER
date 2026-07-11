<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\OtpVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * POST /api/auth/login
     * 
     * Authenticates user and returns a Sanctum Bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::with('leshPoints')->where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // Revoke existing tokens for this device (optional: keeps only 1 active session)
        $user->tokens()->where('name', 'mobile-app')->delete();

        // Create new Sanctum token
        $token = $user->createToken('mobile-app', ['*']);

        Log::info('User logged in.', ['user_id' => $user->id]);

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
                'token' => $token->plainTextToken,
            ],
        ]);
    }

    /**
     * POST /api/auth/register
     * 
     * Creates a new user account and returns a Sanctum Bearer token.
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

        // Verify that the phone number was OTP-verified
        $phone = $this->normalizePhone($request->input('phone'));
        $otpRecord = OtpVerification::where('phone', $phone)
            ->where('is_verified', true)
            ->where('expires_at', '>', now()->subMinutes(10)) // verified within last 10 min
            ->latest()
            ->first();

        if (!$otpRecord) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number not verified. Please complete OTP verification first.',
            ], 422);
        }

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

        // Create Sanctum token for the newly registered user
        $token = $user->createToken('mobile-app', ['*']);

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
                'token' => $token->plainTextToken,
            ],
        ], 201);
    }

    /**
     * POST /api/auth/logout
     * 
     * Revokes the current access token (requires auth:sanctum).
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the token used to authenticate this request
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logged out successfully.',
        ]);
    }

    /**
     * POST /api/auth/resume
     * 
     * Resume session using existing Sanctum token.
     * Called on app startup to validate the stored token and get fresh user data.
     * Requires auth:sanctum middleware (token in Authorization header).
     */
    public function resume(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired token.',
            ], 401);
        }

        $user->load('leshPoints');

        return response()->json([
            'success' => true,
            'message' => 'Session resumed.',
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

    // ─── Private Helpers ─────────────────────────────────────────────────────────

    /**
     * Normalize phone number to +63XXXXXXXXXX format.
     */
    private function normalizePhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);
        $digits = ltrim($cleaned, '+');

        if (str_starts_with($digits, '0')) {
            $digits = '63' . substr($digits, 1);
        }

        if (!str_starts_with($digits, '63')) {
            $digits = '63' . $digits;
        }

        return '+' . $digits;
    }
}
