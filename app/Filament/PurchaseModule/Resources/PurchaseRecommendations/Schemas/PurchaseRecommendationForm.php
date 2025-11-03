<?php

namespace App\Filament\PurchaseModule\Resources\PurchaseRecommendations\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Section;
use Filament\Schemas\Schema;

class PurchaseRecommendationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Recommendation Details')
                    ->schema([
                        TextInput::make('recommendation_number')
                            ->label('Recommendation Number')
                            ->required()
                            ->disabled()
                            ->default(fn () => 'Auto-generated'),
                        Select::make('request_for_quotation_id')
                            ->relationship('requestForQuotation', 'rfq_number')
                            ->label('RFQ')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Reset recommended quotation when RFQ changes
                                $set('recommended_quotation_id', null);
                            }),
                        Select::make('recommended_quotation_id')
                            ->relationship(
                                'recommendedQuotation',
                                'quotation_number',
                                fn ($query, callable $get) => $query->where('request_for_quotation_id', $get('request_for_quotation_id'))
                            )
                            ->label('Recommended Quotation')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the quotation being recommended'),
                        Select::make('company_id')
                            ->relationship('company', 'name')
                            ->label('Company')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('recommendation_date')
                            ->label('Recommendation Date')
                            ->required()
                            ->default(now()),
                        Select::make('currency_id')
                            ->relationship('currency', 'name')
                            ->label('Currency')
                            ->searchable()
                            ->preload(),
                        TextInput::make('recommended_total')
                            ->label('Recommended Total')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'submitted' => 'Submitted',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(2),
                
                Section::make('Justification')
                    ->schema([
                        Textarea::make('justification')
                            ->label('Justification')
                            ->required()
                            ->rows(4)
                            ->helperText('Explain why this supplier/quotation is recommended')
                            ->columnSpanFull(),
                        Textarea::make('comparison_notes')
                            ->label('Comparison Notes')
                            ->rows(4)
                            ->helperText('Notes from quotation comparison')
                            ->columnSpanFull(),
                    ]),
                
                Section::make('Approval Information')
                    ->schema([
                        TextInput::make('approved_by')
                            ->label('Approved By')
                            ->numeric()
                            ->disabled(),
                        DateTimePicker::make('approved_at')
                            ->label('Approved At')
                            ->disabled(),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
