<?php

namespace App\Filament\PurchaseModule\Resources\PaymentSchedules;

use App\Filament\PurchaseModule\Resources\PaymentSchedules\Pages\CreatePaymentSchedule;
use App\Filament\PurchaseModule\Resources\PaymentSchedules\Pages\EditPaymentSchedule;
use App\Filament\PurchaseModule\Resources\PaymentSchedules\Pages\ListPaymentSchedules;
use App\Filament\PurchaseModule\Resources\PaymentSchedules\Schemas\PaymentScheduleForm;
use App\Filament\PurchaseModule\Resources\PaymentSchedules\Tables\PaymentSchedulesTable;
use App\Models\PaymentSchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PaymentScheduleResource extends Resource
{
    protected static ?string $model = PaymentSchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationGroup = 'Payments & Settlements';

    protected static ?string $recordTitleAttribute = 'schedule_number';

    protected static ?string $navigationLabel = 'Payment Schedules';

    protected static ?string $modelLabel = 'Payment Schedule';

    protected static ?string $pluralModelLabel = 'Payment Schedules';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return PaymentScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PaymentSchedulesTable::configure($table);
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
            'index' => ListPaymentSchedules::route('/'),
            'create' => CreatePaymentSchedule::route('/create'),
            'edit' => EditPaymentSchedule::route('/{record}/edit'),
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
