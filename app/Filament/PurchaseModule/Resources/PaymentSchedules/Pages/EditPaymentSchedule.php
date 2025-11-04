<?php

namespace App\Filament\PurchaseModule\Resources\PaymentSchedules\Pages;

use App\Filament\PurchaseModule\Resources\PaymentSchedules\PaymentScheduleResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPaymentSchedule extends EditRecord
{
    protected static string $resource = PaymentScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
