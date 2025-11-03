<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseContracts;

use App\Filament\PurchaseModule\Resources\PurchaseContracts\Pages\CreatePurchaseContract;
use App\Filament\PurchaseModule\Resources\PurchaseContracts\Pages\EditPurchaseContract;
use App\Filament\PurchaseModule\Resources\PurchaseContracts\Pages\ListPurchaseContracts;
use App\Filament\PurchaseModule\Resources\PurchaseContracts\Schemas\PurchaseContractForm;
use App\Filament\PurchaseModule\Resources\PurchaseContracts\Tables\PurchaseContractsTable;
use App\Models\PurchaseContract;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PurchaseContractResource extends Resource
{
    protected static ?string $model = PurchaseContract::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Sourcing & Ordering';

    protected static ?string $recordTitleAttribute = 'contract_number';

    protected static ?string $navigationLabel = 'Purchase Contracts';

    protected static ?string $modelLabel = 'Purchase Contract';

    protected static ?string $pluralModelLabel = 'Purchase Contracts';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PurchaseContractForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseContractsTable::configure($table);
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
            'index' => ListPurchaseContracts::route('/'),
            'create' => CreatePurchaseContract::route('/create'),
            'edit' => EditPurchaseContract::route('/{record}/edit'),
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
