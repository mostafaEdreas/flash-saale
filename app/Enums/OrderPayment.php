<?php

namespace App\Enums;

enum OrderPayment: string
{
    case PENDING = 'panding';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
}