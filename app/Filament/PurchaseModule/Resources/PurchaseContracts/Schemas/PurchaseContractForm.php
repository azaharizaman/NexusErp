<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseContracts\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseContractForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Contract Details')
                    ->schema([
                        TextInput::make('contract_number')
                            ->label('Contract Number')
                            ->required()
                            ->disabled()
                            ->default(fn () => 'Auto-generated on save')
                            ->dehydrated(false),
                        TextInput::make('contract_name')
                            ->label('Contract Name')
                            ->required()
                            ->maxLength(255),
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Company')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('supplier_id')
                            ->relationship(
                                'supplier',
                                'name',
                                fn ($query) => $query->where('is_supplier', true)
                            )
                            ->label('Supplier')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->label('Currency')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('contract_type')
                            ->options([
                                'blanket' => 'Blanket Order',
                                'framework' => 'Framework Agreement',
                                'long_term' => 'Long-term Contract',
                            ])
                            ->required()
                            ->default('blanket')
                            ->native(false),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'active' => 'Active',
                                'expired' => 'Expired',
                                'terminated' => 'Terminated',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Contract Period')
                    ->schema([
                        DatePicker::make('start_date')
                            ->label('Start Date')
                            ->required()
                            ->native(false),
                        DatePicker::make('end_date')
                            ->label('End Date')
                            ->required()
                            ->after('start_date')
                            ->native(false),
                        DatePicker::make('renewal_date')
                            ->label('Renewal Date')
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Contract Value')
                    ->schema([
                        TextInput::make('contract_value')
                            ->label('Contract Value')
                            ->numeric()
                            ->prefix('$')
                            ->helperText('Total contract value (optional, leave empty for no limit)'),
                        TextInput::make('utilized_value')
                            ->label('Utilized Value')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$')
                            ->default(0),
                        TextInput::make('remaining_value')
                            ->label('Remaining Value')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$'),
                    ])
                    ->columns(3),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        RichEditor::make('terms_and_conditions')
                            ->label('Terms and Conditions')
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
