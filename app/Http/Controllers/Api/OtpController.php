<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OtpVerification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class OtpController extends Controller
{
    /**
     * POST /api/otp/send
     * 
     * Send OTP to the given phone number.
     * Body: { phone: "+639171234567" }
     */
    public function send(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));

        // Check resend cooldown (60 seconds)
        if (!OtpVerification::canResend($phone)) {
            return response()->json([
                'success' => false,
                'message' => 'Please wait before requesting another code.',
            ], 429);
        }

        // Generate OTP (invalidates previous ones)
        $otp = OtpVerification::generate($phone);

        // Send via SMS
        $smsResult = $this->sendSMS($phone, $otp->otp_code);

        if (!$smsResult['success']) {
            Log::error('[OTP] SMS send failed', ['phone' => $phone, 'error' => $smsResult['message']]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send verification code. Please try again.',
            ], 500);
        }

        Log::info('[OTP] Code sent', ['phone' => $phone]);

        return response()->json([
            'success' => true,
            'message' => 'Verification code sent!',
            'data' => [
                'expires_in' => OtpVerification::OTP_EXPIRY_MINUTES * 60, // seconds
                'resend_cooldown' => OtpVerification::RESEND_COOLDOWN_SECONDS,
            ],
        ]);
    }

    /**
     * POST /api/otp/verify
     * 
     * Verify the OTP code.
     * Body: { phone: "+639171234567", code: "123456" }
     */
    public function verify(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string|min:10|max:20',
            'code' => 'required|string|size:6',
        ]);

        $phone = $this->normalizePhone($request->input('phone'));
        $code = $request->input('code');

        $result = OtpVerification::verify($phone, $code);

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }

    /**
     * POST /api/otp/resend
     * 
     * Resend a new OTP (same as send, just a semantic alias).
     */
    public function resend(Request $request): JsonResponse
    {
        return $this->send($request);
    }

    // ─── Private Helpers ─────────────────────────────────────────────────────────

    /**
     * Send SMS via UniSMS API.
     */
    private function sendSMS(string $to, string $otp): array
    {
        try {
            $client = new \GuzzleHttp\Client();

            $response = $client->post('https://unismsapi.com/api/sms', [
                'json' => [
                    'recipient' => $to,
                    'content' => "Lesh Kaffe: Your verification code is: " . $otp . ". Valid for 3 minutes. Do not share this code.",
                    'sender_id' => 'UNISOFT',
                ],
                'auth' => [
                    config('services.unisms.key'),
                    ''
                ],
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ]);

            $responseBody = $response->getBody()->getContents();

            Log::info('[OTP] UniSMS Response', [
                'response' => $responseBody,
                'to' => $to
            ]);

            return [
                'success' => true,
                'message' => $responseBody
            ];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $errorMessage = $e->hasResponse()
                ? $e->getResponse()->getBody()->getContents()
                : $e->getMessage();

            Log::error('[OTP] UniSMS Error', [
                'error' => $errorMessage,
                'to' => $to
            ]);

            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }
    }

    /**
     * Normalize phone number to international format.
     * Accepts: 09171234567, 9171234567, +639171234567, 639171234567
     * Returns: +639171234567
     */
    private function normalizePhone(string $phone): string
    {
        // Remove non-numeric except leading +
        $cleaned = preg_replace('/[^0-9+]/', '', $phone);

        // Remove leading + for processing
        $digits = ltrim($cleaned, '+');

        // If starts with 0, replace with 63
        if (str_starts_with($digits, '0')) {
            $digits = '63' . substr($digits, 1);
        }

        // If doesn't start with 63, prepend it
        if (!str_starts_with($digits, '63')) {
            $digits = '63' . $digits;
        }

        return '+' . $digits;
    }
}
