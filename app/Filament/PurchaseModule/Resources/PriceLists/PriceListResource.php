<?php

namespace App\Filament\PurchaseModule\Resources\PriceLists;

use App\Filament\PurchaseModule\Resources\PriceLists\Pages\CreatePriceList;
use App\Filament\PurchaseModule\Resources\PriceLists\Pages\EditPriceList;
use App\Filament\PurchaseModule\Resources\PriceLists\Pages\ListPriceLists;
use App\Filament\PurchaseModule\Resources\PriceLists\Schemas\PriceListForm;
use App\Filament\PurchaseModule\Resources\PriceLists\Tables\PriceListsTable;
use App\Models\PriceList;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Procurement Setup';

    public static function form(Schema $schema): Schema
    {
        return PriceListForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceListsTable::configure($table);
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
            'index' => ListPriceLists::route('/'),
            'create' => CreatePriceList::route('/create'),
            'edit' => EditPriceList::route('/{record}/edit'),
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
