<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseContracts\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseContracts\PurchaseContractResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseContract extends CreateRecord
{
    protected static string $resource = PurchaseContractResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'draft';

        // Calculate remaining value if contract value is set
        if (isset($data['contract_value'])) {
            $data['remaining_value'] = $data['contract_value'] - ($data['utilized_value'] ?? 0);
        }

        return $data;
    }
}
