<?php

namespace App\Filament\PurchaseModule\Resources\PriceLists\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PriceListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                Select::make('currency_id')
                    ->relationship('currency', 'name')
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name'),
                DatePicker::make('effective_from')
                    ->required(),
                DatePicker::make('effective_to'),
                Toggle::make('is_active')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
            ]);
    }
}
