<?php

namespace App\Filament\PurchaseModule\Resources\Currencies\Pages;

use App\Filament\PurchaseModule\Resources\Currencies\CurrencyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCurrency extends CreateRecord
{
    protected static string $resource = CurrencyResource::class;
}
