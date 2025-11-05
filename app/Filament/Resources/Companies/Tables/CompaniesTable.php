<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Actions\Company\ToggleCompanyStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class CompaniesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('code')
                    ->searchable(),
                TextColumn::make('parentCompany.name')
                    ->searchable(),
                IconColumn::make('is_active')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Active'),
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
                ViewAction::make(),
                EditAction::make(),

                // Toggle Company Status Action
                Action::make('toggleStatus')
                    ->label(fn ($record) => $record->is_active ? 'Deactivate' : 'Activate')
                    ->icon(fn ($record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->is_active ? 'danger' : 'success')
                    ->requiresConfirmation()
                    ->modalHeading(fn ($record) => $record->is_active ? 'Deactivate Company' : 'Activate Company')
                    ->modalDescription(fn ($record) => $record->is_active
                        ? "Are you sure you want to deactivate '{$record->name}'?"
                        : "Are you sure you want to activate '{$record->name}'?")
                    ->action(function ($record) {
                        $company = ToggleCompanyStatus::run($record);

                        $message = $company->is_active
                            ? "Company '{$company->name}' has been activated successfully."
                            : "Company '{$company->name}' has been deactivated successfully.";

                        Notification::make()
                            ->title($message)
                            ->success()
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),

                    // Bulk activate companies
                    BulkAction::make('activateCompanies')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Activate Companies')
                        ->modalDescription('Are you sure you want to activate the selected companies?')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if (! $record->is_active) {
                                    ToggleCompanyStatus::run($record, true);
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Successfully activated {$count} companies")
                                    ->success()
                                    ->send();
                            }
                        }),

                    // Bulk deactivate companies
                    BulkAction::make('deactivateCompanies')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Deactivate Companies')
                        ->modalDescription('Are you sure you want to deactivate the selected companies?')
                        ->action(function (Collection $records) {
                            $count = 0;
                            foreach ($records as $record) {
                                if ($record->is_active) {
                                    ToggleCompanyStatus::run($record, false);
                                    $count++;
                                }
                            }

                            if ($count > 0) {
                                \Filament\Notifications\Notification::make()
                                    ->title("Successfully deactivated {$count} companies")
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
            ]);
    }
}
