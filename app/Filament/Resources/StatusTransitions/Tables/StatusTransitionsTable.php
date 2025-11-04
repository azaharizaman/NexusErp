<?php

namespace App\Filament\Resources\StatusTransitions\Tables;

use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class StatusTransitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fromStatus.documentModel.name')
                    ->label('Document Model')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('fromStatus.name')
                    ->label('From Status')
                    ->badge()
                    ->color(fn ($record) => $record->fromStatus->color)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('toStatus.name')
                    ->label('To Status')
                    ->badge()
                    ->color(fn ($record) => $record->toStatus->color)
                    ->searchable()
                    ->sortable(),

                TextColumn::make('approvalWorkflows_count')
                    ->counts('approvalWorkflows')
                    ->label('Workflows')
                    ->badge()
                    ->color('info'),

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
            ])
            ->filters([
                SelectFilter::make('from_status_document_model')
                    ->label('Document Model')
                    ->relationship('fromStatus.documentModel', 'name'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
