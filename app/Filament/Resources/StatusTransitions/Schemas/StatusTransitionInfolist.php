<?php

namespace App\Filament\Resources\StatusTransitions\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StatusTransitionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transition Information')
                    ->schema([
                        TextEntry::make('fromStatus.documentModel.name')
                            ->label('Document Model'),

                        TextEntry::make('fromStatus.name')
                            ->label('From Status')
                            ->badge()
                            ->color(fn ($record) => $record->fromStatus->color),

                        TextEntry::make('toStatus.name')
                            ->label('To Status')
                            ->badge()
                            ->color(fn ($record) => $record->toStatus->color),

                        TextEntry::make('condition')
                            ->label('Conditions')
                            ->formatStateUsing(fn ($state) => $state ? json_encode($state, JSON_PRETTY_PRINT) : 'None')
                            ->columnSpanFull(),
                    ])
                    ->columns(3),

                Section::make('Approval Workflows')
                    ->schema([
                        RepeatableEntry::make('approvalWorkflows')
                            ->label('')
                            ->schema([
                                TextEntry::make('approval_type')
                                    ->label('Approval Type')
                                    ->badge()
                                    ->color(fn ($state) => $state === 'single' ? 'info' : 'warning'),

                                TextEntry::make('required_roles')
                                    ->label('Required Roles')
                                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : 'None'),

                                TextEntry::make('staff_ids')
                                    ->label('Staff IDs')
                                    ->formatStateUsing(fn ($state) => $state ? json_encode($state) : 'None'),
                            ])
                            ->columns(3),
                    ])
                    ->visible(fn ($record) => $record->approvalWorkflows->count() > 0),
            ]);
    }
}
