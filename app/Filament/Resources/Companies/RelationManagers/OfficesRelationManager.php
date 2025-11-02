<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Offices\OfficeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class OfficesRelationManager extends RelationManager
{
    protected static string $relationship = 'offices';

    protected static ?string $relatedResource = OfficeResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
