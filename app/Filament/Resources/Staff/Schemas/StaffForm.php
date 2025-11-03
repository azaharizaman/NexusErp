<?php

namespace App\Filament\Resources\Staff\Schemas;

use AzahariZaman\BackOffice\Enums\StaffStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StaffForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('employee_id')
                    ->required(),
                TextInput::make('first_name')
                    ->required(),
                TextInput::make('last_name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->email(),
                TextInput::make('phone')
                    ->tel(),
                Select::make('office_id')
                    ->relationship('office', 'name'),
                Select::make('department_id')
                    ->relationship('department', 'name'),
                Select::make('position_id')
                    ->relationship('position', 'name'),
                DatePicker::make('hire_date'),
                DatePicker::make('resignation_date'),
                Textarea::make('resignation_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('resigned_at'),
                Select::make('status')
                    ->options(StaffStatus::class)
                    ->default('active')
                    ->required(),
                Select::make('supervisor_id')
                    ->relationship('supervisor', 'id'),
                Toggle::make('is_active')
                    ->required(),
            ]);
    }
}
