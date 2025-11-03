<?php

namespace App\Filament\PurchaseModule\Resources\ExchangeRates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class ExchangeRateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('from_currency_id')
                    ->relationship('fromCurrency', 'name')
                    ->required(),
                Select::make('to_currency_id')
                    ->relationship('toCurrency', 'name')
                    ->required(),
                TextInput::make('rate')
                    ->required()
                    ->numeric(),
                DatePicker::make('effective_date')
                    ->required(),
                DatePicker::make('expiry_date'),
                Toggle::make('is_active')
                    ->required(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
