<?php

namespace App\Filament\PurchaseModule\Resources\RequestForQuotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RequestForQuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('RFQ Details')
                    ->schema([
                        TextInput::make('rfq_number')
                            ->label('RFQ Number')
                            ->required()
                            ->disabled()
                            ->default(fn () => 'Auto-generated'),
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Company')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('rfq_date')
                            ->label('RFQ Date')
                            ->required()
                            ->default(now()),
                        DatePicker::make('expiry_date')
                            ->label('Expiry Date')
                            ->required()
                            ->after('rfq_date'),
                        Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->label('Currency')
                            ->searchable()
                            ->preload(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'sent' => 'Sent',
                                'received' => 'Received',
                                'evaluated' => 'Evaluated',
                                'closed' => 'Closed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(2),

                Section::make('Purchase Requests')
                    ->schema([
                        Select::make('purchaseRequests')
                            ->relationship('purchaseRequests', 'pr_number')
                            ->label('Linked Purchase Requests')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Select one or more purchase requests to include in this RFQ'),
                    ]),

                Section::make('Invited Suppliers')
                    ->schema([
                        Select::make('invitedSuppliers')
                            ->relationship(
                                'invitedSuppliers',
                                'name',
                                fn ($query) => $query->where('is_supplier', true)
                            )
                            ->label('Suppliers to Invite')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Select suppliers to send this RFQ to'),
                    ]),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('terms_and_conditions')
                            ->label('Terms and Conditions')
                            ->rows(4)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
