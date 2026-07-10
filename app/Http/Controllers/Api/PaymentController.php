<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Order;
use App\Models\LeshWallet;
use App\Models\Subscription;

class PaymentController extends Controller
{
    /**
     * POST /api/payments/checkout
     * 
     * Generate a BUX.ph checkout link for online payment
     */
    public function checkout(Request $request): JsonResponse
    {
        Auth::login(User::find(1));
        $userId = Auth::id();

        if (!$userId) {
            \Log::error('[Payment] Checkout failed: Unauthorized. No authenticated user.');
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        try {
            $request->validate([
                'amount' => 'required|numeric|min:1',
                'description' => 'nullable|string|max:255',
                'email' => 'nullable|email',
                'contact' => 'nullable|string|max:30',
                'name' => 'nullable|string|max:100',
                'order_id' => 'nullable|string',
                'channels' => 'nullable|array',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('[Payment] Checkout validation failed', [
                'user_id' => $userId,
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            throw $e;
        }

        $amount = $request->input('amount');
        $description = $request->input('description', 'Lesh Kaffe Order Payment');
        $email = $request->input('email', Auth::user()->email ?? 'customer@leshkaffe.com');
        $rawContact = $request->input('contact', Auth::user()->phone ?? '9161234567');
        $contact = preg_replace('/[^0-9]/', '', $rawContact); // Strip all non-digits
        // Ensure 10 digits (remove country code 63 if present)
        if (strlen($contact) > 10 && str_starts_with($contact, '63')) {
            $contact = substr($contact, 2);
        }
        $name = $request->input('name', Auth::user()->name ?? 'Customer');
        $orderId = $request->input('order_id', 'LK-' . strtoupper(Str::random(6)));

        // Generate unique request ID
        $reqId = 'LESH_' . $userId . '_' . time() . '_' . Str::random(4);

        // Default payment channels
        $enabledChannels = $request->input('channels', [
            '711_direct',
            'grabpay',
            'gcash',
            'maya',
        ]);

        $buxApiUrl = config('bux.api_url');
        $buxApiKey = config('bux.api_key');
        $buxAuth = config('bux.auth');
        $clientId = config('bux.client_id');

        // Validate env vars are set
        if (!$buxApiKey || !$buxAuth || !$clientId) {
            \Log::error('[Payment] BUX credentials missing in .env', [
                'has_key' => !!$buxApiKey, 'has_auth' => !!$buxAuth, 'has_client' => !!$clientId
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Payment gateway not configured. Contact support.',
            ], 500);
        }

        // Notification URL (webhook for payment status)
        $notificationUrl = 'http://s1102464823.onlinehome.us/api/payments/webhook';
        $redirectUrl = 'http://s1102464823.onlinehome.us/api/payments/success';


        try {
            $dataCheckout = [
                'req_id' => $reqId,
                'client_id' => $clientId,
                'amount' => (string) $amount,
                'description' => $description,
                'expiry' => 3,
                'email' => $email,
                'contact' => $contact,
                'name' => $name,
                'notification_url' => $notificationUrl,
                'redirect_url' => $redirectUrl,
                'param1' => $orderId,
                'param2' => Auth::user()->name,
            ];
            $response = Http::withHeaders([
                'x-api-key' => $buxApiKey,
                'Authorization' => $buxAuth,
                'Content-Type' => 'application/json',
            ])->post($buxApiUrl, $dataCheckout);

            if ($response->successful()) {
                $data = $response->json();

                return response()->json([
                    'success' => true,
                    'data' => [
                        'checkout_url' => $data['checkout_url'] ?? $data['url'] ?? null,
                        'req_id' => $reqId,
                        'order_id' => $orderId,
                        'amount' => $amount,
                        'channels' => $enabledChannels,
                        'expires_in_minutes' => 60,
                        'raw_response' => $data,
                    ],
                    'message' => 'Checkout link generated successfully.',
                ]);
            }

            $responseBody = $response->json() ?? $response->body();
            \Log::error('[Payment] BUX API returned error', [
                'status' => $response->status(),
                'request_body' => $dataCheckout,
                'response_body' => $responseBody,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment gateway error: ' . ($responseBody['message'] ?? $response->body() ?? 'Unknown API error'),
                'bux_status' => $response->status(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('[Payment] Exception during checkout process', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'req_id' => $reqId,
                'order_id' => $orderId,
                'user_id' => $userId,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment service error: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * POST /api/payments/webhook
     * 
     * BUX.ph payment notification webhook
     */
    public function webhook(Request $request): JsonResponse
    {
        $data = $request->all();

        \Log::info('[BUX Webhook] Payment notification received', $data);

        // Extract payment info
        $extras = $data['extras'] ?? [];
        $reqId = $data['req_id'] ?? null;
        $status = $data['status'] ?? null;
        $refCode = $data['ref_code'] ?? null;
        $signature = $data['signature'] ?? null;
        $amount = $data['amount'] ?? null;
        $fee = $extras['fee'] ?? null;
        $param1 = $extras['param1'] ?? null;   // LK-xxxxx | TOPUP-userId-amount | SUB-userId-subId
        $param2 = $extras['param2'] ?? null;   // customer name

        if (!$reqId || !$status || !$param1) {
            \Log::warning('[BUX Webhook] Missing required fields in webhook payload', $data);
            return response()->json(['success' => false, 'message' => 'Missing required fields'], 400);
        }

        try {
            $normalizedStatus = strtolower($status);

            if ($normalizedStatus === 'paid' || $normalizedStatus === 'completed') {

                // ─── WALLET TOP-UP ───
                if (str_starts_with($param1, 'TOPUP-')) {
                    // param1 = TOPUP-{userId}-{amount}
                    $parts = explode('-', $param1);
                    $userId = (int) ($parts[1] ?? 0);
                    $topUpAmount = (float) ($parts[2] ?? 0);

                    if ($userId && $topUpAmount > 0) {
                        $wallet = LeshWallet::firstOrCreate(['user_id' => $userId], ['balance' => 0]);
                        $wallet->credit($topUpAmount, "Online Top-Up (Ref: {$refCode})");

                        \Log::info("[BUX Webhook] Wallet top-up successful. userId: {$userId}, amount: {$topUpAmount}, ref: {$refCode}");
                    }

                // ─── SUBSCRIPTION PURCHASE ───
                } elseif (str_starts_with($param1, 'SUB-')) {
                    // param1 = SUB-{userId}-{subscriptionId}
                    $parts = explode('-', $param1);
                    $userId = (int) ($parts[1] ?? 0);
                    $subscriptionId = (int) ($parts[2] ?? 0);

                    if ($userId && $subscriptionId) {
                        User::where('id', $userId)->update(['active_subscription_id' => $subscriptionId]);
                        \Log::info("[BUX Webhook] Subscription activated. userId: {$userId}, subscriptionId: {$subscriptionId}, ref: {$refCode}");
                    }

                // ─── ORDER PAYMENT ───
                } else {
                    $updated = Order::where('order_number', $param1)->update([
                        'req_id' => $reqId,
                        'ref_code' => $refCode,
                        'signature' => $signature,
                        'amount_paid' => $amount,
                        'payment_fee' => $fee,
                        'status' => 'Paid',
                        'current_step' => 'queue',
                        'paid_at' => now(),
                    ]);

                    if ($updated === 0) {
                        \Log::warning("[BUX Webhook] Order not found for update. orderId: {$param1}");
                    } else {
                        \Log::info("[BUX Webhook] Payment confirmed for order: {$param1}, customer: {$param2}, amount: {$amount}");
                    }
                }

            } else {
                \Log::warning("[BUX Webhook] Payment notification received with non-successful status: {$status} for param1: {$param1}");
            }
        } catch (\Exception $e) {
            \Log::error('[BUX Webhook] Exception while processing payment webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $data,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error processing webhook: ' . $e->getMessage()
            ], 500);
        }

        return response()->json(['success' => true, 'message' => 'Webhook received']);
    }

    /**
     * GET /api/payments/success
     * 
     * Redirect page after successful payment
     */
    public function success(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Payment completed successfully!',
            'data' => $request->all(),
        ]);
    }

    /**
     * GET /api/payments/status/{reqId}
     * 
     * Check payment status
     */
    public function status(string $reqId): JsonResponse
    {
        // For now return pending — integrate with BUX status check API later
        return response()->json([
            'success' => true,
            'data' => [
                'req_id' => $reqId,
                'status' => 'pending',
            ],
        ]);
    }
}
