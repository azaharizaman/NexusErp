<?php

namespace App\Filament\Resources\BusinessPartners\Pages;

use App\Actions\BusinessPartner\CreateBusinessPartner as CreateBusinessPartnerAction;
use App\Filament\Resources\BusinessPartners\BusinessPartnerResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBusinessPartner extends CreateRecord
{
    protected static string $resource = BusinessPartnerResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return CreateBusinessPartnerAction::run($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Business partner created successfully';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
