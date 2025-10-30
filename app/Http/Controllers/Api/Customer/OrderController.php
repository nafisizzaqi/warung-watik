<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // List orders milik customer
 public function index(Request $request)
{
    $customer = auth('customer')->user();

    $orders = Order::with(['items.product', 'payments', 'shipments'])
        ->where('customer_id', $customer->id)
        ->get()
        ->map(function($order) {
            return [
                'id' => $order->id,
                'queue_number' => $order->order_number, // supaya React tetap cocok
                'status' => $order->status,
                'subtotal' => $order->total_amount,     // sesuai DB
                'discount' => 0,                        // kalau ga ada diskon
                'total' => $order->grand_total,         // supaya React tetap pakai .total
                'shipping_address' => $order->shipping_address ?? null,
                'payment_method' => $order->payment_method ?? null,
                'items' => $order->items,
                'payment' => $order->payment,           // langsung include payment
                'shipment' => $order->shipment,         // langsung include shipment
                'created_at' => $order->created_at,
            ];
        });

    return response()->json([
        'success' => true,
        'data' => $orders
    ]);
}


    // Buat order dari cart
    public function store(Request $request)
    {
        try {
        $customer = auth('customer')->user();
        Log::info('Auth check', ['customer' => $customer]);

        if (!$customer) {
            return response()->json(['error' => 'Unauthenticated customer'], 401);
        }

        $cart = Cart::with('items.product')
            ->where('customer_id', $customer->id)
            ->first();

        if (!$cart || $cart->items->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        $total = $cart->items->sum(function ($item) {
            return $item->quantity * $item->price;
        });

        $shipping_cost = $request->shipping_cost ?? 0;
        $grand_total = $total + $shipping_cost;

        // Hitung queue_number terbaru
$latestQueue = Order::max('queue_number') ?? 0;
$queueNumber = $latestQueue + 1;

// Buat order
$order = Order::create([
    'customer_id' => $customer->id,
    'order_number' => Str::upper(Str::random(10)),
    'queue_number' => $queueNumber,   // <--- sini
    'status' => 'masuk',              // default status baru
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
        } catch (\Throwable $e) {
        Log::error('OrderController store() error: '.$e->getMessage(), ['trace' => $e->getTraceAsString()]);
        return response()->json(['error' => $e->getMessage()], 500);
    }
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
