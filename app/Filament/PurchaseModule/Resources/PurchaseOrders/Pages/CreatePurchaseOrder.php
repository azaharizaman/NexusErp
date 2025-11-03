<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseOrders\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        // Calculate totals after creating the record
        $this->record->calculateTotals();
    }
}
