<?php

namespace App\Filament\Accounting\Resources\JournalEntries\Pages;

use App\Filament\Accounting\Resources\JournalEntries\JournalEntryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateJournalEntry extends CreateRecord
{
    protected static string $resource = JournalEntryResource::class;
}
