<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Actions\Company\DeleteCompany as DeleteCompanyAction;
use App\Actions\Company\UpdateCompany as UpdateCompanyAction;
use App\Filament\Resources\Companies\CompanyResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCompany extends EditRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * Handle record update using Laravel Action
     */
    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Use our Laravel Action to update the company
        return UpdateCompanyAction::run($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->using(function (\Illuminate\Database\Eloquent\Model $record) {
                    // Use our Laravel Action to delete the company
                    return DeleteCompanyAction::run($record);
                }),
            ForceDeleteAction::make()
                ->using(function (\Illuminate\Database\Eloquent\Model $record) {
                    // Use our Laravel Action to force delete the company
                    return DeleteCompanyAction::run($record, true);
                }),
            RestoreAction::make(),
        ];
    }

    /**
     * Customize success notification
     */
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Company updated successfully';
    }
}
