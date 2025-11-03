<?php

namespace App\Filament\PurchaseModule\Resources\DeliverySchedules;

use App\Filament\PurchaseModule\Resources\DeliverySchedules\Pages\CreateDeliverySchedule;
use App\Filament\PurchaseModule\Resources\DeliverySchedules\Pages\EditDeliverySchedule;
use App\Filament\PurchaseModule\Resources\DeliverySchedules\Pages\ListDeliverySchedules;
use App\Filament\PurchaseModule\Resources\DeliverySchedules\Schemas\DeliveryScheduleForm;
use App\Filament\PurchaseModule\Resources\DeliverySchedules\Tables\DeliverySchedulesTable;
use App\Models\DeliverySchedule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DeliveryScheduleResource extends Resource
{
    protected static ?string $model = DeliverySchedule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Sourcing & Ordering';

    protected static ?string $recordTitleAttribute = 'schedule_number';

    protected static ?string $navigationLabel = 'Delivery Schedules';

    protected static ?string $modelLabel = 'Delivery Schedule';

    protected static ?string $pluralModelLabel = 'Delivery Schedules';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return DeliveryScheduleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeliverySchedulesTable::configure($table);
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
            'index' => ListDeliverySchedules::route('/'),
            'create' => CreateDeliverySchedule::route('/create'),
            'edit' => EditDeliverySchedule::route('/{record}/edit'),
        ];
    }
}
