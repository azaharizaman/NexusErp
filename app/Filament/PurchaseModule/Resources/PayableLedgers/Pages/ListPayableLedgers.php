<?php

namespace App\Filament\PurchaseModule\Resources\PayableLedgers\Pages;

use App\Filament\PurchaseModule\Resources\PayableLedgers\PayableLedgerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPayableLedgers extends ListRecords
{
    protected static string $resource = PayableLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
