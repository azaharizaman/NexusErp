<?php

namespace App\Filament\PurchaseModule\Resources\DeliverySchedules\Pages;

use App\Filament\PurchaseModule\Resources\DeliverySchedules\DeliveryScheduleResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDeliverySchedule extends EditRecord
{
    protected static string $resource = DeliveryScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['updated_by'] = auth()->id();

        // Recalculate remaining quantity
        $data['remaining_quantity'] = ($data['scheduled_quantity'] ?? 0) - ($data['delivered_quantity'] ?? 0);

        return $data;
    }
}
