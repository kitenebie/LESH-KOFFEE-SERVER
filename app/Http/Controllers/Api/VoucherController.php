<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserVoucher;
use App\Models\Voucher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VoucherController extends Controller
{
    /**
     * Get all user vouchers (active only — not expired, not used)
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $vouchers = UserVoucher::where('user_id', $userId)
            ->active()
            ->with('voucher')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $vouchers,
        ]);
    }

    /**
     * Get unclaimed (available) vouchers for the user
     * These are active vouchers that the user hasn't claimed yet
     */
    public function unclaimed(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // Get voucher IDs the user has already claimed
        $claimedVoucherIds = UserVoucher::where('user_id', $userId)
            ->pluck('voucher_id')
            ->toArray();

        // Get active vouchers that haven't been claimed
        $unclaimedVouchers = Voucher::where('is_active', true)
            ->whereNotIn('id', $claimedVoucherIds)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $unclaimedVouchers,
        ]);
    }

    /**
     * Get claimed vouchers for the user (active only — not expired, not used)
     */
    public function claimed(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $claimedVouchers = UserVoucher::where('user_id', $userId)
            ->active()
            ->with('voucher')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $claimedVouchers,
        ]);
    }

    /**
     * Claim a voucher for the user (by ID)
     */
    public function claim(int $id): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $voucher = Voucher::where('id', $id)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Voucher not found or no longer active.',
            ], 404);
        }

        // Check if already claimed
        $alreadyClaimed = UserVoucher::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->exists();

        if ($alreadyClaimed) {
            return response()->json([
                'success' => false,
                'message' => 'You have already claimed this voucher.',
            ], 409);
        }

        // Calculate expiration
        $expiresAt = $voucher->valid_hours
            ? now()->addHours($voucher->valid_hours)
            : now()->addDays(30);

        // Create the user voucher (claim it)
        $userVoucher = UserVoucher::create([
            'user_id' => $userId,
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'description' => $voucher->label,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        $userVoucher->load('voucher');

        return response()->json([
            'success' => true,
            'message' => 'Voucher claimed successfully!',
            'data' => $userVoucher,
        ]);
    }

    /**
     * Claim a voucher by its code
     * POST /api/vouchers/claim-by-code
     * Body: { code: "VOUCHER_CODE" }
     */
    public function claimByCode(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'code' => 'required|string|max:50',
        ]);

        $code = strtoupper(trim($request->input('code')));

        // Find the voucher by code
        $voucher = Voucher::where('code', $code)
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid voucher code. Please check and try again.',
            ], 404);
        }

        // Check if already claimed
        $alreadyClaimed = UserVoucher::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->exists();

        if ($alreadyClaimed) {
            return response()->json([
                'success' => false,
                'message' => 'You have already claimed this voucher.',
            ], 409);
        }

        // Calculate expiration
        $expiresAt = $voucher->valid_hours
            ? now()->addHours($voucher->valid_hours)
            : now()->addDays(30);

        // Claim it
        $userVoucher = UserVoucher::create([
            'user_id' => $userId,
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'description' => $voucher->label,
            'expires_at' => $expiresAt,
            'is_used' => false,
        ]);

        $userVoucher->load('voucher');

        return response()->json([
            'success' => true,
            'message' => "Voucher \"{$voucher->label}\" claimed successfully!",
            'data' => $userVoucher,
            'voucher' => $voucher,
        ]);
    }
}
