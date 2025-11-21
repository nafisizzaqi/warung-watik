<?php

namespace App\Http\Controllers\Api\Customer;

use Stripe\Stripe;
use Stripe\Webhook;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Stripe\Checkout\Session as CheckoutSession;

class StripeController extends Controller
{
    public function createCheckoutSession(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'idr',
                        'product_data' => ['name' => $order->items[0]->product->name],
                        'unit_amount' => $order->grand_total * 100,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => env('APP_URL') . '/order-success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => env('APP_URL') . '/order-cancel',
        ]);
        $order->stripe_session_id = $session->id;
        $order->save();

        // Simpan transaksi pending
        Payment::create([
            'order_id' => $order->id,
            'transaction_id' => $session->id,
            'payment_type' => 'stripe',
            'transaction_status' => 'pending',
            'amount' => $order->grand_total,
        ]);

        return response()->json([
            'id' => $session->id,
            'url' => $session->url,
        ]);
    }

    public function handleWebhook(Request $request)
    {
        Log::info('Webhook hit', ['payload' => $request->all()]);
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        Log::info('RAW:', ['body' => $request->getContent()]);
        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $endpointSecret
            );
        } catch (\UnexpectedValueException $e) {
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;

            $payment = Payment::where('transaction_id', $session->id)->first();
            if ($payment) {
                $payment->update(['transaction_status' => 'paid']);
                $payment->order->update(['status' => 'success']);
            }
        }

        return response()->json(['received' => true]);
    }
}
