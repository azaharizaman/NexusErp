<?php

namespace App\Filament\PurchaseModule\Resources\Suppliers;

use App\Filament\PurchaseModule\Resources\Suppliers\Schemas\SupplierForm;
use App\Filament\PurchaseModule\Resources\Suppliers\Tables\SuppliersTable;
use App\Filament\Resources\BusinessPartners\RelationManagers\ContactsRelationManager;
use App\Filament\Resources\BusinessPartners\Schemas\BusinessPartnerInfolist;
use App\Models\BusinessPartner;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SupplierResource extends Resource
{
    protected static ?string $model = BusinessPartner::class;

    protected static ?string $modelLabel = 'Supplier';

    protected static ?string $pluralModelLabel = 'Suppliers';

    protected static ?string $navigationLabel = 'Suppliers';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static string|UnitEnum|null $navigationGroup = 'Procurement Setup';

    protected static ?string $slug = 'suppliers';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SupplierForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return BusinessPartnerInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SuppliersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ContactsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'view' => Pages\ViewSupplier::route('/{record}'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('is_supplier', true);
    }
}
