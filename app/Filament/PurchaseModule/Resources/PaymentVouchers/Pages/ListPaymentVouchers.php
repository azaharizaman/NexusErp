<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages;

use App\Filament\PurchaseModule\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPaymentVouchers extends ListRecords
{
    protected static string $resource = PaymentVoucherResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
