<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pr_number')
                    ->required(),
                Select::make('requester_id')
                    ->relationship('requester', 'name')
                    ->required(),
                TextInput::make('department_id')
                    ->numeric(),
                Select::make('company_id')
                    ->relationship('company', 'name'),
                DatePicker::make('request_date')
                    ->required(),
                DatePicker::make('required_date'),
                TextInput::make('status')
                    ->required()
                    ->default('draft'),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('currency_id')
                    ->relationship('currency', 'name'),
                Textarea::make('purpose')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
                TextInput::make('approved_by')
                    ->numeric(),
                DateTimePicker::make('approved_at'),
                TextInput::make('rejected_by')
                    ->numeric(),
                DateTimePicker::make('rejected_at'),
            ]);
    }
}
