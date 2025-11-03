<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Pages;

use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\PurchaseRecommendationResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseRecommendation extends CreateRecord
{
    protected static string $resource = PurchaseRecommendationResource::class;
}
