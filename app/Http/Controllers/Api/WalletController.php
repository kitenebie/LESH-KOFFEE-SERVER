<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WalletService;
use App\Models\User;
use App\Models\LeshWallet;
use App\Models\WalletTransfer;
use App\Models\WalletTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $wallet = $this->walletService->getWallet($userId);

        return response()->json([
            'success' => true,
            'data' => $wallet,
        ]);
    }

    /**
     * POST /api/wallet/transfer
     * 
     * Securely transfer Lesh Money to another user by mobile number.
     * Uses DB transaction with row-level locking to prevent race conditions.
     * 
     * Body: { phone: "+639171234567", amount: 100, note?: "Coffee treat!" }
     */
    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
            'amount' => 'required|numeric|min:1|max:50000',
            'note' => 'nullable|string|max:255',
        ]);

        $senderId = Auth::id();
        if (!$senderId) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $recipientPhone = $this->normalizePhone($request->input('phone'));
        $amount = round((float) $request->input('amount'), 2);
        $note = $request->input('note', '');

        // ─── Find recipient by phone ─────────────────────────────────────────────
        $recipient = User::where('phone', $recipientPhone)->first();

        if (!$recipient) {
            return response()->json([
                'success' => false,
                'message' => 'Recipient not found. Make sure they have a Lesh Kaffe account with that number.',
            ], 404);
        }

        // Cannot send to yourself
        if ($recipient->id === $senderId) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot send money to yourself.',
            ], 422);
        }

        // ─── Atomic transfer with row locking ────────────────────────────────────
        try {
            $result = DB::transaction(function () use ($senderId, $recipient, $amount, $note) {
                // Lock sender wallet row to prevent double-spend
                $senderWallet = LeshWallet::where('user_id', $senderId)->lockForUpdate()->first();

                if (!$senderWallet) {
                    throw new \Exception('Your wallet is not set up. Please top up first.');
                }

                if ($senderWallet->balance < $amount) {
                    throw new \Exception('Insufficient balance.');
                }

                // Lock receiver wallet (or create if doesn't exist)
                $receiverWallet = LeshWallet::lockForUpdate()->firstOrCreate(
                    ['user_id' => $recipient->id],
                    ['balance' => 0]
                );

                // Debit sender
                $senderWallet->decrement('balance', $amount);

                // Credit receiver
                $receiverWallet->increment('balance', $amount);

                // Generate reference code
                $refCode = WalletTransfer::generateReferenceCode();

                // Record the transfer
                $transfer = WalletTransfer::create([
                    'reference_code' => $refCode,
                    'sender_id' => $senderId,
                    'receiver_id' => $recipient->id,
                    'amount' => $amount,
                    'note' => $note,
                    'status' => 'completed',
                ]);

                // Record transactions on both sides
                WalletTransaction::create([
                    'user_id' => $senderId,
                    'lesh_wallet_id' => $senderWallet->id,
                    'type' => 'transfer_out',
                    'amount' => $amount,
                    'description' => "Sent ₱{$amount} to " . ($recipient->name ?? $recipient->phone) . ($note ? " — {$note}" : ''),
                    'reference' => $refCode,
                ]);

                WalletTransaction::create([
                    'user_id' => $recipient->id,
                    'lesh_wallet_id' => $receiverWallet->id,
                    'type' => 'transfer_in',
                    'amount' => $amount,
                    'description' => "Received ₱{$amount} from " . (User::find($senderId)->name ?? 'a friend') . ($note ? " — {$note}" : ''),
                    'reference' => $refCode,
                ]);

                return [
                    'reference_code' => $refCode,
                    'new_balance' => $senderWallet->fresh()->balance,
                    'recipient_name' => $recipient->name,
                ];
            });

            Log::info('[Wallet] Transfer completed', [
                'sender_id' => $senderId,
                'receiver_id' => $recipient->id,
                'amount' => $amount,
                'ref' => $result['reference_code'],
            ]);

            return response()->json([
                'success' => true,
                'message' => "₱" . number_format($amount, 2) . " sent to {$result['recipient_name']}!",
                'data' => $result,
            ]);

        } catch (\Exception $e) {
            Log::warning('[Wallet] Transfer failed', [
                'sender_id' => $senderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

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
