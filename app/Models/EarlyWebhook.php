<?php

namespace App\Models;

use App\Enums\EarlyWebhookStatus;
use Illuminate\Database\Eloquent\Model;

class EarlyWebhook extends Model
{
    protected $fillable = [
        'idempotency',
        'hold_id',
        'payload',
        'status'
    ];

    protected $casts = [
        'status' => EarlyWebhookStatus::class,
        'payload' => 'array' ,
    ];
}
