<?php

namespace App\Filament\PurchaseModule\Resources\PayableLedgers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PayableLedgersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('company.name')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->searchable(),
                TextColumn::make('supplierInvoice.id')
                    ->searchable(),
                TextColumn::make('paymentVoucher.id')
                    ->searchable(),
                TextColumn::make('baseCurrency.name')
                    ->searchable(),
                TextColumn::make('foreignCurrency.name')
                    ->searchable(),
                TextColumn::make('transaction_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('transaction_type')
                    ->searchable(),
                TextColumn::make('debit_amount_base')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('credit_amount_base')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('debit_amount_foreign')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('credit_amount_foreign')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('exchange_rate')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('exchange_rate_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('balance_base')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('balance_foreign')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('reference_number')
                    ->searchable(),
                TextColumn::make('created_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('updated_by')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
