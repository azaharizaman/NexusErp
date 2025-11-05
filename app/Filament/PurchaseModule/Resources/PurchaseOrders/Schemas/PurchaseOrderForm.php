<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseOrders\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Schemas\Schema;

class PurchaseOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchase Order Details')
                    ->schema([
                        TextInput::make('po_number')
                            ->label('PO Number')
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
                        Select::make('purchase_recommendation_id')
                            ->relationship('purchaseRecommendation', 'recommendation_number')
                            ->label('Based on Recommendation')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('purchase_contract_id')
                            ->relationship('purchaseContract', 'contract_number')
                            ->label('Under Contract')
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
                        DatePicker::make('po_date')
                            ->label('PO Date')
                            ->required()
                            ->default(now())
                            ->native(false),
                        DatePicker::make('expected_delivery_date')
                            ->label('Expected Delivery')
                            ->native(false),
                        DatePicker::make('delivery_deadline')
                            ->label('Delivery Deadline')
                            ->native(false),
                        Select::make('price_list_id')
                            ->relationship('priceList', 'name')
                            ->label('Price List')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('terms_template_id')
                            ->relationship('termsTemplate', 'name')
                            ->label('Terms Template')
                            ->searchable()
                            ->preload()
                            ->native(false),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'approved' => 'Approved',
                                'issued' => 'Issued',
                                'closed' => 'Closed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Addresses')
                    ->schema([
                        Textarea::make('shipping_address')
                            ->label('Shipping Address')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('billing_address')
                            ->label('Billing Address')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->collapsible(),

                Section::make('Purchase Order Items')
                    ->schema([
                        Repeater::make('items')
                            ->relationship()
                            ->schema([
                                TextInput::make('item_code')
                                    ->label('Item Code')
                                    ->columnSpan(1),
                                TextInput::make('item_description')
                                    ->label('Description')
                                    ->required()
                                    ->columnSpan(2),
                                TextInput::make('quantity')
                                    ->label('Quantity')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        static::updateLineTotal($set, $get);
                                    })
                                    ->columnSpan(1),
                                Select::make('uom_id')
                                    ->relationship('uom', 'name')
                                    ->label('UOM')
                                    ->searchable()
                                    ->preload()
                                    ->native(false)
                                    ->columnSpan(1),
                                TextInput::make('unit_price')
                                    ->label('Unit Price')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        static::updateLineTotal($set, $get);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('discount_percent')
                                    ->label('Discount %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        static::updateLineTotal($set, $get);
                                    })
                                    ->columnSpan(1),
                                TextInput::make('tax_rate')
                                    ->label('Tax %')
                                    ->numeric()
                                    ->default(0)
                                    ->suffix('%')
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                        static::updateLineTotal($set, $get);
                                    })
                                    ->columnSpan(1),
                                Placeholder::make('line_total_display')
                                    ->label('Line Total')
                                    ->content(function (Get $get): string {
                                        $quantity = (float) ($get('quantity') ?? 0);
                                        $unitPrice = (float) ($get('unit_price') ?? 0);
                                        $discountPercent = (float) ($get('discount_percent') ?? 0);
                                        $taxRate = (float) ($get('tax_rate') ?? 0);

                                        $baseTotal = $quantity * $unitPrice;
                                        $discountAmount = $baseTotal * ($discountPercent / 100);
                                        $afterDiscount = $baseTotal - $discountAmount;
                                        $taxAmount = $afterDiscount * ($taxRate / 100);
                                        $lineTotal = $afterDiscount + $taxAmount;

                                        return '$' . number_format($lineTotal, 2);
                                    })
                                    ->columnSpan(1),
                                Hidden::make('line_total')
                                    ->default(0),
                                Hidden::make('discount_amount')
                                    ->default(0),
                                Hidden::make('tax_amount')
                                    ->default(0),
                                DatePicker::make('expected_delivery_date')
                                    ->label('Expected Delivery')
                                    ->native(false)
                                    ->columnSpan(2),
                                Textarea::make('specifications')
                                    ->label('Specifications')
                                    ->rows(2)
                                    ->columnSpan(3),
                                Textarea::make('notes')
                                    ->label('Notes')
                                    ->rows(2)
                                    ->columnSpan(3),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['item_description'] ?? 'New Item')
                            ->addActionLabel('Add Item')
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set, Get $get) {
                                static::updateTotals($set, $get);
                            }),
                    ]),

                Section::make('Totals')
                    ->schema([
                        Placeholder::make('subtotal_display')
                            ->label('Subtotal')
                            ->content(function (Get $get): string {
                                return '$' . number_format(static::calculateSubtotal($get), 2);
                            }),
                        Placeholder::make('discount_display')
                            ->label('Total Discount')
                            ->content(function (Get $get): string {
                                return '$' . number_format(static::calculateTotalDiscount($get), 2);
                            }),
                        Placeholder::make('tax_display')
                            ->label('Total Tax')
                            ->content(function (Get $get): string {
                                return '$' . number_format(static::calculateTotalTax($get), 2);
                            }),
                        Placeholder::make('total_display')
                            ->label('Total Amount')
                            ->content(function (Get $get): string {
                                $subtotal = static::calculateSubtotal($get);
                                $discount = static::calculateTotalDiscount($get);
                                $tax = static::calculateTotalTax($get);
                                $total = $subtotal - $discount + $tax;

                                return '$' . number_format($total, 2);
                            }),
                        Hidden::make('subtotal'),
                        Hidden::make('discount_amount'),
                        Hidden::make('tax_amount'),
                        Hidden::make('total_amount'),
                    ])
                    ->columns(4),

                Section::make('Terms and Conditions')
                    ->schema([
                        TextInput::make('payment_terms')
                            ->label('Payment Terms')
                            ->placeholder('e.g., Net 30, 2/10 Net 30'),
                        TextInput::make('delivery_terms')
                            ->label('Delivery Terms')
                            ->placeholder('e.g., FOB, CIF'),
                        TextInput::make('incoterms')
                            ->label('Incoterms')
                            ->placeholder('e.g., EXW, FOB, CIF'),
                        RichEditor::make('terms_and_conditions')
                            ->label('Terms and Conditions')
                            ->columnSpanFull(),
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
                            ->columnSpanFull()
                            ->helperText('These notes are for internal use only and will not be visible to the supplier.'),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }

    protected static function updateLineTotal(callable $set, Get $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $discountPercent = (float) ($get('discount_percent') ?? 0);
        $taxRate = (float) ($get('tax_rate') ?? 0);

        $baseTotal = $quantity * $unitPrice;
        $discountAmount = $baseTotal * ($discountPercent / 100);
        $afterDiscount = $baseTotal - $discountAmount;
        $taxAmount = $afterDiscount * ($taxRate / 100);
        $lineTotal = $afterDiscount + $taxAmount;

        $set('line_total', number_format($lineTotal, 2, '.', ''));
        $set('discount_amount', number_format($discountAmount, 2, '.', ''));
        $set('tax_amount', number_format($taxAmount, 2, '.', ''));
    }

    protected static function updateTotals(callable $set, Get $get): void
    {
        $subtotal = static::calculateSubtotal($get);
        $discount = static::calculateTotalDiscount($get);
        $tax = static::calculateTotalTax($get);
        $total = $subtotal - $discount + $tax;

        $set('../../subtotal', number_format($subtotal, 2, '.', ''));
        $set('../../discount_amount', number_format($discount, 2, '.', ''));
        $set('../../tax_amount', number_format($tax, 2, '.', ''));
        $set('../../total_amount', number_format($total, 2, '.', ''));
    }

    protected static function calculateSubtotal(Get $get): float
    {
        $items = $get('items') ?? [];
        $subtotal = 0;

        foreach ($items as $item) {
            $quantity = (float) ($item['quantity'] ?? 0);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $subtotal += $quantity * $unitPrice;
        }

        return $subtotal;
    }

    protected static function calculateTotalDiscount(Get $get): float
    {
        $items = $get('items') ?? [];
        $discount = 0;

        foreach ($items as $item) {
            $discount += (float) ($item['discount_amount'] ?? 0);
        }

        return $discount;
    }

    protected static function calculateTotalTax(Get $get): float
    {
        $items = $get('items') ?? [];
        $tax = 0;

        foreach ($items as $item) {
            $tax += (float) ($item['tax_amount'] ?? 0);
        }

        return $tax;
    }
}
