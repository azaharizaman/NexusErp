<?php

namespace App\Filament\Resources\StatusTransitions;

use App\Filament\Resources\StatusTransitions\Pages\CreateStatusTransition;
use App\Filament\Resources\StatusTransitions\Pages\EditStatusTransition;
use App\Filament\Resources\StatusTransitions\Pages\ListStatusTransitions;
use App\Filament\Resources\StatusTransitions\Pages\ViewStatusTransition;
use App\Filament\Resources\StatusTransitions\Schemas\StatusTransitionForm;
use App\Filament\Resources\StatusTransitions\Schemas\StatusTransitionInfolist;
use App\Filament\Resources\StatusTransitions\Tables\StatusTransitionsTable;
use App\Models\StatusTransition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StatusTransitionResource extends Resource
{
    protected static ?string $model = StatusTransition::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-arrows-right-left';

    protected static string | UnitEnum | null $navigationGroup = 'Status Management';

    protected static ?string $navigationLabel = 'Status Transitions';

    protected static ?string $modelLabel = 'Status Transition';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return StatusTransitionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StatusTransitionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatusTransitionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatusTransitions::route('/'),
            'create' => CreateStatusTransition::route('/create'),
            'view' => ViewStatusTransition::route('/{record}'),
            'edit' => EditStatusTransition::route('/{record}/edit'),
        ];
    }
}
