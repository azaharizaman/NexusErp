<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRecommendations;

use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Pages\CreatePurchaseRecommendation;
use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Pages\EditPurchaseRecommendation;
use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Pages\ListPurchaseRecommendations;
use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Schemas\PurchaseRecommendationForm;
use App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Tables\PurchaseRecommendationsTable;
use App\Models\PurchaseRecommendation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PurchaseRecommendationResource extends Resource
{
    protected static ?string $model = PurchaseRecommendation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Requisition Management';

    protected static ?string $recordTitleAttribute = 'recommendation_number';

    protected static ?string $navigationLabel = 'Purchase Recommendations';

    protected static ?string $modelLabel = 'Purchase Recommendation';

    protected static ?string $pluralModelLabel = 'Purchase Recommendations';

    public static function form(Schema $schema): Schema
    {
        return PurchaseRecommendationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseRecommendationsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseRecommendations::route('/'),
            'create' => CreatePurchaseRecommendation::route('/create'),
            'edit' => EditPurchaseRecommendation::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
