<?php

namespace App\Traits;

use App\Enums\HoldStatus;
use App\Models\Hold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

trait HoldTrait {
    public function checkHoldIsActive(Hold $hold ):bool|HttpException
    {
        if (!$hold->isActive()) {
            throw new HttpException(400, "Hold is {$hold->status->value}, cannot place order.");
        }
        return true ;
    }


}