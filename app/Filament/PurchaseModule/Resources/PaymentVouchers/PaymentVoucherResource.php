<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers;

use App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages\CreatePaymentVoucher;
use App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages\EditPaymentVoucher;
use App\Filament\PurchaseModule\Resources\PaymentVouchers\Pages\ListPaymentVouchers;
use App\Filament\PurchaseModule\Resources\PaymentVouchers\Schemas\PaymentVoucherForm;
use App\Filament\PurchaseModule\Resources\PaymentVouchers\Tables\PaymentVouchersTable;
use App\Models\PaymentVoucher;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class PaymentVoucherResource extends Resource
{
    protected static ?string $model = PaymentVoucher::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Payments & Settlements';

    protected static ?string $recordTitleAttribute = 'voucher_number';

    protected static ?string $navigationLabel = 'Payment Vouchers';

    protected static ?string $modelLabel = 'Payment Voucher';

    protected static ?string $pluralModelLabel = 'Payment Vouchers';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return PaymentVoucherForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentVouchersTable::configure($table);
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
            'index' => ListPaymentVouchers::route('/'),
            'create' => CreatePaymentVoucher::route('/create'),
            'edit' => EditPaymentVoucher::route('/{record}/edit'),
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
