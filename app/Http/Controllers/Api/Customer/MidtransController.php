<?php

namespace App\Http\Controllers\Api\Customer;

use Midtrans\Snap;
use Midtrans\Config;
use Midtrans\Transaction;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class MidtransController extends Controller
{
    /**
     * Buat Snap Token untuk order
     */
    public function createSnapToken($orderId)
    {
        $order = Order::with('items.product', 'customer')->findOrFail($orderId);

        // Konfigurasi Midtrans
        $this->setMidtransConfig();

        // Siapkan item untuk Midtrans
        $items = $order->items->map(function ($item) {
            return [
                'id' => $item->product_id,
                'price' => (int) $item->price,
                'quantity' => (int) $item->quantity,
                'name' => $item->product->name,
            ];
        })->toArray();

        $params = [
            'transaction_details' => [
                'order_id' => 'ORDER-' . $order->id . '-' . time(),
                'gross_amount' => (int) $order->grand_total,
            ],
            'customer_details' => [
                'first_name' => $order->customer->name ?? 'Customer',
                'email' => $order->customer->email ?? 'customer@example.com',
                'phone' => $order->customer->phone ?? '08123456789',
            ],
            'item_details' => $items,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan token ke order
            $order->update([
                'snap_token' => $snapToken,
                'midtrans_order_id' => $params['transaction_details']['order_id'],
            ]);

            // Buat Payment record awal dengan status menunggu
            Payment::updateOrCreate(
                ['order_id' => $order->id],
                [
                    'transaction_id' => null, // biar konsisten
                    'transaction_status' => 'menunggu',
                    'gross_amount' => $order->grand_total,
                    'transaction_time' => now(),
                ]
            );

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $order->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Midtrans SnapToken error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal membuat Snap Token'], 500);
        }
    }

    /**
     * Callback / Webhook Midtrans
     */
    public function handleCallback(Request $request)
    {
        Log::info('Midtrans Callback Request:', $request->all());

        $order = Order::where('midtrans_order_id', $request->order_id)->first();
        Log::info('Updating Payment status', [
            'order_id' => $order->id,
            'old_status' => $order->payment?->transaction_status ?? 'null',
            'new_status' => $this->mapMidtransStatus($request->transaction_status, $request->fraud_status ?? null),
        ]);

        if (!$order) {
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Verifikasi signature
        $serverKey = config('midtrans.server_key');
        $calculatedSignature = hash(
            'sha512',
            $request->order_id .
            $request->status_code .
            $request->gross_amount .
            $serverKey
        );

        if ($calculatedSignature !== $request->signature_key) {
            Log::warning('Invalid Midtrans signature for order_id: ' . $request->order_id);
            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Update order status
        $order->midtrans_transaction_status = $request->transaction_status;
        $order->status = $this->mapMidtransStatus($request->transaction_status, $request->fraud_status ?? null);
        $order->save();

        // Simpan atau update payment
        Payment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'transaction_id' => $request->transaction_id ?? null,
                'payment_type' => $request->payment_type ?? null,
                'transaction_status' => $this->mapMidtransStatus($request->transaction_status, $request->fraud_status ?? null),
                'transaction_time' => $request->transaction_time ?? now(),
                'gross_amount' => $request->gross_amount ?? 0,
                'fraud_status' => $request->fraud_status ?? null,
                'va_number' => $request->va_number ?? null,
                'bill_key' => $request->bill_key ?? null,
                'biller_code' => $request->biller_code ?? null,
            ]
        );

        return response()->json(['success' => true, 'data' => $order]);
    }

    /**
     * Cek status transaksi Midtrans
     */
    public function checkStatus($midtransOrderId)
    {
        $order = Order::where('midtrans_order_id', $midtransOrderId)->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'Order not found or midtrans_order_id missing'
            ], 404);
        }

        try {
            $this->setMidtransConfig();

            $status = Transaction::status($order->midtrans_order_id);

            // Update atau buat payment record
            Payment::updateOrCreate(
                ['transaction_id' => $status->transaction_id],
                [
                    'order_id' => $order->id,
                    'payment_type' => $status->payment_type ?? null,
                    'transaction_status' => $status->transaction_status ?? null,
                    'transaction_time' => $status->transaction_time ?? now(),
                    'gross_amount' => $status->gross_amount ?? 0,
                    'fraud_status' => $status->fraud_status ?? null,
                ]
            );

            // Update order status
            $order->midtrans_transaction_status = $status->transaction_status;
            $order->status = $this->mapMidtransStatus($status->transaction_status, $status->fraud_status ?? null);
            $order->save();

            return response()->json([
                'success' => true,
                'order_id' => $order->id,
                'midtrans_order_id' => $order->midtrans_order_id,
                'midtrans_status' => $status,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper untuk set konfigurasi Midtrans
     */
    protected function setMidtransConfig(): void
    {
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * Helper mapping status Midtrans → status order/payment
     */
    protected function mapMidtransStatus(string $transactionStatus, ?string $fraudStatus = null): string
    {
        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'success' : 'pending',
            'settlement' => 'success',
            'pending' => 'pending',
            'deny', 'expire', 'cancel' => 'cancel',
            default => 'pending',
        };
    }
}
