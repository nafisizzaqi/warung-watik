<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // List orders milik customer
    public function index(Request $request)
    {
        $customer = auth('customer')->user();
        $orders = Order::with('items.product')
            ->where('customer_id', $customer->id)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $orders
        ]);
    }

    // Buat order dari cart
    public function store(Request $request)
    {
        $customer = auth('customer')->user();
        $cart = Cart::with('items.product')
            ->where('customer_id', $customer->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Cart is empty'
            ], 400);
        }

        $total = $cart->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $shipping_cost = $request->shipping_cost ?? 0;
        $grand_total = $total + $shipping_cost;

        $order = Order::create([
            'customer_id' => $customer->id,
            'order_number' => Str::upper(Str::random(10)),
            'status' => 'pending',
            'total_amount' => $total,
            'shipping_cost' => $shipping_cost,
            'grand_total' => $grand_total,
            'shipping_address' => $request->shipping_address,
            'payment_method' => $request->payment_method,
        ]);

        foreach ($cart->items as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => $item->quantity * $item->price,
            ]);
        }

        // Hapus cart setelah checkout
        $cart->items()->delete();
        $cart->delete();

        return response()->json([
            'success' => true,
            'message' => 'Order created successfully',
            'data' => $order->load('items.product')
        ]);
    }

    // Show detail order + items
    public function show($id)
    {
        $customer = auth('customer')->user();
        $order = Order::with('items.product')
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }
}
