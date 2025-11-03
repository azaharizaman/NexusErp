<?php

namespace App\Filament\Pages;

use App\Settings\UomSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageUomSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedScale;

    protected static string $settings = UomSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'UOM Settings';

    protected static ?string $navigationLabel = 'Units of Measure';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('default_weight_unit')
                    ->required(),
                TextInput::make('default_length_unit')
                    ->required(),
                TextInput::make('default_volume_unit')
                    ->required(),
                TextInput::make('default_area_unit')
                    ->required(),
                TextInput::make('default_temperature_unit')
                    ->required(),
                Toggle::make('enable_compound_units')
                    ->required(),
                Toggle::make('enable_custom_units')
                    ->required(),
                Toggle::make('auto_convert_units')
                    ->required(),
                TextInput::make('conversion_precision')
                    ->numeric()
                    ->integer()
                    ->required(),
                Toggle::make('show_unit_codes')
                    ->required(),
                Toggle::make('show_unit_names')
                    ->required(),
            ]);
    }
}
