<?php

namespace App\Models;

use App\Enums\OrderPayment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
        use HasFactory ;

    protected $fillable = [
        'hold_id',
        'quantity',
        'total_price',
        'unit_price',
        'product_id',
        'payment',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'payment' => OrderPayment::class,
    ];

    public function hold()
    {
        return $this->belongsTo(hold::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
