<?php

namespace App\Filament\PurchaseModule\Resources\TermsTemplates\Pages;

use App\Filament\PurchaseModule\Resources\TermsTemplates\TermsTemplateResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTermsTemplates extends ListRecords
{
    protected static string $resource = TermsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
