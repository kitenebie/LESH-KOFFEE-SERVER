<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    public function __construct(
        protected WalletService $walletService
    ) {}

    /**
     * GET /api/wallet
     * 
     * Returns the authenticated user's wallet balance and transaction history.
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $data = $this->walletService->getWallet($userId);
        $wallet = $data['wallet'];
        $transactions = $data['transactions'];

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $wallet?->id,
                'user_id' => $wallet?->user_id,
                'balance' => $wallet?->balance ?? 0,
                'currency' => $wallet?->currency ?? 'PHP',
                'is_active' => $wallet?->is_active ?? true,
                'transactions' => $transactions->map(function ($t) {
                    return [
                        'id' => $t->id,
                        'type' => $t->type,
                        'amount' => $t->amount,
                        'description' => $t->description,
                        'transaction_date' => $t->transaction_date?->format('m/d/Y'),
                        'created_at' => $t->created_at,
                    ];
                }),
            ],
        ]);
    }

    /**
     * POST /api/wallet/debit
     * 
     * Debit from user's wallet for a purchase.
     * Protected by auth:sanctum + rate limiting.
     */
    public function debit(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $transaction = $this->walletService->debit(
                $userId,
                (float) $request->input('amount'),
                $request->input('description', 'Payment')
            );

            // Return updated wallet
            $data = $this->walletService->getWallet($userId);
            $wallet = $data['wallet'];

            return response()->json([
                'success' => true,
                'data' => [
                    'balance' => $wallet?->balance ?? 0,
                    'transaction' => $transaction,
                ],
                'message' => 'Debit successful.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    // NOTE: The topUp() method has been REMOVED from this controller.
    //
    // Wallet top-ups are now ONLY processed via the BUX.ph payment webhook
    // (PaymentController@webhook) after a verified payment is confirmed.
    //
    // This prevents the security vulnerability where anyone could call
    // POST /api/wallet/topup with any amount and any user ID.
    // ──────────────────────────────────────────────────────────────────────────
}
