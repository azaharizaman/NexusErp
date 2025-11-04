<?php

namespace App\Filament\Resources\StatusTransitions\Pages;

use App\Filament\Resources\StatusTransitions\StatusTransitionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusTransition extends EditRecord
{
    protected static string $resource = StatusTransitionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
