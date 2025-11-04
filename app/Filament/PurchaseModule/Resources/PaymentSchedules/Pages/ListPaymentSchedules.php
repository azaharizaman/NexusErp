<?php

namespace App\Filament\PurchaseModule\Resources\PaymentSchedules\Pages;

use App\Filament\PurchaseModule\Resources\PaymentSchedules\PaymentScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPaymentSchedules extends ListRecords
{
    protected static string $resource = PaymentScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
