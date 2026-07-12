<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStampProgress extends Model
{
    protected $table = 'user_stamp_progress';

    protected $fillable = [
        'user_id',
        'stamp_quota_category_id',
        'stamp_quota_requirement_id',
        'current_count',
        'is_completed',
        'completed_at',
    ];

    protected $casts = [
        'current_count' => 'integer',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quotaCategory()
    {
        return $this->belongsTo(StampQuotaCategory::class, 'stamp_quota_category_id');
    }

    public function requirement()
    {
        return $this->belongsTo(StampQuotaRequirement::class, 'stamp_quota_requirement_id');
    }
}
