<?php

namespace App\Filament\Resources\SubjectHoursResource\Pages;

use App\Filament\Resources\SubjectHoursResource\SubjectHoursResource;
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
    public function getSubheading(): ?string
    {
        return 'Cálculo detallado de horas lectivas por asignatura.';
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl() => $this->getResource()::getBreadcrumb(),
            null => 'Listado',
        ];
    }
}
