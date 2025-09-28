<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index() {
        return response()->json(['success'=>true, 'data'=>Product::all()]);
    }

    public function show($id) {
        $product = Product::find($id);
        if (!$product) return response()->json(['success'=>false,'message'=>'Product not found'],404);
        return response()->json(['success'=>true, 'data'=>$product]);
    }
}
