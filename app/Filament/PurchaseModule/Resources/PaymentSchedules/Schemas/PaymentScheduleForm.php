<?php

namespace App\Filament\PurchaseModule\Resources\PaymentSchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PaymentScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Payment Schedule Details')
                    ->schema([
                        TextInput::make('schedule_number')
                            ->label('Schedule Number')
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
                        Select::make('supplier_invoice_id')
                            ->relationship('supplierInvoice', 'invoice_number')
                            ->label('Supplier Invoice')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('payment_voucher_id')
                            ->relationship('paymentVoucher', 'voucher_number')
                            ->label('Payment Voucher')
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
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->required()
                            ->native(false),
                        TextInput::make('milestone')
                            ->label('Milestone')
                            ->placeholder('e.g., Upon Delivery, Net 30, 50% Advance')
                            ->maxLength(255),
                    ])->columns(2),

                Section::make('Amount Details')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Scheduled Amount')
                            ->required()
                            ->numeric()
                            ->prefix(fn ($get) => \App\Models\Currency::find($get('currency_id'))?->symbol ?? '$')
                            ->step('0.01'),
                        TextInput::make('paid_amount')
                            ->label('Paid Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix(fn ($get) => \App\Models\Currency::find($get('currency_id'))?->symbol ?? '$')
                            ->step('0.01'),
                        TextInput::make('outstanding_amount')
                            ->label('Outstanding Amount')
                            ->numeric()
                            ->default(0)
                            ->prefix(fn ($get) => \App\Models\Currency::find($get('currency_id'))?->symbol ?? '$')
                            ->step('0.01')
                            ->disabled()
                            ->dehydrated(),
                    ])->columns(3),

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
                    ]),
            ]);
    }
}
