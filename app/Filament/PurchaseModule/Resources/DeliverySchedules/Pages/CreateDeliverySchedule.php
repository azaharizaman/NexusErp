<?php

namespace App\Filament\PurchaseModule\Resources\DeliverySchedules\Pages;

use App\Filament\PurchaseModule\Resources\DeliverySchedules\DeliveryScheduleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDeliverySchedule extends CreateRecord
{
    protected static string $resource = DeliveryScheduleResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        $data['status'] = $data['status'] ?? 'scheduled';

        // Calculate remaining quantity
        $data['remaining_quantity'] = ($data['scheduled_quantity'] ?? 0) - ($data['delivered_quantity'] ?? 0);

        return $data;
    }
}
