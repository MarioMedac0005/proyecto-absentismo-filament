<?php

namespace App\Filament\Resources\Schedules\Pages;

use App\Filament\Resources\Schedules\ScheduleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageSchedules extends ManageRecords
{
    protected static string $resource = ScheduleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Crear Horario')
            ->modalHeading('Crear Horario')
            ->modalDescription('Introduce los datos del horario')
            ->modalIcon('heroicon-o-clock')
            ->modalWidth('xl')
            ->mutateFormDataUsing(function (array $data): array {
                // Si el usuario es profesor, asignar automáticamente su propio ID
                if (auth()->user()->hasRole('profesor')) {
                    $data['user_id'] = auth()->id();
                }
                return $data;
            }),
        ];
    }
    public function getSubheading(): ?string
    {
        return 'Gestión de los horarios y distribución de horas por día.';
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl() => $this->getResource()::getBreadcrumb(),
            null => 'Listado',
        ];
    }
}
