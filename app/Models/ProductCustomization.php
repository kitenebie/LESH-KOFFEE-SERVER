<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCustomization extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'customizations',
    ];

    protected $casts = [
        'customizations' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
