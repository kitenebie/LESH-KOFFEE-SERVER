<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeshPoints extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'balance',
        'is_active',
    ];

    protected $casts = [
        'balance' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(LoyaltyTransaction::class, 'lesh_points_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────────

    /**
     * Earn points (from orders, bonuses, etc.)
     */
    public function earn(int $points, string $description): LoyaltyTransaction
    {
        $this->increment('balance', $points);

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'earned',
            'points' => $points,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
        ]);
    }

    /**
     * Award bonus points
     */
    public function bonus(int $points, string $description): LoyaltyTransaction
    {
        $this->increment('balance', $points);

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'bonus',
            'points' => $points,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
        ]);
    }

    /**
     * Redeem points (deduct)
     */
    public function redeem(int $points, string $description): LoyaltyTransaction
    {
        if (!$this->hasSufficientBalance($points)) {
            throw new \Exception('Insufficient loyalty points.');
        }

        $this->decrement('balance', $points);

        return $this->transactions()->create([
            'user_id' => $this->user_id,
            'type' => 'redeemed',
            'points' => $points,
            'description' => $description,
            'transaction_date' => now()->toDateString(),
        ]);
    }

    /**
     * Check if user has enough points
     */
    public function hasSufficientBalance(int $points): bool
    {
        return $this->balance >= $points;
    }

    /**
     * Recalculate balance from transaction records (for accuracy)
     */
    public function recalculateBalance(): int
    {
        $earned = $this->transactions()
            ->whereIn('type', ['earned', 'bonus'])
            ->sum('points');

        $redeemed = $this->transactions()
            ->where('type', 'redeemed')
            ->sum('points');

        $correct = $earned - $redeemed;
        $this->update(['balance' => $correct]);

        return $correct;
    }
}
