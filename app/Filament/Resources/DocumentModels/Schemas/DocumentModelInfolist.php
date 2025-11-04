<?php

namespace App\Filament\Resources\DocumentModels\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class DocumentModelInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Document Model Information')
                    ->schema([
                        TextEntry::make('name')
                            ->label('Model Name'),

                        TextEntry::make('model_class')
                            ->label('Model Class'),

                        TextEntry::make('description')
                            ->label('Description'),

                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime(),
                    ])
                    ->columns(2),
            ]);
    }
}
