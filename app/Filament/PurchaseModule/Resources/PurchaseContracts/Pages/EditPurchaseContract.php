<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseContracts\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseContracts\PurchaseContractResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseContract extends EditRecord
{
    protected static string $resource = PurchaseContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        // Recalculate remaining value if contract value changed
        if (isset($data['contract_value'])) {
            $data['remaining_value'] = $data['contract_value'] - ($this->record->utilized_value ?? 0);
        }

        return $data;
    }
}
