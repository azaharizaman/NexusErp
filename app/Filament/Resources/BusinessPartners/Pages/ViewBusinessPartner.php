<?php

namespace App\Filament\Resources\BusinessPartners\Pages;

use App\Filament\Resources\BusinessPartners\BusinessPartnerResource;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewBusinessPartner extends ViewRecord
{
    protected static string $resource = BusinessPartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
