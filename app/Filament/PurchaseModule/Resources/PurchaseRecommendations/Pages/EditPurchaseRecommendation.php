<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\PurchaseRecommendationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRecommendation extends EditRecord
{
    protected static string $resource = PurchaseRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }
}
