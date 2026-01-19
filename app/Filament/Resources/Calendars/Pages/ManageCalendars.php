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
                /* ->using(function (array $data, string $model): Model {
                    // Check if we are in 'range' mode
                    if (($data['tipo_fecha'] ?? 'dia') === 'rango' && !empty($data['rango'])) {
                        
                        // Filament range picker usually returns "YYYY-MM-DD - YYYY-MM-DD"
                        // We split the string to get start and end dates
                        $dates = explode(' - ', $data['rango']);
                        
                        if (count($dates) === 2) {
                            $startDate = Carbon::parse($dates[0]);
                            $endDate = Carbon::parse($dates[1]);
                            
                            $record = null;

                            // Loop through each day
                            while ($startDate->lte($endDate)) {
                                $recordData = $data;
                                
                                // Set the specific date for this record
                                $recordData['fecha'] = $startDate->format('Y-m-d');
                                
                                // Create the record
                                $record = $model::create($recordData);
                                
                                // Move to next day
                                $startDate->addDay();
                            }

                            // Return the last created record to satisfy the method signature
                            return $record;
                        }
                    }

                    // Fallback for single day selection
                    return $model::create($data);
                }), */
        ];
    }
}
