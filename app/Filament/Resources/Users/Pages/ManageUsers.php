<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageUsers extends ManageRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear profesor')
                ->modalHeading('Crear profesor'),
        ];
    }

    public function getSubheading(): ?string
    {
        return 'Gestión de los profesores de la aplicación.';
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl() => $this->getResource()::getBreadcrumb(),
            null => 'Listado',
        ];
    }
}
