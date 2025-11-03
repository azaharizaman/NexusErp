<?php

namespace App\Filament\PurchaseModule\Widgets;

use App\Models\PurchaseOrder;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SpendAnalysisWidget extends ChartWidget
{
    protected static ?string $heading = 'Spend Analysis - Last 6 Months';

    protected static ?int $sort = 1;

    protected static ?string $pollingInterval = null;

    protected function getData(): array
    {
        $data = Trend::model(PurchaseOrder::class)
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'Total Spend',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => 'rgba(255, 193, 7, 0.2)',
                    'borderColor' => 'rgb(255, 193, 7)',
                    'borderWidth' => 2,
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => date('M Y', strtotime($value->date))),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
