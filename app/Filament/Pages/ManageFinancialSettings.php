<?php

namespace App\Filament\Pages;

use App\Settings\FinancialSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageFinancialSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string $settings = FinancialSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Financial Settings';

    protected static ?string $navigationLabel = 'Financial';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('default_currency')
                    ->required(),
                TextInput::make('currency_symbol')
                    ->required(),
                TextInput::make('currency_position')
                    ->required(),
                TextInput::make('decimal_places')
                    ->numeric()
                    ->integer()
                    ->required(),
                TextInput::make('decimal_separator')
                    ->required(),
                TextInput::make('thousands_separator')
                    ->required(),
                TextInput::make('default_tax_rate')
                    ->numeric()
                    ->required(),
                Toggle::make('tax_inclusive_pricing')
                    ->required(),
                TextInput::make('financial_year_start')
                    ->required(),
                TextInput::make('invoice_prefix')
                    ->required(),
                TextInput::make('quote_prefix')
                    ->required(),
                TextInput::make('purchase_order_prefix')
                    ->required(),
                TextInput::make('invoice_number_length')
                    ->numeric()
                    ->integer()
                    ->required(),
                Toggle::make('auto_invoice_numbering')
                    ->required(),
            ]);
    }
}
