<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'price',
        'rating',
        'reviews',
        'is_popular',
        'is_customizable',
        'loyalty_points',
        'image',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'rating' => 'decimal:1',
        'reviews' => 'integer',
        'is_popular' => 'boolean',
        'is_customizable' => 'boolean',
        'loyalty_points' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function customization()
    {
        return $this->hasOne(ProductCustomization::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function stampHistories()
    {
        return $this->hasMany(StampHistory::class);
    }

    public function ratings()
    {
        return $this->hasMany(ProductRating::class);
    }
}
