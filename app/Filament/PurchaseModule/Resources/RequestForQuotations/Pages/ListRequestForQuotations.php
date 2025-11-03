<?php

namespace App\Filament\PurchaseModule\Resources\RequestForQuotations\Pages;

use App\Filament\PurchaseModule\Resources\RequestForQuotations\RequestForQuotationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRequestForQuotations extends ListRecords
{
    protected static string $resource = RequestForQuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
