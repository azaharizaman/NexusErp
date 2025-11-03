<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages;

use App\Filament\PurchaseModule\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPaymentVoucher extends EditRecord
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Ensure voucher_number is shown when editing
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        return $data;
    }

    protected function afterSave(): void
    {
        // Recalculate base amount after saving
        $this->record->calculateBaseAmount();
    }
}
