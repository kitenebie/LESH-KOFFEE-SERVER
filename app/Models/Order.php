<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'order_number',
        'date',
        'time',
        'status',
        'current_step',
        'fulfillment',
        'ref_no',
        'req_id',
        'ref_code',
        'signature',
        'amount_paid',
        'payment_fee',
        'payment_method',
        'paid_at',
        'cashier',
        'subtotal',
        'delivery_fee',
        'discount',
        'total',
    ];

    protected $casts = [
        'date' => 'date',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'payment_fee' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function deliveryTracking()
    {
        return $this->hasOne(DeliveryTracking::class);
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }
}
