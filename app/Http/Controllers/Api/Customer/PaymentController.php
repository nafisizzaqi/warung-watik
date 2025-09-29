<?php

namespace App\Http\Controllers\Api\Customer;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class PaymentController extends Controller
{
    public function store(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'transaction_id' => 'required|string',
            'payment_type' => 'required|string',
            'transaction_status' => 'required|string',
            'gross_amount' => 'required|numeric|min:0',
            'transaction_time' => 'nullable|date',
            'fraud_status' => 'nullable|string',
            'va_number' => 'nullable|string',
            'bill_key' => 'nullable|string',
            'biller_code' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = Payment::updateOrCreate(
            ['order_id' => $order->id],
            $request->all()
        );

        // Bisa juga update status order sesuai payment
        if ($payment->transaction_status === 'settlement' || $payment->transaction_status === 'capture') {
            $order->status = 'paid';
            $order->save();

            // Buat shipment otomatis
            if (!$order->shipments) { // pastikan belum ada shipment
                Shipment::create([
                    'order_id' => $order->id,
                    'courier' => 'jne',         // default courier
                    'service' => 'REG',         // default service
                    'cost' => 10000,            // default cost, bisa ambil dari order shipping_cost
                    'etd' => '2-3 days',        // default estimated delivery
                    'status' => 'processing',
                    'tracking_number' => strtoupper(Str::random(8)),
                ]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Payment recorded successfully',
            'data' => $payment
        ]);
    }

    // Lihat detail pembayaran order
    public function show($id)
    {
        $order = Order::with('payment')->find($id);
        $payment = $order->payment;

        return response()->json([
            'success' => true,
            'data' => $payment
        ]);
    }
}
