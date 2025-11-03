<?php

namespace App\Filament\PurchaseModule\Resources\RequestForQuotations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class RequestForQuotationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('rfq_number')
                    ->label('RFQ Number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('rfq_date')
                    ->label('RFQ Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('expiry_date')
                    ->label('Expiry Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'sent' => 'info',
                        'received' => 'warning',
                        'evaluated' => 'success',
                        'closed' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('currency.name')
                    ->label('Currency')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('purchaseRequests')
                    ->label('Linked PRs')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->purchaseRequests->pluck('pr_number'))
                    ->separator(',')
                    ->toggleable(),
                TextColumn::make('invitedSuppliers')
                    ->label('Suppliers')
                    ->badge()
                    ->getStateUsing(fn ($record) => $record->invitedSuppliers->pluck('name'))
                    ->separator(',')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Deleted At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'sent' => 'Sent',
                        'received' => 'Received',
                        'evaluated' => 'Evaluated',
                        'closed' => 'Closed',
                        'cancelled' => 'Cancelled',
                    ]),
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
            ])
            ->defaultSort('rfq_date', 'desc');
    }
}
