<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class ShipmentController extends Controller
{
    public function show($id)
    {
        // Cari order berdasarkan id
        $order = Order::with('shipments')->find($id);
        \Log::info("message", ['order' => $order]);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found'
            ], 404);
        }

        // Ambil shipment terkait order
        $shipment = $order->shipments; // relasi di Order model
        \Log::info("message", ['shipment' => $shipment]);

        if (!$shipment) {
            return response()->json([
                'success' => false,
                'message' => 'Shipment not available yet'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shipment
        ]);
    }
}
