<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRequests\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseRequests\PurchaseRequestResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;
}
