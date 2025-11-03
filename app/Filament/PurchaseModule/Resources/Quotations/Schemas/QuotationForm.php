<?php

namespace App\Filament\PurchaseModule\Resources\Quotations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class QuotationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quotation Details')
                    ->schema([
                        TextInput::make('quotation_number')
                            ->label('Quotation Number')
                            ->required()
                            ->disabled()
                            ->default(fn () => 'Auto-generated'),
                        Select::make('request_for_quotation_id')
                            ->relationship('requestForQuotation', 'rfq_number')
                            ->label('RFQ')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Select::make('business_partner_id')
                            ->relationship(
                                'supplier',
                                'name',
                                fn ($query) => $query->where('is_supplier', true)
                            )
                            ->label('Supplier')
                            ->required()
                            ->searchable()
                            ->preload(),
                        DatePicker::make('quotation_date')
                            ->label('Quotation Date')
                            ->required()
                            ->default(now()),
                        DatePicker::make('valid_until')
                            ->label('Valid Until')
                            ->after('quotation_date'),
                        Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->label('Currency')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(2),

                Section::make('Quotation Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                TextInput::make('item_description')
                                    ->label('Description')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('item_code')
                                    ->label('Code')
                                    ->columnSpan(1),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->columnSpan(1),
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->columnSpan(1),
                                TextInput::make('line_total')
                                    ->label('Line Total')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated()
                                    ->prefix('$')
                                    ->columnSpan(1),
                                TextInput::make('tax_rate')
                                    ->label('Tax %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->columnSpan(1),
                                Textarea::make('specifications')
                                    ->label('Specifications')
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['item_description'] ?? null),
                    ]),

                Section::make('Totals')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$'),
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$'),
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->prefix('$'),
                    ])
                    ->columns(3),

                Section::make('Additional Information')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'accepted' => 'Accepted',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft'),
                        TextInput::make('delivery_lead_time_days')
                            ->label('Delivery Lead Time (days)')
                            ->numeric()
                            ->suffix('days'),
                        Toggle::make('is_recommended')
                            ->label('Recommended Quotation')
                            ->default(false),
                        Textarea::make('payment_terms')
                            ->label('Payment Terms')
                            ->rows(2)
                            ->columnSpanFull(),
                        Textarea::make('terms_and_conditions')
                            ->label('Terms and Conditions')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(3),
            ]);
    }
}
