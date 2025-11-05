<?php

namespace App\Filament\Accounting\Resources\Accounts\Pages;

use App\Filament\Accounting\Resources\Accounts\AccountResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewAccount extends ViewRecord
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
