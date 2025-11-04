<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages;

use App\Filament\PurchaseModule\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentVouchers extends ListRecords
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
