<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampQuotaCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'rank',
        'color',
        'icon',
        'reward_points',
        'description',
        'is_active',
    ];

    protected $casts = [
        'rank' => 'integer',
        'reward_points' => 'integer',
        'is_active' => 'boolean',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function requirements()
    {
        return $this->hasMany(StampQuotaRequirement::class);
    }

    public function users()
    {
        return $this->hasMany(User::class, 'stamp_quota_category_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────────

    /**
     * Get the next tier after this one.
     */
    public function getNextTier(): ?self
    {
        return self::where('is_active', true)
            ->where('rank', '>', $this->rank)
            ->orderBy('rank', 'asc')
            ->first();
    }

    /**
     * Get the first (lowest) tier.
     */
    public static function getStartingTier(): ?self
    {
        return self::where('is_active', true)
            ->orderBy('rank', 'asc')
            ->first();
    }
}
