<?php

namespace App\Filament\PurchaseModule\Resources\Suppliers\Pages;

use App\Actions\BusinessPartner\UpdateBusinessPartner as UpdateBusinessPartnerAction;
use App\Filament\PurchaseModule\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions;
use Illuminate\Database\Eloquent\Model;

class EditSupplier extends EditRecord
{
    protected static string $resource = SupplierResource::class;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['is_supplier'] = true;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['is_supplier'] = true;

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return UpdateBusinessPartnerAction::run($record, $data);
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return 'Supplier updated successfully';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
