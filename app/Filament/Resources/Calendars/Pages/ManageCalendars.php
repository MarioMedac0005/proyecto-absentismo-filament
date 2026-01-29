<?php

namespace App\Filament\Resources\Calendars\Pages;

use App\Filament\Resources\Calendars\CalendarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ManageCalendars extends ManageRecords
{
    protected static string $resource = CalendarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Crear Calendario')
                ->modalHeading('Crear Calendario')
                ->modalDescription('Introduce los datos del calendario')
                ->modalIcon('heroicon-o-calendar')
                ->modalWidth('xl')
                ->using(function (array $data, string $model): Model {
                    $isRange = $data['is_range'] ?? false;

                    if ($isRange) {
                        $startDate = Carbon::parse($data['start_date']);
                        $endDate = Carbon::parse($data['end_date']);
                        $typeId = $data['type_id'];
                        $description = $data['descripcion'] ?? null;
                        
                        $firstRecord = null;
                        $currentDate = $startDate->copy();

                        while ($currentDate->lte($endDate)) {
                            // Usamos updateOrCreate para evitar duplicados y actualizar si ya existe
                            $record = $model::updateOrCreate(
                                ['fecha' => $currentDate->format('Y-m-d')],
                                [
                                    'type_id' => $typeId,
                                    'descripcion' => $description,
                                ]
                            );

                            if (! $firstRecord) {
                                $firstRecord = $record;
                            }

                            $currentDate->addDay();
                        }

                        return $firstRecord;
                    }

                    // Modo simple
                    return $model::create([
                        'fecha' => $data['fecha'],
                        'type_id' => $data['type_id'],
                        'descripcion' => $data['descripcion'] ?? null,
                    ]);
                })
                ->successNotificationTitle('Calendario creado correctamente'),
        ];
    }
    public function getSubheading(): ?string
    {
        return 'Gestión del calendario escolar, días festivos y eventos.';
    }

    public function getBreadcrumbs(): array
    {
        return [
            $this->getResource()::getUrl() => $this->getResource()::getBreadcrumb(),
            null => 'Listado',
        ];
    }
}
