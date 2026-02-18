<?php

namespace App\Models;

use Carbon\Carbon;
use App\Models\Calendar;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Subject — Representa una asignatura del currículo escolar.
 *
 * Es el modelo central del proyecto. Contiene la lógica principal para calcular
 * cuántas horas lectivas tiene una asignatura en cada trimestre, descontando
 * automáticamente los días no lectivos del calendario (festivos, vacaciones...).
 */
class Subject extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables masivamente.
     * - 'nombre'         : Nombre de la asignatura (ej: "Programación").
     * - 'horas_semanales': Total de horas lectivas por semana.
     * - 'grado'          : 'primero' o 'segundo' (curso del ciclo formativo).
     * - 'course_id'      : Curso al que pertenece esta asignatura.
     */
    protected $fillable = [
        'nombre',
        'horas_semanales',
        'grado',
        'course_id'
    ];

    /**
     * Caché estático de días no lectivos, indexado por rango de fechas.
     *
     * Al ser estático, persiste durante toda la petición HTTP. Esto evita
     * hacer la misma consulta SQL al calendario múltiples veces cuando se
     * calculan horas de varias asignaturas con el mismo rango de fechas.
     *
     * Clave: "YYYY-MM-DD_YYYY-MM-DD" (inicio_fin del trimestre)
     * Valor: Colección con las fechas no lectivas como claves (para búsqueda O(1))
     */
    private static array $diasNoLectivosCache = [];

    /**
     * Relación: Una asignatura tiene muchos registros en la tabla pivote subject_users.
     */
    public function subjectUsers()
    {
        return $this->hasMany(SubjectUser::class);
    }

    /**
     * Relación: Una asignatura pertenece a un curso (ej: "1º DAW").
     * Se usa para obtener las fechas de los trimestres.
     */
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Relación: Una asignatura tiene muchos horarios semanales (Schedule).
     * Cada horario indica en qué día de la semana y cuántas horas se imparte.
     */
    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    /**
     * Calcula el total de horas lectivas de esta asignatura en un trimestre dado.
     *
     * El cálculo tiene en cuenta:
     * 1. Las fechas de inicio y fin del trimestre (obtenidas del curso asociado).
     * 2. Los días no lectivos del calendario (festivos, vacaciones...) en ese rango.
     * 3. El horario semanal de la asignatura (qué días y cuántas horas).
     * 4. Opcionalmente, filtra el horario por un profesor concreto ($userId).
     *
     * @param int|null $trimestre  Número del trimestre (1, 2 o 3).
     * @param int|null $userId     ID del profesor. Si es null, suma todos los horarios.
     * @return int                 Total de horas lectivas en el trimestre.
     */
    public function calcularHorasPorTrimestre(?int $trimestre, ?int $userId = null): int
    {
        // Obtener el curso asociado a esta asignatura
        $curso = $this->course;
        if (!$curso) {
            return 0; // Si no tiene curso asignado, no se puede calcular
        }

        // Leer las fechas del trimestre usando acceso dinámico a la propiedad
        // Ejemplo para trimestre 1: $curso->trimestre_1_inicio y $curso->trimestre_1_fin
        $inicio = $curso->{"trimestre_{$trimestre}_inicio"};
        $fin    = $curso->{"trimestre_{$trimestre}_fin"};

        if (!$inicio || !$fin) {
            return 0; // Si las fechas no están configuradas, devolver 0
        }

        // Convertir a objetos Carbon para poder iterar y comparar fechas
        $inicio = Carbon::parse($inicio);
        $fin    = Carbon::parse($fin);

        // --- OPTIMIZACIÓN: Caché de días no lectivos ---
        // Se construye una clave única para este rango de fechas
        $cacheKey = $inicio->toDateString() . '_' . $fin->toDateString();

        // Si no está en caché, consultar la BD y guardarlo
        if (!isset(self::$diasNoLectivosCache[$cacheKey])) {
            self::$diasNoLectivosCache[$cacheKey] = Calendar::whereBetween('fecha', [$inicio, $fin])
                ->pluck('fecha')
                ->map(fn ($f) => Carbon::parse($f)->toDateString())
                ->flip(); // Convertir a claves para búsqueda rápida O(1) con ->has()
        }
        $diasNoLectivos = self::$diasNoLectivosCache[$cacheKey];

        // --- Obtener el horario semanal ---
        // Si se especifica un profesor, filtrar solo su horario
        $schedulesQuery = $this->schedules();
        if ($userId !== null) {
            $schedulesQuery = $schedulesQuery->where('user_id', $userId);
        }
        // Resultado: ['lunes' => 2, 'martes' => 2, 'miercoles' => 1, ...]
        $horarioPorDia = $schedulesQuery->get()->pluck('horas', 'dia_semana');

        // Mapa explícito de número de día ISO a nombre en español.
        // Se usa ISO (lunes=1) en lugar del estándar de Carbon (domingo=0)
        // para evitar problemas con tildes (miércoles → miercoles en la BD).
        $mapaDias = [
            1 => 'lunes',
            2 => 'martes',
            3 => 'miercoles',
            4 => 'jueves',
            5 => 'viernes',
            6 => 'sabado',
            7 => 'domingo',
        ];

        // --- Cálculo final ---
        // CarbonPeriod genera todos los días entre $inicio y $fin (inclusive)
        return collect(CarbonPeriod::create($inicio, $fin))
            // Descartar los días que aparecen en el calendario como no lectivos
            ->reject(fn ($date) => $diasNoLectivos->has($date->toDateString()))
            // Para cada día lectivo, sumar las horas que corresponden a ese día de la semana
            ->sum(function ($date) use ($horarioPorDia, $mapaDias) {
                $diaKey = $mapaDias[$date->dayOfWeekIso] ?? '';
                return $horarioPorDia[$diaKey] ?? 0; // 0 si no hay clase ese día
            });
    }
}
