<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'discount',
        'label',
        'type',
        'is_active',
    ];

    protected $casts = [
        'discount' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function userVouchers()
    {
        return $this->hasMany(UserVoucher::class);
    }

    public function promo()
    {
        return $this->hasOne(Promo::class);
    }
}
