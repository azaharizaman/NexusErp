<?php

namespace App\Filament\Resources\BusinessPartners\Schemas;

use App\Models\BusinessPartner;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BusinessPartnerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('code')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->email(),
                TextInput::make('phone')
                    ->maxLength(50),
                TextInput::make('website')
                    ->url(),
                Select::make('parent_business_partner_id')
                    ->label('Parent Business Partner')
                    ->options(fn (?BusinessPartner $record) => BusinessPartner::query()
                        ->when($record?->exists, function ($query) use ($record) {
                            $excluded = $record->getDescendants()
                                ->pluck('id')
                                ->push($record->id)
                                ->all();

                            $query->whereNotIn('id', $excluded);
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray()
                    )
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Optional link to a parent business partner.'),
                Toggle::make('is_supplier')
                    ->label('Supplier'),
                Toggle::make('is_customer')
                    ->label('Customer'),
                Textarea::make('notes'),
            ]);
    }
}
