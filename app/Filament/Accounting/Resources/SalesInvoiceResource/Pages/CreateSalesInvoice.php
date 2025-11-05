<?php

namespace App\Filament\Accounting\Resources\SalesInvoiceResource\Pages;

use App\Filament\Accounting\Resources\SalesInvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSalesInvoice extends CreateRecord
{
    protected static string $resource = SalesInvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['status'] = 'draft';
        $data['created_by'] = auth()->id();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        // Calculate totals after invoice is created
        $this->record->calculateTotals();
    }
}
