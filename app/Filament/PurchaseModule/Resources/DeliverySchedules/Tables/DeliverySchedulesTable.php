<?php

namespace App\Filament\PurchaseModule\Resources\DeliverySchedules\Tables;

use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DeliverySchedulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('schedule_number')
                    ->label('Schedule Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('purchaseOrder.po_number')
                    ->label('PO Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('purchaseOrder.supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('scheduled_date')
                    ->label('Scheduled Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expected_date')
                    ->label('Expected Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('actual_delivery_date')
                    ->label('Actual Delivery')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'secondary' => 'scheduled',
                        'warning' => 'confirmed',
                        'info' => 'in_transit',
                        'success' => 'delivered',
                        'danger' => 'delayed',
                        'gray' => 'cancelled',
                    ])
                    ->sortable(),
                TextColumn::make('scheduled_quantity')
                    ->label('Qty Scheduled')
                    ->numeric(decimalPlaces: 3)
                    ->alignEnd(),
                TextColumn::make('delivered_quantity')
                    ->label('Qty Delivered')
                    ->numeric(decimalPlaces: 3)
                    ->alignEnd(),
                TextColumn::make('remaining_quantity')
                    ->label('Qty Remaining')
                    ->numeric(decimalPlaces: 3)
                    ->alignEnd(),
                TextColumn::make('delivery_location')
                    ->label('Location')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('tracking_number')
                    ->label('Tracking')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'confirmed' => 'Confirmed',
                        'in_transit' => 'In Transit',
                        'delivered' => 'Delivered',
                        'delayed' => 'Delayed',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                SelectFilter::make('purchaseOrder')
                    ->relationship('purchaseOrder', 'po_number')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('scheduled_date', 'asc');
    }
}
