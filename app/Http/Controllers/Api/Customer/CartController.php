<?php
namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    // List cart beserta items
    public function index(Request $request)
    {
        $user = auth('customer')->user();

        $cart = Cart::with('items.product')
            ->firstOrCreate(['user_id' => $user->id]);

        return response()->json([
            'success' => true,
            'data' => $cart,
        ]);
    }

    // Tambah produk ke cart
    public function store(Request $request)
    {
        $user = auth('customer')->user();

        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cart = Cart::firstOrCreate(['user_id' => $user->id]);

        $item = CartItem::updateOrCreate(
            [
                'cart_id' => $cart->id,
                'product_id' => $request->product_id,
            ],
            [
                'quantity' => \DB::raw("quantity + {$request->quantity}")
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart',
            'data' => $item,
        ]);
    }

    // Update jumlah item
    public function update(Request $request, $id)
    {
        $user = auth('customer')->user();

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)->findOrFail($id);

        $item->quantity = $request->quantity;
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Cart item updated',
            'data' => $item,
        ]);
    }

    // Hapus item
    public function destroy($id)
    {
        $user = auth('customer')->user();

        $cart = Cart::where('user_id', $user->id)->firstOrFail();

        $item = CartItem::where('cart_id', $cart->id)->findOrFail($id);
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart',
        ]);
    }
}
