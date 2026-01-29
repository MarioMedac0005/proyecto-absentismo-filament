<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Calendar;
use Carbon\CarbonPeriod;
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

    public function subjectUsers()
    {
        return $this->hasMany(SubjectUser::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function calcularHorasPorTrimestre(?int $trimestre): int
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

        // Fechas no lectivas (festivos/vacaciones)
        // Usamos flip() para búsqueda O(1)
        $diasNoLectivos = Calendar::whereBetween('fecha', [$inicio, $fin])
            ->pluck('fecha')
            ->map(fn ($f) => Carbon::parse($f)->toDateString())
            ->flip();

        // Horas por día de la semana
        $horarioPorDia = $this->schedules->pluck('horas', 'dia_semana');

        // Mapa explícito para evitar problemas con tildes (miércoles vs miercoles)
        $mapaDias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        ];

        // Convertimos CarbonPeriod a Collection para usar reject/sum
        return collect(CarbonPeriod::create($inicio, $fin))
            ->reject(fn ($date) => $diasNoLectivos->has($date->toDateString()))
            ->sum(function ($date) use ($horarioPorDia, $mapaDias) {
                // Usamos dayOfWeekIso (1=Lunes, 7=Domingo) para mapear al string sin tildes de la BD
                $diaKey = $mapaDias[$date->dayOfWeekIso] ?? '';
                return $horarioPorDia[$diaKey] ?? 0;
            });
    }
}
