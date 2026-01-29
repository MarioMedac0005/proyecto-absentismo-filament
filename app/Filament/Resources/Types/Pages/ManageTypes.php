<?php

namespace App\Filament\Resources\Types\Pages;

use App\Filament\Resources\Types\TypeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageTypes extends ManageRecords
{
    protected static string $resource = TypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
            ->label('Crear Tipo')
            ->modalHeading('Crear Tipo')
            ->modalDescription('Unicamente registre dias no lectivos como (festivo, vacaciones, periodo de practicas, etc). Por defecto, los dias no registrados se consideran lectivos.')
            ->modalIcon('heroicon-o-book-open')
            ->modalWidth('xl')
            ,
        ];
    }
    public function getSubheading(): ?string
    {
        return 'Gestión de los tipos de días (lectivos, festivos, vacaciones...).';
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl() => $this->getResource()::getBreadcrumb(),
            null => 'Listado',
        ];
    }
}
