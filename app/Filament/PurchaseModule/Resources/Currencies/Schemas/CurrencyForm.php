<?php

namespace App\Filament\PurchaseModule\Resources\Currencies\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CurrencyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('symbol'),
                TextInput::make('decimal_places')
                    ->required()
                    ->numeric()
                    ->default(2),
                Toggle::make('is_active')
                    ->required(),
                Toggle::make('is_base')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
