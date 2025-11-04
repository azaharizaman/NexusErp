<?php

namespace App\Filament\Resources\ModelStatuses\Pages;

use App\Filament\Resources\ModelStatuses\ModelStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewModelStatus extends ViewRecord
{
    protected static string $resource = ModelStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
