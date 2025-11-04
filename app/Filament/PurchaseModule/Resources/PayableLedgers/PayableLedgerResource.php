<?php

namespace App\Filament\PurchaseModule\Resources\PayableLedgers;

use App\Filament\PurchaseModule\Resources\PayableLedgers\Pages\CreatePayableLedger;
use App\Filament\PurchaseModule\Resources\PayableLedgers\Pages\EditPayableLedger;
use App\Filament\PurchaseModule\Resources\PayableLedgers\Pages\ListPayableLedgers;
use App\Filament\PurchaseModule\Resources\PayableLedgers\Schemas\PayableLedgerForm;
use App\Filament\PurchaseModule\Resources\PayableLedgers\Tables\PayableLedgersTable;
use App\Models\PayableLedger;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PayableLedgerResource extends Resource
{
    protected static ?string $model = PayableLedger::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $navigationGroup = 'Payments & Settlements';

    protected static ?string $navigationLabel = 'Payable Ledger';

    protected static ?string $modelLabel = 'Payable Ledger Entry';

    protected static ?string $pluralModelLabel = 'Payable Ledger';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return PayableLedgerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PayableLedgersTable::configure($table);
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
            'index' => ListPayableLedgers::route('/'),
            'create' => CreatePayableLedger::route('/create'),
            'edit' => EditPayableLedger::route('/{record}/edit'),
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
