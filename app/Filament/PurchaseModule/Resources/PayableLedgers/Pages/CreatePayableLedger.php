<?php

namespace App\Filament\PurchaseModule\Resources\PayableLedgers\Pages;

use App\Filament\PurchaseModule\Resources\PayableLedgers\PayableLedgerResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayableLedger extends CreateRecord
{
    protected static string $resource = PayableLedgerResource::class;
}
