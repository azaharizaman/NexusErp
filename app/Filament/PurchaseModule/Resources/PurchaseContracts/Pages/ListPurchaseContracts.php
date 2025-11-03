<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseContracts\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseContracts\PurchaseContractResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseContracts extends ListRecords
{
    protected static string $resource = PurchaseContractResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
