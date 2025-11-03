<?php

namespace App\Filament\Resources\BusinessPartners\Tables;

use App\Models\BusinessPartner;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BusinessPartnersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('contacts'))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('code')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('parentPartner.name')
                    ->label('Parent Partner')
                    ->toggleable()
                    ->searchable(),
                TextColumn::make('type_labels')
                    ->label('Type')
                    ->state(fn (BusinessPartner $record) => implode(', ', $record->type_labels))
                    ->badge()
                    ->toggleable(),
                TextColumn::make('contacts_count')
                    ->label('Contacts')
                    ->sortable()
                    ->toggleable(),
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
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
