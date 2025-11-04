<?php

namespace App\Filament\Resources\ModelStatuses\Schemas;

use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ModelStatusForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Status Information')
                    ->schema([
                        Select::make('document_model_id')
                            ->relationship('documentModel', 'name')
                            ->required()
                            ->label('Document Model')
                            ->searchable()
                            ->preload(),

                        TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->label('Status Name')
                            ->helperText('Enter a unique status name (e.g., draft, submitted, approved)'),

                        ColorPicker::make('color')
                            ->label('Status Color')
                            ->default('gray')
                            ->helperText('Choose a color to represent this status in the UI'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535),
                    ])
                    ->columns(2),
            ]);
    }
}
