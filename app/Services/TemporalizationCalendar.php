<?php

namespace App\Services;

use App\Models\Calendar;
use App\Models\Course;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonPeriod;

class TemporalizationCalendar
{
    public function buildFromCourse(Course $course)
    {
        $inicio = $course->trimestre_1_inicio;
        $fin = $course->trimestre_3_fin;

        if (!$inicio || !$fin) {
            throw new \Exception('El curso no tiene definidas las fechas de inicio del 1er trimestre o fin del 3er trimestre.');
        }

        // Construir los datos del calendario y obtener las fechas importantes
        $datos = $this->build($inicio, $fin, $course);
        $datos['fechasMarcadas'] = $this->getMarkedDates($inicio, $fin);
        
        return $datos;
    }

    public function build($inicio, $fin, ?Course $course = null)
    {
        // Crear un periodo iterable por días
        $periodo = CarbonPeriod::create($inicio, $fin);

        // Obtener eventos (festivos, evaluaciones, etc.) agrupados por fecha
        $eventos = Calendar::whereBetween('fecha', [$inicio, $fin])
            ->with('type')
            ->orderBy('fecha')
            ->get()
            ->groupBy('fecha');

        $meses = [];

        // Recorrer cada día del periodo para armar la estructura por meses
        foreach ($periodo as $fecha) {
            $claveMes = $fecha->format('Y-m');

            // Inicializar el mes si no existe
            if (!isset($meses[$claveMes])) {
                $meses[$claveMes]['nombre'] = $fecha->locale('es')->translatedFormat('F Y');
                $meses[$claveMes]['dias'] = [];
            }
            
            // Añadir el día a la lista del mes
            $meses[$claveMes]['dias'][] = [
                'fecha' => $fecha,
                'dia' => $fecha->day,
                'diaSemana' => $fecha->dayOfWeekIso, // 1 (Lunes) a 7 (Domingo)
                'eventos' => $eventos[$fecha->toDateString()] ?? [],
                'trimestre' => $this->getTrimester($fecha, $course),
            ];
        }

        // Rellenar los meses para asegurar una altura uniforme (6 semanas = 42 días)
        foreach ($meses as &$mes) {
            $primerDia = $mes['dias'][0];
            // Calcular cuántos huecos vacíos poner antes del día 1
            $inicioVacio = $primerDia['diaSemana'] - 1;

            // Rellenar días vacíos al principio
            $rellenoInicio = array_fill(0, $inicioVacio, null);
            $mes['dias'] = array_merge($rellenoInicio, $mes['dias']);

            // Rellenar días vacíos al final hasta llegar a 42
            $conteo = count($mes['dias']);
            if ($conteo < 42) {
                $mes['dias'] = array_merge($mes['dias'], array_fill(0, 42 - $conteo, null));
            }
        }

        return ['meses' => $meses];
    }
    
    private function getMarkedDates($inicio, $fin) {
        // Recuperar eventos especiales en el rango de fechas
        return Calendar::whereBetween('fecha', [$inicio, $fin])
            ->with('type')
            ->orderBy('fecha')
            ->get();
    }

    private function getTrimester($fecha, Course $course)
    {
        // Determinar a qué trimestre pertenece la fecha dada
        if ($fecha->between(
            $course->trimestre_1_inicio,
            $course->trimestre_1_fin
        )) {
            return 1;
        }

        if ($fecha->between(
            $course->trimestre_2_inicio,
            $course->trimestre_2_fin
        )) {
            return 2;
        }

        if ($fecha->between(
            $course->trimestre_3_inicio,
            $course->trimestre_3_fin
        )) {
            return 3;
        }

        return null;
    }

    public function generatePdf(Course $course)
    {
        $datos = $this->buildFromCourse($course);

        // Generar el PDF usando la vista y los datos procesados
        $pdf = Pdf::loadView('pdf.temporalization-calendar', [
            'course' => $course,
            'meses' => $datos['meses'],
            'fechasMarcadas' => $datos['fechasMarcadas'],
        ])->setPaper('a4', 'landscape');

        return response()->streamDownload(
            fn () => print($pdf->output()),
            'temporalizacion-' . $course->slug . '.pdf'
        );
    }
}