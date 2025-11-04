<?php

namespace App\Filament\Resources\StatusRequests\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StatusRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Request Information')
                    ->schema([
                        TextEntry::make('documentModel.name')
                            ->label('Document Model'),

                        TextEntry::make('model_type')
                            ->label('Model Type'),

                        TextEntry::make('model_id')
                            ->label('Model ID'),

                        TextEntry::make('currentStatus.name')
                            ->label('Current Status')
                            ->badge()
                            ->color(fn ($record) => $record->currentStatus->color),

                        TextEntry::make('requestedStatus.name')
                            ->label('Requested Status')
                            ->badge()
                            ->color(fn ($record) => $record->requestedStatus->color),

                        TextEntry::make('is_approved')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(function ($record) {
                                if ($record->is_approved) {
                                    return 'Approved';
                                }

                                if ($record->approved_at && ! $record->is_approved) {
                                    return 'Rejected';
                                }

                                return 'Pending';
                            })
                            ->color(function ($record) {
                                if ($record->is_approved) {
                                    return 'success';
                                }

                                if ($record->approved_at && ! $record->is_approved) {
                                    return 'danger';
                                }

                                return 'warning';
                            }),
                    ])
                    ->columns(3),

                Section::make('People')
                    ->schema([
                        TextEntry::make('requester.name')
                            ->label('Requested By'),

                        TextEntry::make('approver.name')
                            ->label('Approved/Rejected By')
                            ->default('Not yet processed'),

                        TextEntry::make('approved_at')
                            ->label('Decision At')
                            ->dateTime()
                            ->default('Pending'),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->default('N/A')
                            ->columnSpanFull()
                            ->visible(fn ($record) => $record->approved_at && ! $record->is_approved),
                    ])
                    ->columns(3),

                Section::make('Approvers')
                    ->schema([
                        TextEntry::make('approvers')
                            ->label('Assigned Approvers')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : 'None assigned'),
                    ]),
            ]);
    }
}
