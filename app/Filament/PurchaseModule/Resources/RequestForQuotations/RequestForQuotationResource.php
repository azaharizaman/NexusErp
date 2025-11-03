<?php

namespace App\Filament\PurchaseModule\Resources\RequestForQuotations;

use App\Filament\PurchaseModule\Resources\RequestForQuotations\Pages\CreateRequestForQuotation;
use App\Filament\PurchaseModule\Resources\RequestForQuotations\Pages\EditRequestForQuotation;
use App\Filament\PurchaseModule\Resources\RequestForQuotations\Pages\ListRequestForQuotations;
use App\Filament\PurchaseModule\Resources\RequestForQuotations\Schemas\RequestForQuotationForm;
use App\Filament\PurchaseModule\Resources\RequestForQuotations\Tables\RequestForQuotationsTable;
use App\Models\RequestForQuotation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class RequestForQuotationResource extends Resource
{
    protected static ?string $model = RequestForQuotation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Requisition Management';

    protected static ?string $recordTitleAttribute = 'rfq_number';

    protected static ?string $navigationLabel = 'Request for Quotations';

    protected static ?string $modelLabel = 'Request for Quotation';

    protected static ?string $pluralModelLabel = 'Requests for Quotations';

    public static function form(Schema $schema): Schema
    {
        return RequestForQuotationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RequestForQuotationsTable::configure($table);
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
            'index' => ListRequestForQuotations::route('/'),
            'create' => CreateRequestForQuotation::route('/create'),
            'edit' => EditRequestForQuotation::route('/{record}/edit'),
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
