<?php

namespace App\Filament\Accounting\Resources;

use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\SalesInvoice;
use Filament\Schemas\Schema;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;
use App\Actions\Accounting\PostSalesInvoice;
use App\Filament\Accounting\Resources\SalesInvoiceResource\Pages;

class SalesInvoiceResource extends Resource
{
    protected static ?string $model = SalesInvoice::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Accounts Receivable';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Sales Invoices';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make('Invoice Information')
                    ->schema([
                        Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->reactive()
                            ->preload(),

                        Components\Select::make('customer_id')
                            ->relationship('customer', 'name', fn (Builder $query) =>
                                $query->where('is_customer', true)
                            )
                            ->searchable()
                            ->required()
                            ->preload(),

                        Components\Select::make('fiscal_year_id')
                        ->relationship('fiscalYear', 'year', fn (Builder $query, callable $get) =>
                            $query->where('company_id', $get('company_id')))
                        ->required()
                        ->reactive()
                        ->preload(),

                    Components\Select::make('accounting_period_id')
                        ->relationship('accountingPeriod', 'period_name', fn (Builder $query, callable $get) =>
                            $query->where('fiscal_year_id', $get('fiscal_year_id'))
                                ->where('status', 'open'))
                        ->required()
                        ->preload(),

                    Components\DatePicker::make('invoice_date')
                        ->required()
                        ->default(now())
                        ->reactive(),

                    Components\TextInput::make('payment_terms')
                        ->maxLength(255),

                    Components\TextInput::make('credit_days')
                        ->numeric()
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $invoiceDate = $get('invoice_date');
                            if ($invoiceDate && $state) {
                                $set('due_date', now()->parse($invoiceDate)->addDays($state));
                            }
                        }),

                    Components\DatePicker::make('due_date')
                        ->required(),

                    Components\Select::make('currency_id')
                        ->relationship('currency', 'code')
                        ->required()
                        ->default(fn () => \App\Models\Currency::where('code', 'MYR')->first()?->id)
                        ->preload(),

                    Components\TextInput::make('exchange_rate')
                        ->numeric()
                        ->default(1.000000)
                        ->required()
                        ->step('0.000001'),
                ])->columns(2),

            Components\Section::make('Invoice Items')
                ->schema([
                    Components\Repeater::make('items')
                        ->relationship('items')
                        ->schema([
                            Components\TextInput::make('item_code')
                                ->required()
                                ->maxLength(100),

                            Components\TextInput::make('item_description')
                                ->required()
                                ->maxLength(500),

                            Components\Textarea::make('specifications')
                                ->maxLength(1000)
                                ->rows(2),

                            Components\TextInput::make('quantity')
                                ->numeric()
                                ->required()
                                ->default(1)
                                ->step('0.0001')
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                                    self::calculateLineTotal($set, $get)),

                            Components\Select::make('uom_id')
                                ->relationship('uom', 'code')
                                ->required()
                                ->preload(),

                            Components\TextInput::make('unit_price')
                                ->numeric()
                                ->required()
                                ->step('0.0001')
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                                    self::calculateLineTotal($set, $get)),

                            Components\TextInput::make('discount_percent')
                                ->numeric()
                                ->default(0)
                                ->step('0.01')
                                ->suffix('%')
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                                    self::calculateLineTotal($set, $get)),

                            Components\TextInput::make('tax_rate')
                                ->numeric()
                                ->default(0)
                                ->step('0.01')
                                ->suffix('%')
                                ->reactive()
                                ->afterStateUpdated(fn ($state, callable $set, callable $get) =>
                                    self::calculateLineTotal($set, $get)),

                            Components\Select::make('revenue_account_id')
                                ->relationship('revenueAccount', 'account_name', fn (Builder $query) =>
                                    $query->where('account_type', 'income'))
                                ->required()
                                ->searchable()
                                ->preload(),

                            Components\TextInput::make('line_total')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->step('0.0001'),

                            Components\TextInput::make('tax_amount')
                                ->numeric()
                                ->disabled()
                                ->dehydrated(false)
                                ->step('0.0001'),
                        ])
                        ->columns(3)
                        ->defaultItems(1)
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => $state['item_description'] ?? null),
                ]),

            Components\Section::make('Totals')
                ->schema([
                    Components\Placeholder::make('subtotal_display')
                        ->label('Subtotal')
                        ->content(fn (?SalesInvoice $record) =>
                            $record ? number_format((float) $record->subtotal, 4) : '0.0000'),

                    Components\TextInput::make('discount_amount')
                        ->numeric()
                        ->default(0)
                        ->step('0.0001'),

                    Components\Placeholder::make('tax_amount_display')
                        ->label('Total Tax')
                        ->content(fn (?SalesInvoice $record) =>
                            $record ? number_format((float) $record->tax_amount, 4) : '0.0000'),

                    Components\Placeholder::make('total_amount_display')
                        ->label('Total Amount')
                        ->content(fn (?SalesInvoice $record) =>
                            $record ? number_format((float) $record->total_amount, 4) : '0.0000'),
                ])->columns(2),

            Components\Section::make('Addresses')
                ->schema([
                    Components\Textarea::make('billing_address')
                        ->rows(3),

                    Components\Textarea::make('shipping_address')
                        ->rows(3),
                ])->columns(2)->collapsible(),

            Components\Section::make('Additional Information')
                ->schema([
                    Components\Textarea::make('description')
                        ->maxLength(1000),

                    Components\Textarea::make('notes')
                        ->maxLength(1000),

                    Components\Textarea::make('terms_and_conditions')
                        ->rows(5),
                ])->collapsible(),
        ]);
    }

    protected static function calculateLineTotal(callable $set, callable $get): void
    {
        $quantity = (float) ($get('quantity') ?? 0);
        $unitPrice = (float) ($get('unit_price') ?? 0);
        $discountPercent = (float) ($get('discount_percent') ?? 0);
        $taxRate = (float) ($get('tax_rate') ?? 0);

        $subtotal = $quantity * $unitPrice;
        $discountAmount = $subtotal * ($discountPercent / 100);
        $lineTotal = $subtotal - $discountAmount;
        $taxAmount = $lineTotal * ($taxRate / 100);

        $set('line_total', number_format($lineTotal, 4, '.', ''));
        $set('tax_amount', number_format($taxAmount, 4, '.', ''));
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('invoice_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->money(fn ($record) => $record->currency->code ?? 'MYR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('paid_amount')
                    ->money(fn ($record) => $record->currency->code ?? 'MYR')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('outstanding_amount')
                    ->money(fn ($record) => $record->currency->code ?? 'MYR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'issued' => 'info',
                        'partially_paid' => 'warning',
                        'paid' => 'success',
                        'overdue' => 'danger',
                        'cancelled' => 'gray',
                        default => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_posted_to_gl')
                    ->label('Posted to GL')
                    ->boolean()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Created By')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'issued' => 'Issued',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('invoice_date')
                    ->form([
                        Components\DatePicker::make('from'),
                        Components\DatePicker::make('until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('invoice_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('invoice_date', '<=', $date));
                    }),

                Tables\Filters\TernaryFilter::make('is_posted_to_gl')
                    ->label('Posted to GL'),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),

                Tables\Actions\Action::make('post_to_gl')
                    ->label('Post to GL')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (SalesInvoice $record) =>
                        !$record->is_posted_to_gl &&
                        in_array($record->status, ['issued', 'partially_paid']))
                    ->action(function (SalesInvoice $record) {
                        // Get AR account ID from company settings (simplified - should be configurable)
                        $arAccount = \App\Models\Account::where('account_code', '1200')
                            ->where('company_id', $record->company_id)
                            ->firstOrFail();

                        $taxAccount = \App\Models\Account::where('account_code', '2130')
                            ->where('company_id', $record->company_id)
                            ->first();

                        PostSalesInvoice::run($record, $arAccount->id, $taxAccount?->id);
                    })
                    ->successNotificationTitle('Invoice posted to GL successfully'),

                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSalesInvoices::route('/'),
            'create' => Pages\CreateSalesInvoice::route('/create'),
            'edit' => Pages\EditSalesInvoice::route('/{record}/edit'),
            'view' => Pages\ViewSalesInvoice::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }
}
