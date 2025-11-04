<?php

namespace App\Filament\Resources\StatusRequests\Pages;

use App\Filament\Resources\StatusRequests\StatusRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListStatusRequests extends ListRecords
{
    protected static string $resource = StatusRequestResource::class;
}
