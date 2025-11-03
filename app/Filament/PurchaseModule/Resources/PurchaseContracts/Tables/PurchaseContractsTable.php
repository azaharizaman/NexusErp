<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseContracts\Tables;

use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PurchaseContractsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('contract_number')
                    ->label('Contract Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('contract_name')
                    ->label('Contract Name')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('contract_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'blanket',
                        'success' => 'framework',
                        'info' => 'long_term',
                    ]),
                TextColumn::make('start_date')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('End Date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'active',
                        'danger' => 'expired',
                        'warning' => 'terminated',
                    ])
                    ->sortable(),
                TextColumn::make('contract_value')
                    ->label('Contract Value')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('utilized_value')
                    ->label('Utilized')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('remaining_value')
                    ->label('Remaining')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'expired' => 'Expired',
                        'terminated' => 'Terminated',
                    ])
                    ->multiple(),
                SelectFilter::make('contract_type')
                    ->options([
                        'blanket' => 'Blanket Order',
                        'framework' => 'Framework Agreement',
                        'long_term' => 'Long-term Contract',
                    ])
                    ->multiple(),
                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                    RestoreAction::make(),
                    ForceDeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
