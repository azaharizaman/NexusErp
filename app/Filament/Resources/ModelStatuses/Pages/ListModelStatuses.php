<?php

namespace App\Filament\Resources\ModelStatuses\Pages;

use App\Filament\Resources\ModelStatuses\ModelStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListModelStatuses extends ListRecords
{
    protected static string $resource = ModelStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
