<?php

namespace App\Filament\Pages;

use App\Settings\GeneralSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageGeneralSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string $settings = GeneralSettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'General Settings';

    protected static ?string $navigationLabel = 'General';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('app_name')
                    ->required(),
                TextInput::make('app_description')
                    ->required(),
                TextInput::make('app_logo')
                    ->required(),
                TextInput::make('app_favicon')
                    ->required(),
                TextInput::make('timezone')
                    ->required(),
                TextInput::make('date_format')
                    ->required(),
                TextInput::make('time_format')
                    ->required(),
                TextInput::make('datetime_format')
                    ->required(),
                TextInput::make('default_language')
                    ->required(),
                Toggle::make('maintenance_mode')
                    ->required(),
                TextInput::make('maintenance_message'),
            ]);
    }
}
