<?php

namespace App\Filament\Resources\ModelStatuses;

use App\Filament\Resources\ModelStatuses\Pages\CreateModelStatus;
use App\Filament\Resources\ModelStatuses\Pages\EditModelStatus;
use App\Filament\Resources\ModelStatuses\Pages\ListModelStatuses;
use App\Filament\Resources\ModelStatuses\Pages\ViewModelStatus;
use App\Filament\Resources\ModelStatuses\Schemas\ModelStatusForm;
use App\Filament\Resources\ModelStatuses\Schemas\ModelStatusInfolist;
use App\Filament\Resources\ModelStatuses\Tables\ModelStatusesTable;
use App\Models\ModelStatus;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class ModelStatusResource extends Resource
{
    protected static ?string $model = ModelStatus::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-tag';

    protected static string | UnitEnum | null $navigationGroup = 'Status Management';

    protected static ?string $navigationLabel = 'Statuses';

    protected static ?string $modelLabel = 'Status';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ModelStatusForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return ModelStatusInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ModelStatusesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListModelStatuses::route('/'),
            'create' => CreateModelStatus::route('/create'),
            'view' => ViewModelStatus::route('/{record}'),
            'edit' => EditModelStatus::route('/{record}/edit'),
        ];
    }
}
