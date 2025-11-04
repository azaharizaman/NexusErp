<?php

namespace App\Filament\Resources\DocumentModels\Pages;

use App\Filament\Resources\DocumentModels\DocumentModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDocumentModel extends EditRecord
{
    protected static string $resource = DocumentModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
