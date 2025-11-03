<?php

namespace App\Filament\PurchaseModule\Resources\Suppliers\Pages;

use App\Actions\BusinessPartner\CreateBusinessPartner as CreateBusinessPartnerAction;
use App\Filament\PurchaseModule\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['is_supplier'] = true;

        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return CreateBusinessPartnerAction::run($data);
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Supplier created successfully';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }
}
