<?php

namespace App\Filament\Resources\DocumentModels\Schemas;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Document Model Information')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Model Name')
                            ->helperText('Human-readable name for this document model'),

                        TextInput::make('model_class')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->label('Model Class')
                            ->helperText('Full namespace of the model class (e.g., App\Models\PurchaseOrder)')
                            ->placeholder('App\Models\YourModel'),

                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->maxLength(65535),
                    ])
                    ->columns(1),
            ]);
    }
}
