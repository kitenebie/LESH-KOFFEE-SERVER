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

    public function topUp(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
        ]);

        $transaction = $this->walletService->topUp(
            $userId,
            (float) $request->input('amount'),
            $request->input('description', 'Wallet Top-Up')
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
            'message' => 'Top-up successful.',
        ]);
    }

    public function debit(Request $request): JsonResponse
    {
        $userId = Auth::id();

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'nullable|string',
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
}
