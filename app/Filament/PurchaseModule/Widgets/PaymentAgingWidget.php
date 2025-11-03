<?php

namespace App\Filament\PurchaseModule\Widgets;

use App\Models\PaymentSchedule;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class PaymentAgingWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                PaymentSchedule::query()
                    ->with(['supplier', 'currency'])
                    ->where('status', '!=', 'paid')
                    ->orderBy('due_date', 'asc')
            )
            ->columns([
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('days_overdue')
                    ->label('Days Overdue')
                    ->getStateUsing(function ($record) {
                        if ($record->due_date->isFuture()) {
                            return 0;
                        }
                        return now()->diffInDays($record->due_date);
                    })
                    ->badge()
                    ->color(fn ($state) => match (true) {
                        $state === 0 => 'success',
                        $state <= 30 => 'warning',
                        $state <= 60 => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('scheduled_amount')
                    ->label('Scheduled Amount')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable(),
                TextColumn::make('paid_amount')
                    ->label('Paid Amount')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable(),
                TextColumn::make('remaining_amount')
                    ->label('Remaining Amount')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'secondary' => 'scheduled',
                        'warning' => 'overdue',
                        'info' => 'partially_paid',
                        'success' => 'paid',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
            ])
            ->heading('Payment Aging Analysis')
            ->description('Outstanding payments by due date');
    }
}
