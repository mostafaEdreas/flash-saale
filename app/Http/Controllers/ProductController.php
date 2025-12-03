<?php

namespace App\Http\Controllers;

use App\Facades\Response;
use App\Models\Product;
use App\Services\PaginationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    
    public function index()
    {
        $products = Product::paginate(20);
        
        $responnse = Response::setData($products->getCollection())
            ->additionals('pagination', new PaginationService($products));
       return response()->json($responnse->toArray());
    }

    public function show($id)
    {
        $keyProduct = 'product_'.$id;
        $product = Cache::remember($keyProduct, 600, function () use ($id) {
            return Product::select('id','available_quantity','name')->findOrFail($id);
        });
        $response = Response::setData($product);
        return response()->json($response->toArray());

    }

    public function create()
    {
        // Logic to show form for creating a new product
    }

    public function store(Request $request)
    {
        // Logic to store a new product
    }

    public function edit($id)
    {
        // Logic to show form for editing a product
    }

    public function update(Request $request, $id)
    {
        // Logic to update an existing product
    }

    public function destroy($id)
    {
        // Logic to delete a product
    }
}
