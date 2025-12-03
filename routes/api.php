    
 <?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\HoldController;
use App\Http\Controllers\OrderController;

//  products
Route::get('/products', [ProductController::class, 'index']);

Route::get('/products/{id}', [ProductController::class, 'show']);


//  holds
Route::post('/holds', [HoldController::class, 'store']);

Route::post('/payments/webhook' , [OrderController::class,'webhook']) ;
Route::post('/orders' , [OrderController::class,'placeOrder']) ;

