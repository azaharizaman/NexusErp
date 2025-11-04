<?php

namespace App\Filament\Resources\StatusTransitions\Schemas;

use App\Models\ModelStatus;
use App\Models\User;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Get;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class StatusTransitionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Section::make('Transition Configuration')
                    ->schema([
                        Select::make('status_from_id')
                            ->label('From Status')
                            ->options(fn () => ModelStatus::with('documentModel')
                                ->get()
                                ->mapWithKeys(fn ($status) => [
                                    $status->id => $status->documentModel->name.' - '.$status->name,
                                ]))
                            ->required()
                            ->searchable()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('status_to_id', null)),

                        Select::make('status_to_id')
                            ->label('To Status')
                            ->options(function (Get $get) {
                                $fromStatusId = $get('status_from_id');
                                if (! $fromStatusId) {
                                    return [];
                                }

                                $fromStatus = ModelStatus::find($fromStatusId);
                                if (! $fromStatus) {
                                    return [];
                                }

                                return ModelStatus::where('document_model_id', $fromStatus->document_model_id)
                                    ->where('id', '!=', $fromStatusId)
                                    ->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->disabled(fn (Get $get) => ! $get('status_from_id')),

                        KeyValue::make('condition')
                            ->label('Transition Conditions')
                            ->helperText('Optional: Define conditions as key-value pairs')
                            ->keyLabel('Condition Key')
                            ->valueLabel('Condition Value')
                            ->addActionLabel('Add Condition')
                            ->reorderable()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Approval Workflow')
                    ->schema([
                        Repeater::make('approvalWorkflows')
                            ->relationship()
                            ->schema([
                                Select::make('approval_type')
                                    ->options([
                                        'single' => 'Single Approval',
                                        'group' => 'Group Approval',
                                    ])
                                    ->default('single')
                                    ->required()
                                    ->label('Approval Type'),

                                TagsInput::make('required_roles')
                                    ->label('Required Roles')
                                    ->helperText('Select or type role names')
                                    ->suggestions(fn () => Role::pluck('name')->toArray())
                                    ->placeholder('Add role'),

                                Select::make('staff_ids')
                                    ->label('Specific Staff Members')
                                    ->helperText('Select specific staff members (optional)')
                                    ->multiple()
                                    ->searchable()
                                    ->preload()
                                    ->options(fn () => User::pluck('name', 'id'))
                                    ->placeholder('Select staff members'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add Approval Workflow')
                            ->collapsible()
                            ->collapsed(),
                    ]),
            ]);
    }
}
