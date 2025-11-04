<?php

namespace App\Filament\Resources\ModelStatuses\Schemas;

use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class ModelStatusInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Status Information')
                    ->schema([
                        TextEntry::make('documentModel.name')
                            ->label('Document Model'),

                        TextEntry::make('name')
                            ->label('Status Name'),

                        ColorEntry::make('color')
                            ->label('Color'),

                        TextEntry::make('description')
                            ->label('Description'),

                        TextEntry::make('sort_order')
                            ->label('Sort Order'),

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
