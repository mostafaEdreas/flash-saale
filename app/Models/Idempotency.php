<?php

namespace App\Models;

use App\Enums\IdempotencyStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Idempotency extends Model
{
        use HasFactory  ;

    protected $fillable = [
        'key',
    ];
}
