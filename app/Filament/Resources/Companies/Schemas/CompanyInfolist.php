<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Actions\Company\ToggleCompanyStatus;
use AzahariZaman\BackOffice\Models\Company;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Size;
use Illuminate\Validation\ValidationException;
use Throwable;

class CompanyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->inlineLabel(),
                TextEntry::make('code')
                    ->placeholder('-'),
                TextEntry::make('description')
                    ->placeholder('-')
                    ->columnSpanFull()
                    ->hiddenLabel(),
                TextEntry::make('parentCompany.name')
                    ->label('Parent company')
                    ->placeholder('-')
                    ->afterLabel(
                        Action::make('changeParentCompany')
                            ->label('Change Parent Company')
                            ->icon('heroicon-m-arrow-path-rounded-square')
                            ->size(Size::Small)
                            ->form(fn (Company $record): array => [
                                Select::make('parent_company_id')
                                    ->label('Parent Company')
                                    ->options(
                                        Company::query()
                                            ->when($record->exists, function ($query) use ($record) {
                                                $excludedIds = $record->allChildCompanies()
                                                    ->pluck('id')
                                                    ->push($record->id)
                                                    ->all();

                                                $query->whereNotIn('id', $excludedIds);
                                            })
                                            ->orderBy('name')
                                            ->pluck('name', 'id')
                                            ->toArray()
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('No parent')
                                    ->default($record->parent_company_id)
                                    ->helperText('Leave empty to remove the parent relationship.'),
                            ])
                            ->action(function (array $data, Company $record, Action $action): void {
                                $parentId = $data['parent_company_id'] ?? null;

                                if (filled($parentId)) {
                                    $parentId = (int) $parentId;

                                    $invalidParentIds = $record->allChildCompanies()
                                        ->pluck('id')
                                        ->push($record->id);

                                    if ($invalidParentIds->contains($parentId)) {
                                        $action->halt();

                                        Notification::make()
                                            ->title('Cannot assign a descendant as the parent company.')
                                            ->danger()
                                            ->send();

                                        return;
                                    }
                                } else {
                                    $parentId = null;
                                }

                                $record->parent_company_id = $parentId;

                                $record->save();

                                $livewire = $action->getLivewire();

                                if (method_exists($livewire, 'refreshFormData')) {
                                    $livewire->refreshFormData([
                                        'parent_company_id',
                                        'parentCompany',
                                        'parentCompany.name',
                                    ]);
                                }

                                Notification::make()
                                    ->title('Parent company updated')
                                    ->success()
                                    ->send();
                            })
                    ),
                IconEntry::make('is_active')
                    ->boolean()
                    ->label('Is active')
                    ->afterLabel(
                        Action::make('toggleCompanyStatus')
                            ->label(fn (Company $record): string => $record->is_active ? 'Deactivate' : 'Activate')
                            ->icon(fn (Company $record): string => $record->is_active ? 'heroicon-m-x-mark' : 'heroicon-m-check')
                            ->color(fn (Company $record): string => $record->is_active ? 'danger' : 'success')
                            ->size(Size::Small)
                            ->requiresConfirmation()
                            ->modalHeading(fn (Company $record): string => $record->is_active ? 'Deactivate Company' : 'Activate Company')
                            ->modalDescription(fn (Company $record): string => $record->is_active
                                ? "Are you sure you want to deactivate '{$record->name}'?"
                                : "Are you sure you want to activate '{$record->name}'?")
                            ->action(function (Company $record, Action $action): void {
                                try {
                                    $company = ToggleCompanyStatus::run($record);
                                } catch (ValidationException $exception) {
                                    $action->halt();

                                    Notification::make()
                                        ->title(collect($exception->errors())->flatten()->first() ?? 'Unable to update the company status.')
                                        ->danger()
                                        ->send();

                                    return;
                                } catch (Throwable $exception) {
                                    $action->halt();

                                    report($exception);

                                    Notification::make()
                                        ->title('Failed to update the company status. Please try again later.')
                                        ->danger()
                                        ->send();

                                    return;
                                }

                                $livewire = $action->getLivewire();

                                if (method_exists($livewire, 'refreshFormData')) {
                                    $livewire->refreshFormData([
                                        'is_active',
                                    ]);
                                }

                                Notification::make()
                                    ->title($company->is_active
                                        ? "Company '{$company->name}' has been activated successfully."
                                        : "Company '{$company->name}' has been deactivated successfully.")
                                    ->success()
                                    ->send();
                            })
                    ),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('deleted_at')
                    ->dateTime()
                    ->visible(fn (Company $record): bool => $record->trashed()),
            ]);
    }
}
