<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'category',
        'icon',
        'color',
        'accent_color',
        'label',
        'description',
        'collected',
        'required',
        'reward',
    ];

    protected $casts = [
        'collected' => 'integer',
        'required' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function histories()
    {
        return $this->hasMany(StampHistory::class);
    }
}
