<?php

namespace App\Filament\Resources\DocumentModels;

use App\Filament\Resources\DocumentModels\Pages\CreateDocumentModel;
use App\Filament\Resources\DocumentModels\Pages\EditDocumentModel;
use App\Filament\Resources\DocumentModels\Pages\ListDocumentModels;
use App\Filament\Resources\DocumentModels\Pages\ViewDocumentModel;
use App\Filament\Resources\DocumentModels\Schemas\DocumentModelForm;
use App\Filament\Resources\DocumentModels\Schemas\DocumentModelInfolist;
use App\Filament\Resources\DocumentModels\Tables\DocumentModelsTable;
use App\Models\DocumentModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use UnitEnum;

class DocumentModelResource extends Resource
{
    protected static ?string $model = DocumentModel::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-text';

    protected static string | \UnitEnum | null $navigationGroup = 'Status Management';

    protected static ?string $navigationLabel = 'Document Models';

    protected static ?string $modelLabel = 'Document Model';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return DocumentModelForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return DocumentModelInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentModelsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentModels::route('/'),
            'create' => CreateDocumentModel::route('/create'),
            'view' => ViewDocumentModel::route('/{record}'),
            'edit' => EditDocumentModel::route('/{record}/edit'),
        ];
    }
}
