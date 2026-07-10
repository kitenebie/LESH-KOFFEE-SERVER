<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LeshPoints;
use App\Models\LoyaltyTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoyaltyController extends Controller
{
    /**
     * GET /api/loyalty/transactions
     * Returns transactions list
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $transactions = LoyaltyTransaction::where('user_id', $userId)
            ->orderBy('transaction_date', 'desc')
            ->get()
            ->map(fn($t) => [
                'id' => $t->id,
                'type' => $t->type,
                'points' => $t->points,
                'description' => $t->description,
                'transaction_date' => $t->transaction_date?->format('m/d/Y'),
                'created_at' => $t->created_at,
            ]);

        return response()->json([
            'success' => true,
            'data' => $transactions,
        ]);
    }

    /**
     * GET /api/loyalty/points
     * Returns LeshPoints balance + transactions
     */
    public function points(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $leshPoints = LeshPoints::with('transactions')
            ->where('user_id', $userId)
            ->first();

        if (!$leshPoints) {
            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => 0,
                    'is_active' => false,
                    'transactions' => [],
                ],
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $leshPoints->id,
                'user_id' => $leshPoints->user_id,
                'balance' => $leshPoints->balance,
                'is_active' => $leshPoints->is_active,
                'transactions' => $leshPoints->transactions->map(fn($t) => [
                    'id' => $t->id,
                    'type' => $t->type,
                    'points' => $t->points,
                    'description' => $t->description,
                    'transaction_date' => $t->transaction_date?->format('m/d/Y'),
                ]),
            ],
        ]);
    }

    /**
     * POST /api/loyalty/earn
     * Earn points (from order, activity, etc.)
     */
    public function earn(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'required|string',
        ]);

        $leshPoints = LeshPoints::where('user_id', $userId)->firstOrFail();
        $transaction = $leshPoints->earn(
            (int) $request->input('points'),
            $request->input('description')
        );

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $leshPoints->fresh()->balance,
                'transaction' => $transaction,
            ],
            'message' => 'Points earned!',
        ]);
    }

    /**
     * POST /api/loyalty/redeem
     * Redeem points
     */
    public function redeem(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'points' => 'required|integer|min:1',
            'description' => 'required|string',
        ]);

        $leshPoints = LeshPoints::where('user_id', $userId)->firstOrFail();

        try {
            $transaction = $leshPoints->redeem(
                (int) $request->input('points'),
                $request->input('description')
            );

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $leshPoints->fresh()->balance,
                    'transaction' => $transaction,
                ],
                'message' => 'Points redeemed!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * POST /api/loyalty/recalculate
     * Recalculate balance from transaction records
     */
    public function recalculate(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $leshPoints = LeshPoints::where('user_id', $userId)->firstOrFail();
        $newBalance = $leshPoints->recalculateBalance();

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => $newBalance,
            ],
            'message' => 'Balance recalculated from transaction records.',
        ]);
    }
}
