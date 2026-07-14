<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Order extends Model
{
    use HasFactory;

    // ─── Auto-award stamps & loyalty when order is marked Completed ────────

    protected static function booted(): void
    {
        static::updating(function (Order $order) {
            // Only trigger when status changes TO 'Completed'
            if ($order->isDirty('status') && $order->status === 'Completed') {
                $order->awardRewardsOnCompletion();
            }
        });
    }

    /**
     * Award loyalty points and stamp quota progress when order is completed.
     */
    private function awardRewardsOnCompletion(): void
    {
        $items = $this->items()->get()->map(fn($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
        ])->toArray();

        if (empty($items)) return;

        // Award loyalty points
        $this->awardLoyaltyPoints($items);

        // Award stamp quota progress
        (new \App\Services\StampQuotaService())->processOrderStamps($this->user_id, $items);

        Log::info('[Order] Rewards awarded on completion', [
            'order_id' => $this->id,
            'user_id' => $this->user_id,
            'items_count' => count($items),
        ]);
    }

    private function awardLoyaltyPoints(array $items): void
    {
        $productIds = array_column($items, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        $totalPoints = 0;
        foreach ($items as $item) {
            $product = $products->get($item['product_id']);
            if (!$product || ($product->loyalty_points ?? 0) <= 0) continue;
            $totalPoints += $product->loyalty_points * (int) ($item['quantity'] ?? 1);
        }

        if ($totalPoints <= 0) return;

        $leshPoints = LeshPoints::firstOrCreate(
            ['user_id' => $this->user_id],
            ['balance' => 0, 'is_active' => true]
        );
        $leshPoints->earn($totalPoints, "Order #{$this->order_number} completed (+{$totalPoints} pts)");
    }

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
        'subscription_discount',
        'voucher_discount',
        'perk_discount',
        'voucher_codes',
        'subscription_id',
        'subscription_items_used',
        'total',
    ];

    protected $casts = [
        'date' => 'date',
        'subtotal' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'discount' => 'decimal:2',
        'subscription_discount' => 'decimal:2',
        'voucher_discount' => 'decimal:2',
        'perk_discount' => 'decimal:2',
        'subscription_items_used' => 'integer',
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

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
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
