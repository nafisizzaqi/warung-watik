<?php

namespace App\Filament\Widgets;

use App\Models\Payment;
use Filament\Widgets\LineChartWidget;
use Illuminate\Support\Carbon;

class PurchaseHistoryChart extends LineChartWidget
{
    protected static ?string $heading = 'Histori Pembelian (Revenue)';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        // Ambil transaksi settlement 30 hari terakhir
        $payments = Payment::query()
            ->where('transaction_status', 'settlement')
            ->whereDate('transaction_time', '>=', now()->subDays(30))
            ->orderBy('transaction_time')
            ->get()
            ->groupBy(fn ($payment) => Carbon::parse($payment->transaction_time)->format('Y-m-d'));

        $labels = [];
        $data = [];

        foreach ($payments as $date => $items) {
            $labels[] = $date;

            // Jumlah revenue per hari dari midtrans_status->gross_amount
            $dailyTotal = $items->sum(function ($item) {
                $status = is_array($item->midtrans_status) ? $item->midtrans_status : json_decode($item->midtrans_status, true);
                return isset($status['gross_amount']) ? (float) $status['gross_amount'] : 0;
            });

            $data[] = $dailyTotal;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Revenue (IDR)',
                    'data' => $data,
                    'borderColor' => '#4F46E5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.2)',
                    'fill' => true,
                ],
            ],
        ];
    }
}
