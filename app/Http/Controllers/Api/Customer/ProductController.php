<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Product::all()]);
    }

    public function show($id)
    {
        $product = Product::find($id);
        if (!$product)
            return response()->json(['success' => false, 'message' => 'Product not found'], 404);
        return response()->json(['success' => true, 'data' => $product]);
    }

    public function decreaseStock($id)
    {
        $product = Product::findOrFail($id);

        if ($product->stock <= 0) {
            return response()->json([
                'message' => 'Stock habis.'
            ], 400);
        }

        // kurangi stock secara aman
        $product->decrement('stock');

        return response()->json([
            'message' => 'Stock dikurangi.',
            'stock' => $product->stock
        ]);
    }
}
