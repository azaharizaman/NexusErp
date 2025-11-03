<?php

namespace App\Filament\PurchaseModule\Resources\TermsTemplates\Pages;

use App\Filament\PurchaseModule\Resources\TermsTemplates\TermsTemplateResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditTermsTemplate extends EditRecord
{
    protected static string $resource = TermsTemplateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
