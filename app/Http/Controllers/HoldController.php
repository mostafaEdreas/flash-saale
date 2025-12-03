<?php
namespace App\Http\Controllers;

use App\Enums\HoldStatus;
use App\Facades\Response;
use App\Http\Requests\HoldRequest;
use App\Http\Resources\HoldResource;
use App\Models\Hold;
use App\Models\Product;
use App\Traits\IdempotencyTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HoldController extends Controller
{
    use IdempotencyTrait;
    public function store(HoldRequest $request)
    {
        $productId = $request->input('product_id');
        $quantity  = $request->input('quantity');

        $this->guardIdempotency($request);
        $hold = DB::transaction(function () use ($productId, $quantity,$request) {
            // Get or create idempotency record

            $product = Product::lockForUpdate()->find($productId);

            if ($product->available_quantity < $quantity) {
                throw new HttpException(422, 'Insufficient product quantity available.');
            }


            $hold = Hold::create([
                'product_id' => $product->id,
                'quantity'   => $quantity,
                'expiry_at'  => now()->addMinutes(5),
                'status'     => HoldStatus::ACTIVE,
            ]);

            // Update product quantity
            $product->decrement('available_quantity', $quantity);
            Cache::forget("product_{$product->id}");
            $this->createIdempotency($request);
            return $hold;
        });

        $response = Response::setData(new HoldResource($hold));
        return response()->json($response->toArray());

    }

   

}
