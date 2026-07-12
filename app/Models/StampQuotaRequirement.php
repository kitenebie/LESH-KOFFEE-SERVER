<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StampQuotaRequirement extends Model
{
    protected $fillable = [
        'stamp_quota_category_id',
        'category_id',
        'required_count',
        'points_per_stamp',
    ];

    protected $casts = [
        'required_count' => 'integer',
        'points_per_stamp' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────────

    public function quotaCategory()
    {
        return $this->belongsTo(StampQuotaCategory::class, 'stamp_quota_category_id');
    }

    public function productCategory()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function userProgress()
    {
        return $this->hasMany(UserStampProgress::class);
    }
}
