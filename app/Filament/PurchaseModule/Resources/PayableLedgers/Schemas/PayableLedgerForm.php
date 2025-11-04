<?php

namespace App\Filament\PurchaseModule\Resources\PayableLedgers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PayableLedgerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                Select::make('supplier_id')
                    ->relationship('supplier', 'name')
                    ->required(),
                Select::make('supplier_invoice_id')
                    ->relationship('supplierInvoice', 'id'),
                Select::make('payment_voucher_id')
                    ->relationship('paymentVoucher', 'id'),
                Select::make('base_currency_id')
                    ->relationship('baseCurrency', 'name')
                    ->required(),
                Select::make('foreign_currency_id')
                    ->relationship('foreignCurrency', 'name'),
                DatePicker::make('transaction_date')
                    ->required(),
                TextInput::make('transaction_type')
                    ->required(),
                TextInput::make('debit_amount_base')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('credit_amount_base')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('debit_amount_foreign')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('credit_amount_foreign')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('exchange_rate')
                    ->numeric(),
                DatePicker::make('exchange_rate_date'),
                TextInput::make('balance_base')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('balance_foreign')
                    ->required()
                    ->numeric()
                    ->default(0),
                TextInput::make('reference_number'),
                Textarea::make('description')
                    ->columnSpanFull(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                TextInput::make('created_by')
                    ->numeric(),
                TextInput::make('updated_by')
                    ->numeric(),
            ]);
    }
}
