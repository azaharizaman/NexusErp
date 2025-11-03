<?php

namespace App\Filament\PurchaseModule\Resources\DeliverySchedules\Pages;

use App\Filament\PurchaseModule\Resources\DeliverySchedules\DeliveryScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDeliverySchedules extends ListRecords
{
    protected static string $resource = DeliveryScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
