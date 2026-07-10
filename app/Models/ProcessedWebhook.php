<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProcessedWebhook extends Model
{
    protected $fillable = [
        'req_id',
        'status',
        'amount',
        'ref_code',
    ];
}
