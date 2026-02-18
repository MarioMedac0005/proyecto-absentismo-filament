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

class SubjectHoursExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    // Guardamos qué filas son cabecera de asignatura para estilarlas
    private array $subjectHeaderRows = [];
    private array $emptyRows = [];

    public function title(): string
    {
        return 'Horas por Asignatura';
    }

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

    public function collection(): Collection
    {
        $user = auth()->user();

        $query = Subject::with(['course', 'subjectUsers.user', 'schedules']);

        if ($user->hasRole('profesor')) {
            $subjectIds = $user->subjects()->pluck('subjects.id');
            $query->whereIn('id', $subjectIds);
        }

        $subjects = $query->orderBy('nombre')->get();

        $rows = collect();
        // +1 porque la fila 1 es la cabecera de columnas (headings)
        $rowIndex = 2;

        foreach ($subjects as $subject) {
            $subjectUsers = $subject->subjectUsers->filter(fn ($su) => $su->user !== null);

            if ($subjectUsers->isEmpty()) {
                // Asignatura sin profesor: una sola fila
                $rows->push([
                    'Sin asignar',
                    $subject->nombre,
                    $subject->course?->nombre ?? 'Sin curso',
                    $subject->calcularHorasPorTrimestre(1),
                    $subject->calcularHorasPorTrimestre(2),
                    $subject->calcularHorasPorTrimestre(3),
                ]);
                $rowIndex++;
            } else {
                // Una fila por cada profesor con sus propias horas
                foreach ($subjectUsers as $su) {
                    $userId = $su->user->id;
                    $rows->push([
                        $su->user->name,
                        $subject->nombre,
                        $subject->course?->nombre ?? 'Sin curso',
                        $subject->calcularHorasPorTrimestre(1, $userId),
                        $subject->calcularHorasPorTrimestre(2, $userId),
                        $subject->calcularHorasPorTrimestre(3, $userId),
                    ]);
                    $rowIndex++;
                }
            }

            // Fila vacía separadora entre asignaturas
            $rows->push(['', '', '', '', '', '']);
            $this->emptyRows[] = $rowIndex;
            $rowIndex++;
        }

        return $rows;
    }

    public function styles(Worksheet $sheet): array
    {
        $styles = [
            // Cabecera de columnas: fondo azul oscuro, texto blanco, negrita
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1E3A5F']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];

        // Filas vacías separadoras: altura reducida
        foreach ($this->emptyRows as $row) {
            $sheet->getRowDimension($row)->setRowHeight(6);
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // Profesor
            'B' => 30, // Asignatura
            'C' => 25, // Curso
            'D' => 20, // Horas T1
            'E' => 20, // Horas T2
            'F' => 20, // Horas T3
        ];
    }
}
