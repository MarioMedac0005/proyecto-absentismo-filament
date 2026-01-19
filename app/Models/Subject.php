<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Calendar;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'nombre',
        'horas_semanales',
        'grado',
        'course_id'
    ];

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function calcularHorasPorTrimestre(int $trimestre = null): int
    {
        $curso = $this->course;

        if (!$curso) {
            return 0;
        }

        $inicio = $curso->{"trimestre_{$trimestre}_inicio"};
        $fin = $curso->{"trimestre_{$trimestre}_fin"};

        if (!$inicio || !$fin) {
            return 0;
        }

        $inicio = Carbon::parse($inicio);
        $fin = Carbon::parse($fin);

        // Obtener días festivos y vacaciones en el rango
        $diasNoLectivos = Calendar::whereBetween('fecha', [$inicio, $fin])
            ->pluck('fecha')
            ->map(fn($fecha) => Carbon::parse($fecha)->format('Y-m-d'))
            ->toArray();

        // Obtener horario de la asignatura agrupado por día de la semana
        $horarioPorDia = $this->schedules->pluck('horas', 'dia_semana')->toArray();

        $mapaDias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        ];

        $horasTotales = 0;

        // Iterar día a día
        for ($date = $inicio->copy(); $date->lte($fin); $date->addDay()) {
            $fechaString = $date->format('Y-m-d');

            // Si es festivo o vacaciones, saltar
            if (in_array($fechaString, $diasNoLectivos)) {
                continue;
            }

            $diaSemanaNum = $date->dayOfWeekIso;
            $diaSemanaNombre = $mapaDias[$diaSemanaNum] ?? '';

            if (isset($horarioPorDia[$diaSemanaNombre])) {
                $horasTotales += $horarioPorDia[$diaSemanaNombre];
            }
        }

        return $horasTotales;
    }
}
