<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRequests;

use App\Filament\PurchaseModule\Resources\PurchaseRequests\Pages\CreatePurchaseRequest;
use App\Filament\PurchaseModule\Resources\PurchaseRequests\Pages\EditPurchaseRequest;
use App\Filament\PurchaseModule\Resources\PurchaseRequests\Pages\ListPurchaseRequests;
use App\Filament\PurchaseModule\Resources\PurchaseRequests\Schemas\PurchaseRequestForm;
use App\Filament\PurchaseModule\Resources\PurchaseRequests\Tables\PurchaseRequestsTable;
use App\Models\PurchaseRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Requisition Management';

    protected static ?string $recordTitleAttribute = 'pr_number';

    public static function form(Schema $schema): Schema
    {
        return PurchaseRequestForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PurchaseRequestsTable::configure($table);
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
            'index' => ListPurchaseRequests::route('/'),
            'create' => CreatePurchaseRequest::route('/create'),
            'edit' => EditPurchaseRequest::route('/{record}/edit'),
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
