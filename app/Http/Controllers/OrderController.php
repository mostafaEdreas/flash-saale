<?php
namespace App\Http\Controllers;

use App\Enums\EarlyWebhookStatus;
use App\Enums\HoldStatus;
use App\Enums\OrderPayment;
use App\Facades\Response;
use App\Models\EarlyWebhook;
use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use App\Services\ResponseService;
use App\Traits\HoldTrait;
use App\Traits\IdempotencyTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

class OrderController extends Controller
{
    use IdempotencyTrait ,HoldTrait;

    public function placeOrder(Request $request)
    {
        $hold_id = $request->input('hold_id');
        $hold = Hold::lockForUpdate()->findOrFail($hold_id);

        // if hold recourd is not active return throw with code 400
        $this->checkHoldIsActive( $hold);
        
        // handle Order with transaction
        $order = DB::transaction(function () use ($hold ,  $request) {

            /// if idempotency key Exsits return throw
           $this->guardIdempotency($request);
                

            //
            $order = Order::create([
                'hold_id'     => $hold->id,
                'product_id'  => $hold->product_id,
                'quantity'    => $hold->quantity,
                'unit_price'  => $hold->product->price ?? 0,
                'total_price' => $hold->quantity * ($hold->product->price ?? 0),
                'payment'     => OrderPayment::PENDING,
            ]);

            $hold->status = HoldStatus::UESED;
            $hold->save();
            $this->createIdempotency( $request);
            return $order;
        });
        $this->ProcessEarlyWebhook($order);
        $response = Response::setData($order->fresh());

        return response()->json($response->toArray());
    }

    public function webhook(Request $request)
    {
        $response = DB::transaction(function () use ($request)  {


            $hold_id = $request->input('hold_id') ;

            $idempotency = $this->checkIdempotency($request);
            $order = Order::lockForUpdate()->with('hold')->where('hold_id',$hold_id)->first();

             /// if order doesnt arrive yet craete early web hook and retun success to provider ;
            if (!$order) {
               return $this->earlyWebhook($request)->toArray() ;
            }

            if ($idempotency || $order->payment !== OrderPayment::PENDING) {
                return $this->checkOrderStatus($order)->toArray();
            }

             // Handle the payment logic here
            $status = $request->input('status');
            if ($status === 'success') {
                $order->payment = OrderPayment::PAID;
            } else {
                $order->payment = OrderPayment::CANCELLED;
                $product = Product::lockForUpdate()->findOrFail($order->hold?->product_id); 
                $product->increment('available_quantity', $order->quantity);
                Cache::forget("product_{$product->id}");
            }
            $order->save();
            
            $this->createIdempotency($request);

            // now the ordre status acsuly return response with current status
            return $this->checkOrderStatus($order)->toArray();
    
        });
        

        return response()->json($response);
    }


    private function checkOrderStatus(Order $order): ? ResponseService{
         if ($order->payment === OrderPayment::PAID) {
                // If already paid, return success message
                return Response::setData($order);
            } elseif($order->payment === OrderPayment::CANCELLED) {
                // If already cancelled, return cancelled message
                return Response::setMessage('Order is CANCELLED');
            } 
            return null ;
    }


    private function earlyWebhook(Request $request): ResponseService {
        $hold_id = $request->input('hold_id');
        $idempotency = $request->input('idempotency');
        if( EarlyWebhook::where('idempotency' , $idempotency)->exists()){
            return Response::setMessage('Payment already processed.');
        }
        EarlyWebhook::create(
            [
                    'idempotency' => $idempotency,
                    'hold_id' => $hold_id,
                    'status' => EarlyWebhookStatus::RECEIVED,
                    'payload' => $request->all(),
                ]
            );

        return Response::setMessage('Your order has been paid successfully. It hasn’t arrived yet, but we will deliver it soon.');

    }

    private function ProcessEarlyWebhook(Order $order) : void {
        $earlyWebhook = EarlyWebhook::where('hold_id',$order->hold_id)
                ->where('status',EarlyWebhookStatus::RECEIVED)->first();
        if ($earlyWebhook) {

            $fakeRequest = new Request($earlyWebhook->payload);
            
             $this->webhook($fakeRequest);

            $earlyWebhook->update(['status' => EarlyWebhookStatus::PROCESSED]);

        }
        
    }

}
