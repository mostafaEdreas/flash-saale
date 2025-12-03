<?php

namespace App\Services;

use App\Enums\HoldStatus;
use App\Models\Hold;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
class HoldService {
        public function expireHolds(){
        $holds =  Hold::expired()->get();
        
        foreach ($holds as $hold) {
            DB::transaction(function () use ($hold) {
            
                $hold->lockForUpdate(); 
                
                // Change hold status to expired
                $hold->update(['status' => HoldStatus::EXPIRED]);

                // Restore product quantity 
                if ($product = $hold->product) {
                    // Lock the product row to prevent race with Create Hold/Order (safest practice)
                    $product->lockForUpdate(); 
                    $product->increment('available_quantity', $hold->quantity);
                    Cache::forget("product_{$product->id}");
                }
                
            });
        }
    }
}