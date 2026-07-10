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
     * Get all user vouchers
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $vouchers = UserVoucher::where('user_id', $userId)
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
     * Get claimed vouchers for the user
     */
    public function claimed(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $claimedVouchers = UserVoucher::where('user_id', $userId)
            ->with('voucher')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $claimedVouchers,
        ]);
    }

    /**
     * Claim a voucher for the user
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

        // Create the user voucher (claim it)
        $userVoucher = UserVoucher::create([
            'user_id' => $userId,
            'voucher_id' => $voucher->id,
            'code' => $voucher->code,
            'description' => $voucher->label,
            'expires_at' => now()->addDays(30),
            'is_used' => false,
        ]);

        $userVoucher->load('voucher');

        return response()->json([
            'success' => true,
            'message' => 'Voucher claimed successfully!',
            'data' => $userVoucher,
        ]);
    }
}
