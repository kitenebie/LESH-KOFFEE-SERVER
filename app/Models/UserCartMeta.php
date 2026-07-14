<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCartMeta extends Model
{
    use HasFactory;

    protected $table = 'user_cart_meta';

    protected $fillable = [
        'user_id',
        'fulfillment_mode',
        'applied_voucher_codes',
        'use_subscription',
        'subscription_items_to_use',
    ];

    protected $casts = [
        'applied_voucher_codes' => 'array',
        'use_subscription' => 'boolean',
        'subscription_items_to_use' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
