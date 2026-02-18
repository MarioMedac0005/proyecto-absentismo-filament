<?php

namespace App\Exports;

use App\Models\Subject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

/**
 * SubjectHoursExport — Exporta las horas lectivas por asignatura a un archivo Excel (.xlsx).
 *
 * Implementa varias interfaces de Maatwebsite/Excel para personalizar el archivo:
 * - FromCollection  : Proporciona los datos como una colección de Laravel.
 * - WithHeadings    : Define la fila de cabeceras del Excel.
 * - WithStyles      : Aplica estilos visuales (colores, negrita, alineación).
 * - WithColumnWidths: Define el ancho de cada columna.
 * - WithTitle       : Establece el nombre de la hoja del Excel.
 */
class SubjectHoursExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    /**
     * Nombre de la hoja del archivo Excel.
     */
    public function title(): string
    {
        return 'Horas por Asignatura';
    }

    /**
     * Define las cabeceras de la primera fila del Excel.
     * Cada elemento del array corresponde a una columna (A, B, C...).
     */
    public function headings(): array
    {
        return [
            'Profesor',
            'Asignatura',
            'Curso',
            'Horas 1º Trimestre',
            'Horas 2º Trimestre',
            'Horas 3º Trimestre',
        ];
    }

    /**
     * Genera la colección de datos que se escribirán en el Excel.
     *
     * El comportamiento varía según el rol del usuario autenticado:
     * - Administrador: exporta todas las asignaturas de todos los profesores.
     * - Profesor: exporta solo las asignaturas que él imparte.
     *
     * Las horas de cada trimestre se calculan en tiempo real usando
     * Subject::calcularHorasPorTrimestre(), que descuenta festivos y vacaciones.
     *
     * @return Collection Colección de filas, cada una como array con 6 valores.
     */
    public function collection(): Collection
    {
        $user = auth()->user();

        // Cargar asignaturas con sus relaciones para evitar N+1 queries
        $query = Subject::with(['course', 'subjectUsers.user', 'schedules']);

        // Si es profesor, filtrar solo sus asignaturas asignadas
        if ($user->hasRole('profesor')) {
            $subjectIds = $user->subjects()->pluck('subjects.id');
            $query->whereIn('id', $subjectIds);
        }

        $subjects = $query->orderBy('nombre')->get();

        $rows = collect();

        foreach ($subjects as $subject) {
            // Filtrar los subjectUsers que tienen un usuario válido (no eliminados)
            $subjectUsers = $subject->subjectUsers->filter(fn ($su) => $su->user !== null);

            // Crear una fila por cada profesor asignado a la asignatura
            foreach ($subjectUsers as $su) {
                $userId = $su->user->id;

                // Calcular horas de cada trimestre para este profesor concreto
                $h1 = $subject->calcularHorasPorTrimestre(1, $userId);
                $h2 = $subject->calcularHorasPorTrimestre(2, $userId);
                $h3 = $subject->calcularHorasPorTrimestre(3, $userId);

                // Omitir filas donde el profesor no tiene ninguna hora en ningún trimestre
                if ($h1 === 0 && $h2 === 0 && $h3 === 0) {
                    continue;
                }

                // Añadir la fila a la colección con los 6 valores de las columnas
                $rows->push([
                    $su->user->name,                          // Columna A: Profesor
                    $subject->nombre,                         // Columna B: Asignatura
                    $subject->course?->nombre ?? 'Sin curso', // Columna C: Curso
                    $h1,                                      // Columna D: Horas 1º Tri
                    $h2,                                      // Columna E: Horas 2º Tri
                    $h3,                                      // Columna F: Horas 3º Tri
                ]);
            }
        }

        return $rows;
    }

    /**
     * Define los estilos visuales del Excel.
     *
     * La clave del array es el número de fila a la que se aplica el estilo.
     * Aquí se estiliza la fila 1 (cabeceras):
     * - Texto en blanco y negrita.
     * - Fondo azul oscuro (#1E3A5F).
     * - Texto centrado horizontalmente.
     *
     * @param Worksheet $sheet La hoja de cálculo de PhpSpreadsheet.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }

    /**
     * Define el ancho de cada columna del Excel (en caracteres).
     * Las letras corresponden a las columnas: A=Profesor, B=Asignatura, etc.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 30, // Profesor
            'B' => 30, // Asignatura
            'C' => 25, // Curso
            'D' => 20, // Horas 1º Trimestre
            'E' => 20, // Horas 2º Trimestre
            'F' => 20, // Horas 3º Trimestre
        ];
    }
}
