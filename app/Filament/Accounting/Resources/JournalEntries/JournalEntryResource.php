<?php

namespace App\Filament\Accounting\Resources\JournalEntries;

use App\Actions\Accounting\PostJournalEntry;
use App\Actions\Accounting\ReverseJournalEntry;
use App\Filament\Accounting\Resources\JournalEntries\Pages\CreateJournalEntry;
use App\Filament\Accounting\Resources\JournalEntries\Pages\EditJournalEntry;
use App\Filament\Accounting\Resources\JournalEntries\Pages\ListJournalEntries;
use App\Filament\Accounting\Resources\JournalEntries\Pages\ViewJournalEntry;
use App\Models\JournalEntry;
use Filament\Forms\Components;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class JournalEntryResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'General Ledger';

    protected static ?string $navigationLabel = 'Journal Entries';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\Section::make('Journal Entry Information')
                    ->schema([
                        Components\TextInput::make('journal_entry_number')
                            ->label('JE Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated on save'),

                        Components\Select::make('company_id')
                            ->label('Company')
                            ->relationship('company', 'name')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->default(fn () => auth()->user()->company_id ?? null),

                        Components\Select::make('fiscal_year_id')
                            ->label('Fiscal Year')
                            ->relationship('fiscalYear', 'name', fn ($query, Get $get) => $query->where('company_id', $get('company_id')))
                            ->required()
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('accounting_period_id', null)),

                        Components\Select::make('accounting_period_id')
                            ->label('Accounting Period')
                            ->relationship(
                                'accountingPeriod',
                                'period_name',
                                fn ($query, Get $get) => $query->where('fiscal_year_id', $get('fiscal_year_id'))
                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                        Components\Select::make('entry_type')
                            ->label('Entry Type')
                            ->options([
                                'manual' => 'Manual',
                                'automatic' => 'Automatic',
                                'opening' => 'Opening',
                                'closing' => 'Closing',
                                'adjusting' => 'Adjusting',
                                'reversing' => 'Reversing',
                                'reclassification' => 'Reclassification',
                                'intercompany' => 'Inter-company',
                            ])
                            ->default('manual')
                            ->required(),

                        Components\DatePicker::make('entry_date')
                            ->label('Entry Date')
                            ->required()
                            ->default(now()),

                        Components\TextInput::make('reference_number')
                            ->label('Reference Number')
                            ->maxLength(255),

                        Components\Textarea::make('description')
                            ->label('Description')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        Components\Textarea::make('notes')
                            ->label('Notes')
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Components\Section::make('Journal Entry Lines')
                    ->schema([
                        Components\Repeater::make('lines')
                            ->relationship('lines')
                            ->schema([
                                Components\Select::make('account_id')
                                    ->label('Account')
                                    ->relationship('account', 'account_name')
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(3),

                                Components\TextInput::make('debit')
                                    ->label('Debit')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->live()
                                    ->columnSpan(2),

                                Components\TextInput::make('credit')
                                    ->label('Credit')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->step(0.01)
                                    ->prefix('$')
                                    ->live()
                                    ->columnSpan(2),

                                Components\Select::make('cost_center_id')
                                    ->label('Cost Center')
                                    ->relationship('costCenter', 'name')
                                    ->searchable()
                                    ->columnSpan(2),

                                Components\Textarea::make('description')
                                    ->label('Description')
                                    ->rows(1)
                                    ->columnSpanFull(),
                            ])
                            ->columns(9)
                            ->reorderable('sort_order')
                            ->addActionLabel('Add Line')
                            ->minItems(2)
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $totalDebit = collect($state)->sum('debit');
                                $totalCredit = collect($state)->sum('credit');
                                $set('total_debit', $totalDebit);
                                $set('total_credit', $totalCredit);
                            }),

                        Components\Placeholder::make('balance_info')
                            ->label('Balance Information')
                            ->content(function (Get $get) {
                                $totalDebit = $get('total_debit') ?? 0;
                                $totalCredit = $get('total_credit') ?? 0;
                                $difference = $totalDebit - $totalCredit;

                                $status = abs($difference) < 0.01 ? '✅ Balanced' : '❌ Not Balanced';

                                return "Debits: $".number_format($totalDebit, 2).' | '.
                                       "Credits: $".number_format($totalCredit, 2).' | '.
                                       "Difference: $".number_format($difference, 2).' | '.
                                       $status;
                            })
                            ->columnSpanFull(),

                        Components\Hidden::make('total_debit'),
                        Components\Hidden::make('total_credit'),
                    ]),

                Components\Section::make('Currency & Exchange')
                    ->schema([
                        Components\Select::make('currency_id')
                            ->label('Currency')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload(),

                        Components\TextInput::make('exchange_rate')
                            ->label('Exchange Rate')
                            ->numeric()
                            ->step(0.000001)
                            ->minValue(0),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('journal_entry_number')
                    ->label('JE Number')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('entry_date')
                    ->label('Entry Date')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('entry_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'success' => 'automatic',
                        'primary' => 'manual',
                        'warning' => ['opening', 'closing', 'adjusting'],
                        'danger' => 'reversing',
                    ]),

                Tables\Columns\TextColumn::make('description')
                    ->label('Description')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_credit')
                    ->label('Total Credit')
                    ->money('usd')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'submitted',
                        'success' => 'posted',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('fiscalYear.name')
                    ->label('Fiscal Year')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('accountingPeriod.period_name')
                    ->label('Period')
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'posted' => 'Posted',
                        'cancelled' => 'Cancelled',
                    ]),

                Tables\Filters\SelectFilter::make('entry_type')
                    ->options([
                        'manual' => 'Manual',
                        'automatic' => 'Automatic',
                        'opening' => 'Opening',
                        'closing' => 'Closing',
                        'adjusting' => 'Adjusting',
                        'reversing' => 'Reversing',
                        'reclassification' => 'Reclassification',
                        'intercompany' => 'Inter-company',
                    ]),

                Tables\Filters\Filter::make('entry_date')
                    ->form([
                        Components\DatePicker::make('from'),
                        Components\DatePicker::make('until'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q, $date) => $q->whereDate('entry_date', '>=', $date))
                            ->when($data['until'], fn ($q, $date) => $q->whereDate('entry_date', '<=', $date));
                    }),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (JournalEntry $record) => $record->status === 'draft'),
                Tables\Actions\Action::make('post')
                    ->label('Post')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (JournalEntry $record) => PostJournalEntry::run($record))
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Journal entry posted')
                            ->body('The journal entry has been posted to the general ledger.')
                    )
                    ->visible(fn (JournalEntry $record) => in_array($record->status, ['draft', 'submitted'])),
                Tables\Actions\Action::make('reverse')
                    ->label('Reverse')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (JournalEntry $record) => ReverseJournalEntry::run($record))
                    ->successNotification(
                        fn () => \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Reversal entry created')
                            ->body('A reversal entry has been created.')
                    )
                    ->visible(fn (JournalEntry $record) => $record->status === 'posted' && ! $record->is_reversal && ! $record->reversal_entry_id),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
                Tables\Actions\ForceDeleteBulkAction::make(),
                Tables\Actions\RestoreBulkAction::make(),
            ])
            ->defaultSort('entry_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListJournalEntries::route('/'),
            'create' => CreateJournalEntry::route('/create'),
            'view' => ViewJournalEntry::route('/{record}'),
            'edit' => EditJournalEntry::route('/{record}/edit'),
        ];
    }
}
