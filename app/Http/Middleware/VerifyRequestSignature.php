<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyRequestSignature
{
    /**
     * HMAC Request Signature Verification
     * 
     * Every request from the mobile app must include:
     * - X-Timestamp: Unix timestamp (seconds) — request must be within 5 minutes
     * - X-Signature: HMAC-SHA256 of "{timestamp}.{method}.{path}.{body}" using APP_SIGNING_KEY
     * 
     * This prevents:
     * - Request tampering (changing amount, user_id, etc.)
     * - Replay attacks (timestamp window)
     * - Modded apps without the signing key
     * 
     * NOTE: The signing key is obfuscated in the app binary, not in plain text.
     * It's not 100% uncrackable but raises the bar significantly.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $signingKey = config('app.signing_key');

        // If no signing key is configured, skip verification (dev mode)
        if (!$signingKey) {
            return $next($request);
        }

        $timestamp = $request->header('X-Timestamp');
        $signature = $request->header('X-Signature');

        // ─── Check required headers exist ────────────────────────────────────
        if (!$timestamp || !$signature) {
            \Log::warning('[SignatureVerification] Missing headers', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'has_timestamp' => !!$timestamp,
                'has_signature' => !!$signature,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Request signature required.',
            ], 403);
        }

        // ─── Check timestamp freshness (5 minute window) ────────────────────
        $now = time();
        $requestTime = (int) $timestamp;
        $drift = abs($now - $requestTime);

        if ($drift > 300) { // 5 minutes
            \Log::warning('[SignatureVerification] Timestamp expired', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'drift_seconds' => $drift,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Request expired. Please try again.',
            ], 403);
        }

        // ─── Reconstruct and verify signature ────────────────────────────────
        $method = strtoupper($request->method());
        $path = '/' . ltrim($request->path(), '/');
        $body = $request->getContent() ?: '';

        // Signature payload: "{timestamp}.{METHOD}.{/path}.{body}"
        $payload = "{$timestamp}.{$method}.{$path}.{$body}";
        $expectedSignature = hash_hmac('sha256', $payload, $signingKey);

        // Debug: log what the server reconstructs vs what the app sent
        if (!hash_equals($expectedSignature, $signature)) {
            \Log::debug('[SignatureVerification] Mismatch debug', [
                'server_payload' => "{$timestamp}.{$method}.{$path}." . (strlen($body) > 100 ? substr($body, 0, 100) . '...' : $body),
                'server_signature' => $expectedSignature,
                'client_signature' => $signature,
            ]);
        }

        if (!hash_equals($expectedSignature, $signature)) {
            \Log::warning('[SignatureVerification] Invalid signature', [
                'ip' => $request->ip(),
                'path' => $request->path(),
                'method' => $method,
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Invalid request signature.',
            ], 403);
        }

        return $next($request);
    }
}
