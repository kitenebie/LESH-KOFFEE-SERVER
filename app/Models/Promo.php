<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'voucher_id',
        'color',
        'heading',
        'subheading',
        'badge',
        'image',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
