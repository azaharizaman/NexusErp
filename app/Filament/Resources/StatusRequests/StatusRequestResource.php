<?php

namespace App\Filament\Resources\StatusRequests;

use App\Filament\Resources\StatusRequests\Pages\ListStatusRequests;
use App\Filament\Resources\StatusRequests\Pages\ViewStatusRequest;
use App\Filament\Resources\StatusRequests\Schemas\StatusRequestInfolist;
use App\Filament\Resources\StatusRequests\Tables\StatusRequestsTable;
use App\Models\StatusRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class StatusRequestResource extends Resource
{
    protected static ?string $model = StatusRequest::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-check';

    protected static string | \UnitEnum | null $navigationGroup = 'Status Management';

    protected static ?string $navigationLabel = 'Approval Requests';

    protected static ?string $modelLabel = 'Status Request';

    protected static ?int $navigationSort = 4;

    public static function infolist(Schema $schema): Schema
    {
        return StatusRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StatusRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStatusRequests::route('/'),
            'view' => ViewStatusRequest::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
