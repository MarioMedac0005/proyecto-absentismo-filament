<?php

namespace App\Filament\Resources\SubjectHoursResource\Pages;

use App\Filament\Resources\SubjectHoursResource;
use Filament\Resources\Pages\ListRecords;

class ListSubjectHours extends ListRecords
{
    protected static string $resource = SubjectHoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action
        ];
    }
}
