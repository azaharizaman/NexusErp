<?php

namespace App\Filament\Resources\ModelStatuses\Pages;

use App\Filament\Resources\ModelStatuses\ModelStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditModelStatus extends EditRecord
{
    protected static string $resource = ModelStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
