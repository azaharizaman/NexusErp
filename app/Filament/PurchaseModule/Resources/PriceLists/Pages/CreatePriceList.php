<?php

namespace App\Filament\PurchaseModule\Resources\PriceLists\Pages;

use App\Filament\PurchaseModule\Resources\PriceLists\PriceListResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePriceList extends CreateRecord
{
    protected static string $resource = PriceListResource::class;
}
