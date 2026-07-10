<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpotlightCustomer extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'user_id',
        'name',
        'cups_this_month',
        'avatar',
        'reward',
    ];

    protected $casts = [
        'cups_this_month' => 'integer',
    ];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
