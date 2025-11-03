<?php

namespace App\Filament\Resources\BusinessPartners\Pages;

use App\Actions\BusinessPartner\UpdateBusinessPartner as UpdateBusinessPartnerAction;
use App\Filament\Resources\BusinessPartners\BusinessPartnerResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditBusinessPartner extends EditRecord
{
    protected static string $resource = BusinessPartnerResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return UpdateBusinessPartnerAction::run($record, $data);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Business partner updated successfully';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
