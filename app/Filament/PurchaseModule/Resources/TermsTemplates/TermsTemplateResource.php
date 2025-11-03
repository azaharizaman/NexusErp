<?php

namespace App\Filament\PurchaseModule\Resources\TermsTemplates;

use App\Filament\PurchaseModule\Resources\TermsTemplates\Pages\CreateTermsTemplate;
use App\Filament\PurchaseModule\Resources\TermsTemplates\Pages\EditTermsTemplate;
use App\Filament\PurchaseModule\Resources\TermsTemplates\Pages\ListTermsTemplates;
use App\Filament\PurchaseModule\Resources\TermsTemplates\Schemas\TermsTemplateForm;
use App\Filament\PurchaseModule\Resources\TermsTemplates\Tables\TermsTemplatesTable;
use App\Models\TermsTemplate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TermsTemplateResource extends Resource
{
    protected static ?string $model = TermsTemplate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationGroup = 'Procurement Setup';

    public static function form(Schema $schema): Schema
    {
        return TermsTemplateForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TermsTemplatesTable::configure($table);
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
            'index' => ListTermsTemplates::route('/'),
            'create' => CreateTermsTemplate::route('/create'),
            'edit' => EditTermsTemplate::route('/{record}/edit'),
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
