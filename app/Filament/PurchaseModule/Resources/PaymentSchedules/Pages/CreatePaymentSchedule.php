<?php

namespace App\Filament\PurchaseModule\Resources\PaymentSchedules\Pages;

use App\Filament\PurchaseModule\Resources\PaymentSchedules\PaymentScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePaymentSchedule extends CreateRecord
{
    protected static string $resource = PaymentScheduleResource::class;
}
