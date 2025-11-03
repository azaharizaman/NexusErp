<?php

namespace App\Filament\PurchaseModule\Resources\ExchangeRates\Pages;

use App\Filament\PurchaseModule\Resources\ExchangeRates\ExchangeRateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExchangeRate extends CreateRecord
{
    protected static string $resource = ExchangeRateResource::class;
}
