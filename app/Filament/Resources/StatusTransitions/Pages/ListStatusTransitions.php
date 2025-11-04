<?php

namespace App\Filament\Resources\StatusTransitions\Pages;

use App\Filament\Resources\StatusTransitions\StatusTransitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStatusTransitions extends ListRecords
{
    protected static string $resource = StatusTransitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
