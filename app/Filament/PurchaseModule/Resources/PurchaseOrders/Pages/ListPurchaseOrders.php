<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseOrders\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseOrders extends ListRecords
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
