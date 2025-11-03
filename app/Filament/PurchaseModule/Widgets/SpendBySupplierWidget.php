<?php

namespace App\Filament\PurchaseModule\Widgets;

use App\Models\PurchaseOrder;
use Filament\Widgets\ChartWidget;

class SpendBySupplierWidget extends ChartWidget
{
    protected static ?string $heading = 'Spend by Top 10 Suppliers';

    protected static ?int $sort = 2;

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $spendData = PurchaseOrder::query()
            ->with('supplier')
            ->selectRaw('supplier_id, SUM(total_amount) as total_spend')
            ->whereIn('status', ['approved', 'issued', 'closed'])
            ->groupBy('supplier_id')
            ->orderByDesc('total_spend')
            ->limit(10)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Spend',
                    'data' => $spendData->pluck('total_spend')->toArray(),
                    'backgroundColor' => [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)',
                        'rgba(255, 159, 64, 0.5)',
                        'rgba(199, 199, 199, 0.5)',
                        'rgba(83, 102, 255, 0.5)',
                        'rgba(255, 99, 255, 0.5)',
                        'rgba(99, 255, 132, 0.5)',
                    ],
                ],
            ],
            'labels' => $spendData->map(fn ($item) => $item->supplier?->name ?? 'Unknown')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
