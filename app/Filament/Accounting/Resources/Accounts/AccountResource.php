<?php

namespace App\Filament\Accounting\Resources\Accounts;

use App\Filament\Accounting\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Accounting\Resources\Accounts\Pages\EditAccount;
use App\Filament\Accounting\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Accounting\Resources\Accounts\Pages\ViewAccount;
use App\Models\Account;
use BackedEnum;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class AccountResource extends Resource
{
    protected static ?string $model = Account::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Chart of Accounts & Setup';

    protected static ?string $recordTitleAttribute = 'account_name';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Account Information')
                    ->schema([
                        Forms\Components\TextInput::make('account_code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\TextInput::make('account_name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('account_type')
                            ->options([
                                'Asset' => 'Asset',
                                'Liability' => 'Liability',
                                'Equity' => 'Equity',
                                'Income' => 'Income',
                                'Expense' => 'Expense',
                            ])
                            ->required()
                            ->live(),
                        Forms\Components\Select::make('sub_type')
                            ->options(function (Forms\Get $get) {
                                $accountType = $get('account_type');

                                return match ($accountType) {
                                    'Asset' => [
                                        'Current Asset' => 'Current Asset',
                                        'Fixed Asset' => 'Fixed Asset',
                                        'Intangible Asset' => 'Intangible Asset',
                                        'Investment' => 'Investment',
                                    ],
                                    'Liability' => [
                                        'Current Liability' => 'Current Liability',
                                        'Long-term Liability' => 'Long-term Liability',
                                    ],
                                    'Equity' => [
                                        'Capital' => 'Capital',
                                        'Retained Earnings' => 'Retained Earnings',
                                        'Drawings' => 'Drawings',
                                    ],
                                    'Income' => [
                                        'Operating Revenue' => 'Operating Revenue',
                                        'Other Income' => 'Other Income',
                                    ],
                                    'Expense' => [
                                        'Cost of Goods Sold' => 'Cost of Goods Sold',
                                        'Operating Expense' => 'Operating Expense',
                                        'Other Expense' => 'Other Expense',
                                    ],
                                    default => [],
                                };
                            })
                            ->required(),
                        Forms\Components\Select::make('account_group_id')
                            ->relationship('accountGroup', 'name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('parent_account_id')
                            ->relationship('parentAccount', 'account_name')
                            ->searchable()
                            ->preload(),
                        Forms\Components\Select::make('company_id')
                            ->relationship('company', 'name')
                            ->required()
                            ->default(fn () => auth()->user()->company_id ?? null),
                    ])
                    ->columns(2),
                    
                Forms\Components\Section::make('Account Properties')
                    ->schema([
                        Forms\Components\Toggle::make('is_group')
                            ->label('Is Group Account'),
                        Forms\Components\Toggle::make('is_control_account')
                            ->label('Is Control Account'),
                        Forms\Components\Toggle::make('allow_manual_entries')
                            ->label('Allow Manual Entries')
                            ->default(true),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ])
                    ->columns(4),
                    
                Forms\Components\Section::make('Balance Information')
                    ->schema([
                        Forms\Components\TextInput::make('opening_balance')
                            ->numeric()
                            ->default(0)
                            ->step(0.01),
                        Forms\Components\Select::make('balance_type')
                            ->options([
                                'Debit' => 'Debit',
                                'Credit' => 'Credit',
                            ])
                            ->required()
                            ->default('Debit'),
                        Forms\Components\Select::make('currency_id')
                            ->relationship('currency', 'code')
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('account_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('account_type')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_type')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('accountGroup.name')
                    ->label('Group')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('current_balance')
                    ->numeric(decimalPlaces: 2)
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('account_type')
                    ->options([
                        'Asset' => 'Asset',
                        'Liability' => 'Liability',
                        'Equity' => 'Equity',
                        'Income' => 'Income',
                        'Expense' => 'Expense',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->trueLabel('Active accounts')
                    ->falseLabel('Inactive accounts')
                    ->native(false),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
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
            'index' => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'view' => ViewAccount::route('/{record}'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
