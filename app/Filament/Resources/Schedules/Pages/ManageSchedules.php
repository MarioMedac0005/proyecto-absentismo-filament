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
            ,
        ];
    }
}
