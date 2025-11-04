<?php

namespace App\Filament\PurchaseModule\Resources\PayableLedgers\Pages;

use App\Filament\PurchaseModule\Resources\PayableLedgers\PayableLedgerResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditPayableLedger extends EditRecord
{
    protected static string $resource = PayableLedgerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
