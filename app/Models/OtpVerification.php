<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class OtpVerification extends Model
{
    protected $fillable = [
        'phone',
        'otp_code',
        'expires_at',
        'is_used',
        'is_verified',
        'attempts',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_used' => 'boolean',
        'is_verified' => 'boolean',
        'attempts' => 'integer',
    ];

    // ─── Constants ───────────────────────────────────────────────────────────────

    const OTP_EXPIRY_MINUTES = 3;
    const MAX_ATTEMPTS = 5;
    const RESEND_COOLDOWN_SECONDS = 60;

    // ─── Scopes ──────────────────────────────────────────────────────────────────

    /**
     * Get the latest valid (unused, unexpired) OTP for a phone number.
     */
    public function scopeValidForPhone($query, string $phone)
    {
        return $query->where('phone', $phone)
            ->where('is_used', false)
            ->where('is_verified', false)
            ->where('expires_at', '>', now())
            ->where('attempts', '<', self::MAX_ATTEMPTS)
            ->latest();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Check if the OTP has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if max attempts exceeded.
     */
    public function isLocked(): bool
    {
        return $this->attempts >= self::MAX_ATTEMPTS;
    }

    /**
     * Check if a resend is allowed (cooldown period).
     */
    public static function canResend(string $phone): bool
    {
        $latest = self::where('phone', $phone)
            ->latest()
            ->first();

        if (!$latest) return true;

        return $latest->created_at->diffInSeconds(now()) >= self::RESEND_COOLDOWN_SECONDS;
    }

    /**
     * Generate a new OTP for a phone number.
     * Invalidates any existing unused OTPs for the same phone.
     */
    public static function generate(string $phone): self
    {
        // Invalidate all previous unused OTPs for this phone
        self::where('phone', $phone)
            ->where('is_used', false)
            ->where('is_verified', false)
            ->update(['is_used' => true]);

        // Create fresh OTP
        return self::create([
            'phone' => $phone,
            'otp_code' => str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT),
            'expires_at' => now()->addMinutes(self::OTP_EXPIRY_MINUTES),
        ]);
    }

    /**
     * Verify an OTP code for a phone number.
     * Returns ['success' => bool, 'message' => string]
     */
    public static function verify(string $phone, string $code): array
    {
        $otp = self::validForPhone($phone)->first();

        if (!$otp) {
            return ['success' => false, 'message' => 'No valid OTP found. Please request a new one.'];
        }

        if ($otp->isExpired()) {
            $otp->update(['is_used' => true]);
            return ['success' => false, 'message' => 'OTP has expired. Please request a new one.'];
        }

        if ($otp->isLocked()) {
            $otp->update(['is_used' => true]);
            return ['success' => false, 'message' => 'Too many failed attempts. Please request a new OTP.'];
        }

        if ($otp->otp_code !== $code) {
            $otp->increment('attempts');
            $remaining = self::MAX_ATTEMPTS - $otp->attempts;
            return ['success' => false, 'message' => "Incorrect code. {$remaining} attempt(s) remaining."];
        }

        // Mark as verified
        $otp->update(['is_verified' => true, 'is_used' => true]);

        return ['success' => true, 'message' => 'OTP verified successfully.'];
    }
}
