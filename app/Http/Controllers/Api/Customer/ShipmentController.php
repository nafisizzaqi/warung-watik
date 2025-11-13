<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Shipment;
use Illuminate\Support\Facades\Validator;

class ShipmentController extends Controller
{

    protected $couriers = [
        'jne' => [
            'REG' => ['label' => 'Regular', 'cost' => 9000],
            'YES' => ['label' => 'Yakin Esok Sampai', 'cost' => 15000],
            'OKE' => ['label' => 'Ongkos Kirim Ekonomis', 'cost' => 7000],
        ],
        'tiki' => [
            'ECO' => ['label' => 'Economy', 'cost' => 8000],
            'REG' => ['label' => 'Regular', 'cost' => 10000],
            'ONS' => ['label' => 'Over Night Service', 'cost' => 20000],
        ],
        'pos' => [
            'Paket Kilat Khusus' => ['label' => 'Paket Kilat Khusus', 'cost' => 10000],
            'Express Next Day' => ['label' => 'Express Next Day', 'cost' => 20000],
        ],
    ];
    /**
     * Buat shipment untuk order sebelum checkout
     */
    public function createShipment(Request $request, $orderId)
    {
        $order = Order::findOrFail($orderId);

        $validator = Validator::make($request->all(), [
            'courier' => 'required|string|in:' . implode(',', array_keys($this->couriers)),
            'service' => [
                'required',
                function ($attribute, $value, $fail) use ($request) {
                    if (!isset($this->couriers[$request->courier][$value])) {
                        $fail("Service tidak valid untuk kurir {$request->courier}");
                    }
                }
            ],
            'cost' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $cost = $this->couriers[$request->courier][$request->service]['cost'] ?? 0;

        $shipment = Shipment::updateOrCreate(
            ['order_id' => $order->id],
            [
                'courier' => $request->courier,
                'service' => $request->service,
                'cost' => $cost,
                'status' => 'processing',
            ]
        );

        return response()->json([
            'success' => true,
            'shipment' => $shipment,
        ]);
    }

    public function getCourierOptions()
    {
        $options = collect($this->couriers)->map(function ($services, $courier) {
            return [
                'courier' => $courier,
                'services' => collect($services)->map(fn($serviceData, $code) => [
                    'code' => $code,
                    'label' => $serviceData['label'], // ambil label
                    'cost' => $serviceData['cost'],  // ambil cost juga
                ])->values()
            ];
        })->values();

        return response()->json($options);
    }
}
