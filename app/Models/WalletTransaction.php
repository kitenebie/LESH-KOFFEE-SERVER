<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'amount',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function wallet()
    {
        return $this->belongsTo(LeshWallet::class, 'wallet_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
