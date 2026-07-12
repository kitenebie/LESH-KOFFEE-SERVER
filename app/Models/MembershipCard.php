<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipCard extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'card_tier',
        'card_number',
        'card_exp',
        'card_cvv',
        'is_active',
    ];

    protected $hidden = [
        'card_cvv',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────

    /**
     * Generate a unique 16-digit membership card number (formatted XXXX-XXXX-XXXX-XXXX).
     */
    public static function generateCardNumber(): string
    {
        do {
            $digits = '';
            for ($i = 0; $i < 16; $i++) {
                $digits .= random_int(0, 9);
            }
            $formatted = implode('-', str_split($digits, 4));
        } while (self::where('card_number', $formatted)->exists());

        return $formatted;
    }

    /**
     * Create a membership card for a user with default Bronze tier.
     */
    public static function createForUser(User $user): self
    {
        $rawCvv = str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        $card = self::create([
            'user_id' => $user->id,
            'card_tier' => 'Bronze',
            'card_number' => self::generateCardNumber(),
            'card_exp' => now()->addYears(5)->format('m/y'),
            'card_cvv' => bcrypt($rawCvv),
            'is_active' => true,
        ]);

        // Store raw CVV temporarily so it can be returned ONCE to user
        $card->raw_cvv = $rawCvv;

        return $card;
    }

    /**
     * Upgrade the card tier.
     */
    public function upgradeTier(string $newTier): void
    {
        $validTiers = ['Bronze', 'Silver', 'Gold', 'Platinum', 'Diamond'];
        if (in_array($newTier, $validTiers)) {
            $this->update(['card_tier' => $newTier]);
        }
    }

    /**
     * Get the display label for the tier.
     */
    public function getTierLabelAttribute(): string
    {
        return "Lesh Kaffe {$this->card_tier} Member";
    }
}
