<?php

namespace App\Filament\PurchaseModule\Widgets;

use App\Models\DeliverySchedule;
use App\Models\PurchaseOrder;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class SupplierPerformanceWidget extends BaseWidget
{
    protected static ?int $sort = 4;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PurchaseOrder::query()
                    ->with(['supplier', 'deliverySchedules'])
                    ->whereHas('deliverySchedules')
                    ->selectRaw('supplier_id, COUNT(*) as order_count')
                    ->groupBy('supplier_id')
                    ->orderByDesc('order_count')
                    ->limit(10)
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('order_count')
                    ->label('Total Orders')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('on_time_deliveries')
                    ->label('On-Time Deliveries')
                    ->getStateUsing(function ($record) {
                        return DeliverySchedule::where('supplier_id', $record->supplier_id)
                            ->where('status', 'delivered')
                            ->whereColumn('delivered_at', '<=', 'scheduled_date')
                            ->count();
                    })
                    ->alignEnd(),
                TextColumn::make('late_deliveries')
                    ->label('Late Deliveries')
                    ->getStateUsing(function ($record) {
                        return DeliverySchedule::where('supplier_id', $record->supplier_id)
                            ->where('status', 'delivered')
                            ->whereColumn('delivered_at', '>', 'scheduled_date')
                            ->count();
                    })
                    ->alignEnd()
                    ->color('warning'),
                TextColumn::make('performance_rate')
                    ->label('Performance %')
                    ->getStateUsing(function ($record) {
                        $onTime = DeliverySchedule::where('supplier_id', $record->supplier_id)
                            ->where('status', 'delivered')
                            ->whereColumn('delivered_at', '<=', 'scheduled_date')
                            ->count();
                        $total = DeliverySchedule::where('supplier_id', $record->supplier_id)
                            ->where('status', 'delivered')
                            ->count();
                        
                        if ($total === 0) {
                            return 'N/A';
                        }
                        
                        return round(($onTime / $total) * 100, 1) . '%';
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 'N/A' => 'gray',
                        (float)str_replace('%', '', $state) >= 90 => 'success',
                        (float)str_replace('%', '', $state) >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->alignEnd(),
            ])
            ->heading('Supplier Performance')
            ->description('Top 10 suppliers by order count with delivery performance');
    }
}
