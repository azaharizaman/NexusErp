<?php

namespace App\Filament\PurchaseModule\Resources\TaxRules;

use App\Filament\PurchaseModule\Resources\TaxRules\Pages\CreateTaxRule;
use App\Filament\PurchaseModule\Resources\TaxRules\Pages\EditTaxRule;
use App\Filament\PurchaseModule\Resources\TaxRules\Pages\ListTaxRules;
use App\Filament\PurchaseModule\Resources\TaxRules\Schemas\TaxRuleForm;
use App\Filament\PurchaseModule\Resources\TaxRules\Tables\TaxRulesTable;
use App\Models\TaxRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TaxRuleResource extends Resource
{
    protected static ?string $model = TaxRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationGroup = 'Procurement Setup';

    public static function form(Schema $schema): Schema
    {
        return TaxRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaxRulesTable::configure($table);
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
            'index' => ListTaxRules::route('/'),
            'create' => CreateTaxRule::route('/create'),
            'edit' => EditTaxRule::route('/{record}/edit'),
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
