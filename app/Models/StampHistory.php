<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'stamp_achievement_id',
        'product_id',
        'product_name',
        'stamped_date',
        'stamped_time',
    ];

    protected $casts = [
        'stamped_date' => 'date',
    ];

    public function stampAchievement()
    {
        return $this->belongsTo(StampAchievement::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
