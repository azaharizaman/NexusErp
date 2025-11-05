<?php

namespace App\Filament\PurchaseModule\Resources\Suppliers\Schemas;

use App\Models\BusinessPartner;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SupplierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('code')
                    ->maxLength(50)
                    ->unique(ignoreRecord: true),
                TextInput::make('email')
                    ->email()
                    ->maxLength(255),
                TextInput::make('phone')
                    ->maxLength(50),
                TextInput::make('website')
                    ->url()
                    ->maxLength(255),
                Select::make('parent_business_partner_id')
                    ->label('Parent Supplier')
                    ->options(fn (?BusinessPartner $record) => BusinessPartner::query()
                        ->suppliers()
                        ->when($record?->exists, function ($query) use ($record) {
                            $excluded = $record->getDescendants()
                                ->pluck('id')
                                ->push($record->id)
                                ->all();

                            $query->whereNotIn('id', $excluded);
                        })
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->toArray())
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->helperText('Optional hierarchy for supplier organizations.'),
                Toggle::make('is_customer')
                    ->label('Also a customer')
                    ->helperText('Enable if this supplier also acts as a customer.'),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Hidden::make('is_supplier')
                    ->default(true)
                    ->dehydrated()
                    ->afterStateHydrated(fn (Hidden $component, $state) => $component->state(true)),
            ]);
    }
}
