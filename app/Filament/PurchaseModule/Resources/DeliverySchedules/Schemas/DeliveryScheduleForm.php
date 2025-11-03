<?php

namespace App\Filament\PurchaseModule\Resources\DeliverySchedules\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DeliveryScheduleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Schedule Details')
                    ->schema([
                        TextInput::make('schedule_number')
                            ->label('Schedule Number')
                            ->required()
                            ->disabled()
                            ->default(fn () => 'Auto-generated on save')
                            ->dehydrated(false),
                        Select::make('purchase_order_id')
                            ->relationship('purchaseOrder', 'po_number')
                            ->label('Purchase Order')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->reactive(),
                        Select::make('purchase_order_item_id')
                            ->relationship('purchaseOrderItem', 'item_description')
                            ->label('PO Item')
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->helperText('Optional - link to a specific PO line item'),
                        Select::make('status')
                            ->options([
                                'scheduled' => 'Scheduled',
                                'confirmed' => 'Confirmed',
                                'in_transit' => 'In Transit',
                                'delivered' => 'Delivered',
                                'delayed' => 'Delayed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->default('scheduled')
                            ->native(false),
                    ])
                    ->columns(2),

                Section::make('Delivery Dates')
                    ->schema([
                        DatePicker::make('scheduled_date')
                            ->label('Scheduled Date')
                            ->required()
                            ->native(false),
                        DatePicker::make('expected_date')
                            ->label('Expected Date')
                            ->native(false),
                        DatePicker::make('actual_delivery_date')
                            ->label('Actual Delivery Date')
                            ->native(false),
                    ])
                    ->columns(3),

                Section::make('Quantities')
                    ->schema([
                        TextInput::make('scheduled_quantity')
                            ->label('Scheduled Quantity')
                            ->numeric()
                            ->required()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $delivered = (float) ($get('delivered_quantity') ?? 0);
                                $set('remaining_quantity', (float) $state - $delivered);
                            }),
                        TextInput::make('delivered_quantity')
                            ->label('Delivered Quantity')
                            ->numeric()
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $scheduled = (float) ($get('scheduled_quantity') ?? 0);
                                $set('remaining_quantity', $scheduled - (float) $state);
                            }),
                        TextInput::make('remaining_quantity')
                            ->label('Remaining Quantity')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(),
                    ])
                    ->columns(3),

                Section::make('Delivery Information')
                    ->schema([
                        TextInput::make('delivery_location')
                            ->label('Delivery Location')
                            ->maxLength(255),
                        TextInput::make('tracking_number')
                            ->label('Tracking Number')
                            ->maxLength(255),
                        TextInput::make('reminder_days_before')
                            ->label('Reminder Days Before')
                            ->numeric()
                            ->default(3)
                            ->suffix('days')
                            ->helperText('Send reminder this many days before scheduled date'),
                        Textarea::make('delivery_instructions')
                            ->label('Delivery Instructions')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(3)
                    ->collapsible(),
            ]);
    }
}
