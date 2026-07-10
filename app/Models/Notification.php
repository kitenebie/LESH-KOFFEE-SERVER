<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $table = 'user_notifications';

    protected $fillable = [
        'user_id',
        'type',
        'icon',
        'title',
        'message',
        'is_unread',
    ];

    protected $casts = [
        'is_unread' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
