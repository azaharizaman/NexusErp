<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages;

use App\Filament\PurchaseModule\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentVoucher extends CreateRecord
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Calculate base amount after creating the record
        $this->record->calculateBaseAmount();
    }
}
