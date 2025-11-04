<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentVoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Voucher Details')
                    ->schema([
                        TextInput::make('voucher_number')
                            ->label('Voucher Number')
                            ->required()
                            ->disabled()
                            ->default(fn () => 'Auto-generated on save')
                            ->dehydrated(false),
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
                        Select::make('supplier_invoice_id')
                            ->relationship('supplierInvoice', 'invoice_number')
                            ->label('Supplier Invoice')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->label('Currency')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->default(fn () => \App\Models\Currency::where('is_base', true)->first()?->id),
                        DatePicker::make('payment_date')
                            ->label('Payment Date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => \App\Models\Currency::find($get('currency_id'))?->symbol ?? '$')
                            ->step('0.01'),
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'cash' => 'Cash',
                                'cheque' => 'Cheque',
                                'credit_card' => 'Credit Card',
                                'wire_transfer' => 'Wire Transfer',
                                'other' => 'Other',
                            ])
                            ->native(false),
                        TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Payment Details')
                    ->schema([
                        TextInput::make('bank_name')
                            ->label('Bank Name')
                            ->maxLength(255),
                        TextInput::make('bank_account_number')
                            ->label('Bank Account Number')
                            ->maxLength(255),
                        TextInput::make('cheque_number')
                            ->label('Cheque Number')
                            ->maxLength(255),
                        TextInput::make('transaction_id')
                            ->label('Transaction ID')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Description & Notes')
                    ->schema([
                        Textarea::make('description')
                            ->label('Description')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
