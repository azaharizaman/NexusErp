<?php

namespace App\Filament\PurchaseModule\Resources\Quotations\Pages;

use App\Filament\PurchaseModule\Resources\Quotations\QuotationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateQuotation extends CreateRecord
{
    protected static string $resource = QuotationResource::class;
}
