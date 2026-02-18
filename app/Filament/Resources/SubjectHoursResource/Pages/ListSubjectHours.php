<?php

namespace App\Filament\Resources\SubjectHoursResource\Pages;

use App\Filament\Resources\SubjectHoursResource\SubjectHoursResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;

class ListSubjectHours extends ListRecords
{
    protected static string $resource = SubjectHoursResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportar_excel')
                ->label('Exportar a Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(route('export.subject-hours'))
                ->openUrlInNewTab(true),
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



