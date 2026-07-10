<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVoucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'voucher_id',
        'code',
        'description',
        'expires_at',
        'is_used',
    ];

    protected $casts = [
        'expires_at' => 'date',
        'is_used' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function voucher()
    {
        return $this->belongsTo(Voucher::class);
    }
}
