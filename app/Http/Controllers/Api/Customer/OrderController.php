<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Cart;
use App\Models\Order;
use App\Models\CartItem;
use App\Models\OrderItem;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ShipmentService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    // List orders milik customer
    public function index(Request $request)
    {
        $customer = auth('customer')->user();

        $orders = Order::with(['items.product.category', 'payments', 'shipments'])
            ->where('customer_id', $customer->id)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'queue_number' => $order->order_number,
                    'status' => $order->status,
                    'subtotal' => $order->total_amount,
                    'discount' => 0,
                    'total' => $order->grand_total,
                    'shipping_address' => $order->shipping_address ?? null,
                    'payment_method' => $order->payment_method ?? null,
                    'items' => $order->items->map(function ($item) {
                        $product = $item->product;
                        return [
                            'id' => $item->id,
                            'quantity' => $item->quantity,
                            'subtotal' => $item->subtotal,
                            'product' => $product ? [
                                'id' => $product->id,
                                'name' => $product->name,
                                'category' => $product->category ? [
                                    'id' => $product->category->id,
                                    'name' => $product->category->name,
                                ] : null,
                                'description' => $product->description,
                                'image' => $product->image,
                                'price' => $product->price,
                            ] : null
                        ];
                    }),
                    'payment' => $order->payments,
                    'shipment' => $order->shipments,
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
                'status' => 'pending',              // default status baru
                'total_amount' => $total,
                'shipping_cost' => $shipping_cost,
                'grand_total' => $grand_total,
                'shipping_address' => null,
                'payment_method' => null,
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
                'data' => $order->load([
                    'items',
                    'items.product',
                    'items.product.category'
                ])
            ]);
        } catch (\Throwable $e) {
            Log::error('OrderController store() error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Show detail order + items
    public function show($id)
    {
        $customer = auth('customer')->user();
        $order = Order::with('items.product.category')
            ->where('customer_id', $customer->id)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    public function update(Request $request, $id)
    {
        $order = Order::where('id', $id)
            ->where('customer_id', auth('customer')->id())
            ->firstOrFail();

        $validated = $request->validate([
            'shipping_address' => 'required|string',
            'payment_method' => 'required|in:midtrans,cash',
            'courier' => 'required|string',
            'service' => 'required|string',
            'shipping_cost' => 'nullable|numeric|min:0', // <- tambahan
        ]);

        // Ambil shipping cost dari frontend jika ada, fallback ke DB
        $shippingCost = $validated['shipping_cost'] ?? 0;
        if ($shippingCost <= 0) {
            $service = ShipmentService::where('courier', $validated['courier'])
                ->where('code', $validated['service'])
                ->first();
            $shippingCost = $service ? $service->cost : 0;
        }

        // Hitung ulang grand total
        $productTotal = $order->items->sum('subtotal');
        $grandTotal = $productTotal + $shippingCost;

        // Update order
        $order->update([
            'shipping_address' => $validated['shipping_address'],
            'payment_method' => $validated['payment_method'],
            'courier' => $validated['courier'],
            'service' => $validated['service'],
            'shipping_cost' => $shippingCost,
            'grand_total' => $grandTotal,
        ]);

        return response()->json([
            'message' => 'Order updated successfully',
            'data' => $order->fresh()
        ]);
    }
}
