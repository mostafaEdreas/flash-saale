<?php

namespace App\Models;

use App\Enums\HoldStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    use HasFactory ;
   protected $fillable = [
        'product_id',
        'quantity',
        'expiry_at',
        'status',
    ];

    protected $casts = [
        'expiry_at' => 'datetime',
        'status' => HoldStatus::class,
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function isActive(): bool
    {
        return $this->status === HoldStatus::ACTIVE && $this->expiry_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->expiry_at->isPast();
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_at', '<', now())->where('status', HoldStatus::ACTIVE);
    }


   
}
