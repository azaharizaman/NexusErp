<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\PurchaseRecommendationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchaseRecommendations extends ListRecords
{
    protected static string $resource = PurchaseRecommendationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
