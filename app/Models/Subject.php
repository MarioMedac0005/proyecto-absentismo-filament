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

    // Caché estático para días no lectivos: evita queries repetidas al Calendar por request
    private static array $diasNoLectivosCache = [];

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

    public function calcularHorasPorTrimestre(?int $trimestre, ?int $userId = null): int
    {
        $curso = $this->course;
        if (!$curso) {
            return 0;
        }

        $inicio = $curso->{"trimestre_{$trimestre}_inicio"};
        $fin    = $curso->{"trimestre_{$trimestre}_fin"};

        if (!$inicio || !$fin) {
            return 0;
        }

        $inicio = Carbon::parse($inicio);
        $fin    = Carbon::parse($fin);

        // Caché de días no lectivos por rango (evita N queries al Calendar)
        $cacheKey = $inicio->toDateString() . '_' . $fin->toDateString();
        if (!isset(self::$diasNoLectivosCache[$cacheKey])) {
            self::$diasNoLectivosCache[$cacheKey] = Calendar::whereBetween('fecha', [$inicio, $fin])
                ->pluck('fecha')
                ->map(fn ($f) => Carbon::parse($f)->toDateString())
                ->flip();
        }
        $diasNoLectivos = self::$diasNoLectivosCache[$cacheKey];

        // Horas por día de la semana — filtrar por profesor si se indica
        $schedulesQuery = $this->schedules();
        if ($userId !== null) {
            $schedulesQuery = $schedulesQuery->where('user_id', $userId);
        }
        $horarioPorDia = $schedulesQuery->get()->pluck('horas', 'dia_semana');

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

        return collect(CarbonPeriod::create($inicio, $fin))
            ->reject(fn ($date) => $diasNoLectivos->has($date->toDateString()))
            ->sum(function ($date) use ($horarioPorDia, $mapaDias) {
                $diaKey = $mapaDias[$date->dayOfWeekIso] ?? '';
                return $horarioPorDia[$diaKey] ?? 0;
            });
    }
}
