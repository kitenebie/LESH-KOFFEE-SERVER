<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransfer extends Model
{
    protected $fillable = [
        'reference_code',
        'sender_id',
        'receiver_id',
        'amount',
        'note',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Generate a unique reference code.
     */
    public static function generateReferenceCode(): string
    {
        do {
            $code = 'LT-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
        } while (self::where('reference_code', $code)->exists());

        return $code;
    }
}
