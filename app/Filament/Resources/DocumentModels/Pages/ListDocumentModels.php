<?php

namespace App\Filament\Resources\DocumentModels\Pages;

use App\Filament\Resources\DocumentModels\DocumentModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDocumentModels extends ListRecords
{
    protected static string $resource = DocumentModelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
