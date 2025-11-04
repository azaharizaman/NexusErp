<?php

namespace App\Filament\Resources\StatusTransitions\Pages;

use App\Filament\Resources\StatusTransitions\StatusTransitionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewStatusTransition extends ViewRecord
{
    protected static string $resource = StatusTransitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
