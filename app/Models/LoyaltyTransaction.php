<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoyaltyTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'lesh_points_id',
        'type',
        'points',
        'description',
        'transaction_date',
    ];

    protected $casts = [
        'points' => 'integer',
        'transaction_date' => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function leshPoints()
    {
        return $this->belongsTo(LeshPoints::class);
    }
}
