<?php

namespace App\Filament\Resources\BusinessPartners\Schemas;

use App\Models\BusinessPartner;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class BusinessPartnerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('name')
                    ->label('Business Partner')
                    ->inlineLabel(),
                TextEntry::make('code')
                    ->placeholder('-')
                    ->inlineLabel(),
                TextEntry::make('parentPartner.name')
                    ->label('Parent Partner')
                    ->placeholder('-')
                    ->inlineLabel(),
                TextEntry::make('type_labels')
                    ->label('Type')
                    ->state(fn (BusinessPartner $record) => implode(', ', $record->type_labels))
                    ->placeholder('General')
                    ->inlineLabel(),
                TextEntry::make('email')
                    ->placeholder('-')
                    ->inlineLabel(),
                TextEntry::make('phone')
                    ->placeholder('-')
                    ->inlineLabel(),
                TextEntry::make('website')
                    ->placeholder('-')
                    ->inlineLabel(),
                TextEntry::make('notes')
                    ->placeholder('-')
                    ->inlineLabel(),
            ]);
    }
}
