<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'drinks',
        'loyalty_points',
        'icon',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'drinks' => 'integer',
        'loyalty_points' => 'integer',
        'is_active' => 'boolean',
    ];

    public function subscribers()
    {
        return $this->hasMany(User::class, 'active_subscription_id');
    }
}
