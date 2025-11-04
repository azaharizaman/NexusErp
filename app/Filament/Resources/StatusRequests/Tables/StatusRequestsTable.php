<?php

namespace App\Filament\Resources\StatusRequests\Tables;

use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatusRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('documentModel.name')
                    ->label('Document Model')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model_type')
                    ->label('Model Type')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('model_id')
                    ->label('Model ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('currentStatus.name')
                    ->label('From')
                    ->badge()
                    ->color(fn ($record) => $record->currentStatus->color)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('requestedStatus.name')
                    ->label('To')
                    ->badge()
                    ->color(fn ($record) => $record->requestedStatus->color)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('is_approved')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(function ($record) {
                        if ($record->is_approved) {
                            return 'Approved';
                        }

                        if ($record->approved_at && ! $record->is_approved) {
                            return 'Rejected';
                        }

                        return 'Pending';
                    })
                    ->color(function ($record) {
                        if ($record->is_approved) {
                            return 'success';
                        }

                        if ($record->approved_at && ! $record->is_approved) {
                            return 'danger';
                        }

                        return 'warning';
                    })
                    ->sortable(),

                TextColumn::make('requester.name')
                    ->label('Requested By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('approver.name')
                    ->label('Processed By')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Requested At')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('approved_at')
                    ->label('Processed At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                SelectFilter::make('document_model_id')
                    ->relationship('documentModel', 'name')
                    ->label('Document Model'),

                SelectFilter::make('is_approved')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->query(function ($query, $state) {
                        if ($state['value'] === 'pending') {
                            return $query->whereNull('approved_at');
                        }
                        if ($state['value'] === 'approved') {
                            return $query->where('is_approved', true);
                        }
                        if ($state['value'] === 'rejected') {
                            return $query->where('is_approved', false)->whereNotNull('approved_at');
                        }
                    }),
            ])
            ->actions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
