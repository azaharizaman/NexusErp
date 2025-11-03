<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Tables;

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

class PaymentVouchersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('voucher_number')
                    ->label('Voucher Number')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold'),
                TextColumn::make('supplier.name')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('purchaseOrder.po_number')
                    ->label('Purchase Order')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->badge()
                    ->colors([
                        'primary' => 'bank_transfer',
                        'success' => 'check',
                        'warning' => 'cash',
                        'info' => 'credit_card',
                    ])
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending_approval',
                        'info' => 'approved',
                        'success' => 'paid',
                        'danger' => 'cancelled',
                    ])
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn (string $state): string => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency?->code ?? 'USD')
                    ->sortable()
                    ->alignEnd(),
                TextColumn::make('base_amount')
                    ->label('Base Amount')
                    ->money('USD')
                    ->sortable()
                    ->alignEnd()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('currency.code')
                    ->label('Currency')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('payment_reference')
                    ->label('Reference')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('approver.name')
                    ->label('Approved By')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('approved_at')
                    ->label('Approved At')
                    ->dateTime()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('paidBy.name')
                    ->label('Paid By')
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('updated_at')
                    ->label('Updated At')
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
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'paid' => 'Paid',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                SelectFilter::make('payment_method')
                    ->options([
                        'bank_transfer' => 'Bank Transfer',
                        'check' => 'Check',
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'debit_card' => 'Debit Card',
                        'online_payment' => 'Online Payment',
                        'wire_transfer' => 'Wire Transfer',
                    ])
                    ->multiple(),
                SelectFilter::make('supplier')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('company')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('currency')
                    ->relationship('currency', 'name')
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
