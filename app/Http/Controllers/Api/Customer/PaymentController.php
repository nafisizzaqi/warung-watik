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

        $rules = [
            'payment_type' => 'required|string',
            'transaction_status' => 'required|string',
            'shipping_cost' => 'required|numeric|min:0',
            'courier' => 'required|string',
            'service' => 'required|string',
        ];

        if ($request->payment_type !== 'cash') {
            $rules = array_merge($rules, [
                'transaction_id' => 'required|string',
                'gross_amount' => 'required|numeric|min:0',
                'transaction_time' => 'nullable|date',
                'fraud_status' => 'nullable|string',
                'va_number' => 'nullable|string',
                'bill_key' => 'nullable|string',
                'biller_code' => 'nullable|string',
            ]);
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Simpan payment
        if ($request->payment_type === 'cash') {
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'payment_type' => 'cash',
                    'transaction_status' => 'settlement',
                    'gross_amount' => $order->total_amount,
                    'transaction_id' => null,
                    'va_number' => null,
                    'bill_key' => null,
                    'biller_code' => null,
                    'fraud_status' => null,
                    'transaction_time' => now(),
                ]
            );
        } else {
            $payment = Payment::updateOrCreate(
                ['order_id' => $order->id],
                $request->only([
                    'transaction_id',
                    'payment_type',
                    'transaction_status',
                    'gross_amount',
                    'transaction_time',
                    'fraud_status',
                    'va_number',
                    'bill_key',
                    'biller_code',
                ])
            );
        }

        // Update order jika payment sukses
        if (in_array($payment->transaction_status, ['settlement', 'capture'])) {
            $order->status = 'success';
            $order->shipping_cost = $request->shipping_cost;
            $order->grand_total = $order->total_amount + $order->shipping_cost;
            $order->courier = $request->courier;
            $order->service = $request->service;
            $order->save();

            // Buat shipment jika belum ada
            if (!$order->shipments()->exists()) {
                Shipment::create([
                    'order_id' => $order->id,
                    'courier' => $request->courier,
                    'service' => $request->service,
                    'cost' => $request->shipping_cost,
                    'etd' => '2-3 days',
                    'status' => 'processing',
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
        $user = auth()->user(); // Ambil user login

        $order = Order::with('payments')->where('user_id', $user->id)->find($id);

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order tidak ditemukan atau bukan milik Anda'
            ], 404);
        }

        $payments = $order->payments;

        return response()->json([
            'success' => true,
            'data' => $payments
        ]);
    }
}
