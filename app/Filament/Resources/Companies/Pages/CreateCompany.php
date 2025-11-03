<?php

namespace App\Filament\Resources\Companies\Pages;

use App\Actions\Company\CreateCompany as CreateCompanyAction;
use App\Filament\Resources\Companies\CompanyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompany extends CreateRecord
{
    protected static string $resource = CompanyResource::class;

    /**
     * Handle record creation using Laravel Action
     */
    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Use our Laravel Action to create the company
        return CreateCompanyAction::run($data);
    }

    /**
     * Customize success notification
     */
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Company created successfully';
    }

    /**
     * Redirect after creation
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
