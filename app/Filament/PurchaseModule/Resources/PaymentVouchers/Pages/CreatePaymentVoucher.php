<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages;

use App\Filament\PurchaseModule\Resources\PaymentVouchers\PaymentVoucherResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentVoucher extends CreateRecord
{
    protected static string $resource = PaymentVoucherResource::class;
}
