<?php

namespace App\Filament\Pages;

use App\Settings\CompanySettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageCompanySettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static string $settings = CompanySettings::class;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Company Settings';

    protected static ?string $navigationLabel = 'Company';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('company_name')
                    ->required(),
                TextInput::make('company_registration_number'),
                TextInput::make('company_tax_number'),
                TextInput::make('company_phone')
                    ->tel(),
                TextInput::make('company_email')
                    ->email(),
                TextInput::make('company_website'),
                TextInput::make('company_address_line_1'),
                TextInput::make('company_address_line_2'),
                TextInput::make('company_city'),
                TextInput::make('company_state'),
                TextInput::make('company_postal_code'),
                TextInput::make('company_country'),
                TextInput::make('company_logo'),
                TextInput::make('company_description'),
            ]);
    }
}
