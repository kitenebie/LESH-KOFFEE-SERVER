<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryTracking extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'rider_name',
        'rider_phone',
        'rider_avatar',
        'rider_latitude',
        'rider_longitude',
        'user_latitude',
        'user_longitude',
        'estimated_minutes',
    ];

    protected $casts = [
        'rider_latitude' => 'decimal:8',
        'rider_longitude' => 'decimal:8',
        'user_latitude' => 'decimal:8',
        'user_longitude' => 'decimal:8',
        'estimated_minutes' => 'integer',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
