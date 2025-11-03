<?php

namespace App\Filament\PurchaseModule\Resources\PaymentVouchers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
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
                        Select::make('purchase_order_id')
                            ->relationship('purchaseOrder', 'po_number')
                            ->label('Purchase Order')
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
                        DatePicker::make('value_date')
                            ->label('Value Date')
                            ->native(false),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'pending_approval' => 'Pending Approval',
                                'approved' => 'Approved',
                                'paid' => 'Paid',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Payment Details')
                    ->schema([
                        Select::make('payment_method')
                            ->label('Payment Method')
                            ->options([
                                'bank_transfer' => 'Bank Transfer',
                                'check' => 'Check',
                                'cash' => 'Cash',
                                'credit_card' => 'Credit Card',
                                'debit_card' => 'Debit Card',
                                'online_payment' => 'Online Payment',
                                'wire_transfer' => 'Wire Transfer',
                            ])
                            ->required()
                            ->searchable()
                            ->native(false),
                        TextInput::make('payment_reference')
                            ->label('Payment Reference')
                            ->helperText('Check number, transaction ID, etc.'),
                        TextInput::make('bank_account')
                            ->label('Bank Account'),
                        TextInput::make('amount')
                            ->label('Amount')
                            ->numeric()
                            ->required()
                            ->prefix('$')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                static::updateBaseAmount($set, $get);
                            }),
                        TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->default(1.000000)
                            ->minValue(0)
                            ->step(0.000001)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                static::updateBaseAmount($set, $get);
                            }),
                        Placeholder::make('base_amount_display')
                            ->label('Base Amount')
                            ->content(function (Get $get): string {
                                $amount = (float) ($get('amount') ?? 0);
                                $exchangeRate = (float) ($get('exchange_rate') ?? 1);
                                $baseAmount = $amount * $exchangeRate;
                                
                                return '$'.number_format($baseAmount, 2);
                            }),
                        Hidden::make('base_amount')
                            ->default(0),
                    ])
                    ->columns(3),

                Section::make('Additional Information')
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
                    ])
                    ->collapsible()
                    ->collapsed(),

                Section::make('Approval Information')
                    ->schema([
                        Select::make('requested_by')
                            ->relationship('requester', 'name')
                            ->label('Requested By')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('approved_by')
                            ->relationship('approver', 'name')
                            ->label('Approved By')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DatePicker::make('approved_at')
                            ->label('Approved At')
                            ->native(false),
                        Select::make('paid_by')
                            ->relationship('paidBy', 'name')
                            ->label('Paid By')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        DatePicker::make('paid_at')
                            ->label('Paid At')
                            ->native(false),
                        Textarea::make('cancellation_reason')
                            ->label('Cancellation Reason')
                            ->rows(2)
                            ->columnSpanFull()
                            ->visible(fn (Get $get) => $get('status') === 'cancelled'),
                    ])
                    ->columns(3)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }

    protected static function updateBaseAmount($set, Get $get): void
    {
        $amount = (float) ($get('amount') ?? 0);
        $exchangeRate = (float) ($get('exchange_rate') ?? 1);
        $baseAmount = $amount * $exchangeRate;
        
        $set('base_amount', number_format($baseAmount, 2, '.', ''));
    }
}
