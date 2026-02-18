<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\SubjectUser;
use App\Models\Type;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * RealDataSeeder — Carga los datos reales del curso escolar 2024-2025 en la base de datos.
 *
 * Este seeder se ejecuta con: php artisan db:seed --class=RealDataSeeder
 * (o con php artisan migrate:fresh --seed si se quiere resetear todo primero)
 *
 * Crea en orden:
 * 1. Tipos de días del calendario (festivo, vacaciones, evaluación, examen)
 * 2. Días festivos nacionales y autonómicos
 * 3. Periodos de vacaciones escolares
 * 4. Días de evaluación y exámenes
 * 5. Los dos profesores reales del sistema
 * 6. Los 6 cursos del ciclo formativo (DAW, DAM, ASIR)
 * 7. Las asignaturas de cada curso con sus horarios
 */
class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================================
        // 1. TIPOS DE DÍAS DEL CALENDARIO
        // Cada tipo tiene un nombre y un color para mostrarse en el panel.
        // =====================================================================
        $tipoFestivo = Type::create([
            'nombre' => 'Festivo',
            'color' => '#ff4141', // Rojo
        ]);

        $tipoLectivo = Type::create([
            'nombre' => 'Lectivo',
            // Sin color especial — es el tipo por defecto
        ]);

        $tipoVacaciones = Type::create([
            'nombre' => 'Vacaciones',
            'color' => '#42ff7d', // Verde
        ]);

        $tipoEvaluacion = Type::create([
            'nombre' => 'Evaluación',
            'color' => '#f1c40f', // Amarillo
        ]);

        $tipoExamen = Type::create([
            'nombre' => 'Examen',
            'color' => '#e67e22', // Naranja
        ]);

        // =====================================================================
        // 2. FESTIVOS NACIONALES Y AUTONÓMICOS (curso 2024-2025)
        // Estos días se descontarán del cálculo de horas lectivas.
        // =====================================================================
        $diasFestivos = [
            '2024-10-12', // Fiesta Nacional de España
            '2024-11-01', // Todos los Santos
            '2024-12-06', // Día de la Constitución
            '2024-12-08', // Inmaculada Concepción
            '2024-12-25', // Navidad
            '2025-01-01', // Año Nuevo
            '2025-01-06', // Reyes
            '2025-02-28', // Día de Andalucía
            '2025-04-17', // Jueves Santo
            '2025-04-18', // Viernes Santo
            '2025-05-01', // Fiesta del Trabajo
        ];

        foreach ($diasFestivos as $fecha) {
            Calendar::create([
                'fecha' => Carbon::parse($fecha),
                'descripcion' => 'Festivo',
                'type_id' => $tipoFestivo->id,
            ]);
        }

        // =====================================================================
        // 3. VACACIONES ESCOLARES — Se crean día a día dentro del periodo.
        // Se comprueba que no exista ya un festivo en esa fecha para no duplicar.
        // =====================================================================
        $periodosVacaciones = [
            // Vacaciones de Navidad
            ['inicio' => '2024-12-23', 'fin' => '2025-01-07', 'descripcion' => 'Vacaciones de Navidad'],
            // Vacaciones de Semana Santa
            ['inicio' => '2025-04-14', 'fin' => '2025-04-21', 'descripcion' => 'Vacaciones de Semana Santa'],
        ];

        foreach ($periodosVacaciones as $periodo) {
            $inicio = Carbon::parse($periodo['inicio']);
            $fin    = Carbon::parse($periodo['fin']);

            // Iterar cada día del periodo de vacaciones
            for ($fecha = $inicio->copy(); $fecha->lte($fin); $fecha->addDay()) {
                // Evitar duplicar si ya existe un festivo en esa fecha
                $existeFestivo = Calendar::where('fecha', $fecha->format('Y-m-d'))->exists();
                if (!$existeFestivo) {
                    Calendar::create([
                        'fecha' => $fecha->format('Y-m-d'),
                        'descripcion' => $periodo['descripcion'],
                        'type_id' => $tipoVacaciones->id,
                    ]);
                }
            }
        }

        // =====================================================================
        // 4. DÍAS DE EVALUACIÓN — Uno al final de cada trimestre
        // =====================================================================
        $diasEvaluacion = [
            '2024-12-20', // Final 1er trimestre
            '2025-03-28', // Final 2do trimestre
            '2025-06-19', // Final 3er trimestre
        ];

        foreach ($diasEvaluacion as $fecha) {
            Calendar::create([
                'fecha' => Carbon::parse($fecha),
                'descripcion' => 'Día de Evaluación',
                'type_id' => $tipoEvaluacion->id,
            ]);
        }

        // =====================================================================
        // 5. DÍAS DE EXAMEN — Dos días antes de cada evaluación
        // =====================================================================
        $diasExamen = [
            '2024-12-18', '2024-12-19', // Exámenes finales 1er trimestre
            '2025-03-26', '2025-03-27', // Exámenes finales 2do trimestre
            '2025-06-17', '2025-06-18', // Exámenes finales 3er trimestre
        ];

        foreach ($diasExamen as $fecha) {
            Calendar::create([
                'fecha' => Carbon::parse($fecha),
                'descripcion' => 'Examen',
                'type_id' => $tipoExamen->id,
            ]);
        }

        // =====================================================================
        // 6. PROFESORES — Solo los dos usuarios reales del sistema
        // Se les asigna el rol 'profesor' con Spatie Permission.
        // =====================================================================
        $mario = User::create([
            'name' => 'Mario Ortiz Hidalgo',
            'email' => 'moh0005@alu.medac.es',
            'password' => Hash::make('Usuario123'),
        ]);
        $mario->assignRole('profesor');

        $javier = User::create([
            'name' => 'Javier Ruiz',
            'email' => 'javier.ruiz@doc.medac.es',
            'password' => Hash::make('Usuario123'),
        ]);
        $javier->assignRole('profesor');

        $profesoresCreados = [$mario, $javier];

        // =====================================================================
        // 7. CONFIGURACIÓN DE CURSOS — Fechas de trimestres del curso 2024-2025
        // Estas fechas son compartidas por todos los cursos del sistema.
        // =====================================================================
        $fechasTrimestres = [
            'inicio_curso' => '2024',
            'fin_curso' => '2025',
            'trimestre_1_inicio' => '2024-09-15',
            'trimestre_1_fin' => '2024-12-22',
            'trimestre_2_inicio' => '2025-01-08',
            'trimestre_2_fin' => '2025-04-13',
            'trimestre_3_inicio' => '2025-04-21',
            'trimestre_3_fin' => '2025-06-20',
        ];

        // Lista de los 6 cursos del sistema (DAW, DAM y ASIR en 1º y 2º)
        $listadoCursos = [
            ['nombre' => '1º DAW', 'grado' => 'primero'],
            ['nombre' => '2º DAW', 'grado' => 'segundo'],
            ['nombre' => '1º DAM', 'grado' => 'primero'],
            ['nombre' => '2º DAM', 'grado' => 'segundo'],
            ['nombre' => '1º ASIR', 'grado' => 'primero'],
            ['nombre' => '2º ASIR', 'grado' => 'segundo'],
        ];

        // =====================================================================
        // 8. MALLA CURRICULAR — Asignaturas y horas semanales de cada curso
        // Clave: nombre del curso | Valor: array [nombre_asignatura => horas_semanales]
        // =====================================================================
        $mallaCurricular = [
            '1º DAW' => [
                'Programación' => 8,
                'Bases de Datos' => 6,
                'Entornos de Desarrollo' => 3,
                'Lenguajes de Marcas' => 4,
                'Sistemas Informáticos' => 5,
                'Formación y Orientación Laboral' => 3,
            ],
            '2º DAW' => [
                'Desarrollo Web en Entorno Cliente' => 7,
                'Desarrollo Web en Entorno Servidor' => 8,
                'Despliegue de Aplicaciones Web' => 4,
                'Diseño de Interfaces Web' => 6,
                'Empresa e Iniciativa Emprendedora' => 3,
            ],
            '1º DAM' => [
                'Programación' => 8,
                'Bases de Datos' => 6,
                'Entornos de Desarrollo' => 3,
                'Lenguajes de Marcas' => 4,
                'Sistemas Informáticos' => 5,
                'Formación y Orientación Laboral' => 3,
            ],
            '2º DAM' => [
                'Acceso a Datos' => 6,
                'Desarrollo de Interfaces' => 6,
                'Programación Multimedia y Disp. Móviles' => 5,
                'Programación de Servicios y Procesos' => 4,
                'Sistemas de Gestión Empresarial' => 5,
                'Empresa e Iniciativa Emprendedora' => 3,
            ],
            '1º ASIR' => [
                'Fundamentos de Hardware' => 3,
                'Gestión de Bases de Datos' => 5,
                'Implantación de Sist. Operativos' => 8,
                'Lenguajes de Marcas' => 3,
                'Planificación y Admin. de Redes' => 6,
                'Formación y Orientación Laboral' => 3,
            ],
            '2º ASIR' => [
                'Admin. de Sist. Gestores de Bases de Datos' => 3,
                'Admin. de Sistemas Operativos' => 6,
                'Implantación de Aplicaciones Web' => 5,
                'Servicios de Red e Internet' => 6,
                'Seguridad y Alta Disponibilidad' => 5,
                'Empresa e Iniciativa Emprendedora' => 3,
            ],
        ];

        // Asignaturas de Mario en 1º DAW (solo estas 3 tienen horario asignado)
        $asignaturasMario = [
            'Programación' => 8,
            'Bases de Datos' => 6,
            'Entornos de Desarrollo' => 3,
        ];

        // Asignaturas de Javier en 1º DAM (solo estas 3 tienen horario asignado)
        $asignaturasJavier = [
            'Programación' => 8,
            'Bases de Datos' => 6,
            'Sistemas Informáticos' => 5,
        ];

        // =====================================================================
        // BUCLE PRINCIPAL — Crear cursos, asignaturas y horarios
        // =====================================================================
        foreach ($listadoCursos as $datosCurso) {
            // Crear el curso combinando sus datos con las fechas de trimestres
            $curso = Course::create(array_merge($datosCurso, $fechasTrimestres));

            // Obtener las asignaturas de la malla curricular para este curso
            $asignaturasDelCurso = $mallaCurricular[$curso->nombre] ?? [];

            foreach ($asignaturasDelCurso as $nombreAsignatura => $horasSemanales) {
                // Crear la asignatura en la base de datos
                $asignatura = Subject::create([
                    'nombre' => $nombreAsignatura,
                    'horas_semanales' => $horasSemanales,
                    'grado' => $curso->grado,
                    'course_id' => $curso->id,
                ]);

                // Asignar Mario a sus asignaturas de 1º DAW y crear su horario
                if ($curso->nombre === '1º DAW' && isset($asignaturasMario[$nombreAsignatura])) {
                    SubjectUser::create(['subject_id' => $asignatura->id, 'user_id' => $mario->id]);
                    $this->crearHorario($asignatura, $horasSemanales, $mario->id);
                }

                // Asignar Javier a sus asignaturas de 1º DAM y crear su horario
                if ($curso->nombre === '1º DAM' && isset($asignaturasJavier[$nombreAsignatura])) {
                    SubjectUser::create(['subject_id' => $asignatura->id, 'user_id' => $javier->id]);
                    $this->crearHorario($asignatura, $horasSemanales, $javier->id);
                }
            }
        }
    }

    /**
     * Distribuye las horas semanales de una asignatura entre los días de la semana.
     *
     * Algoritmo: Asigna un máximo de 2 horas por día, repartiendo de lunes a viernes
     * de forma cíclica hasta agotar todas las horas semanales.
     *
     * Ejemplo: 8 horas semanales → lunes 2h, martes 2h, miércoles 2h, jueves 2h.
     * Ejemplo: 3 horas semanales → lunes 2h, martes 1h.
     *
     * @param Subject $asignatura    La asignatura a la que pertenece el horario.
     * @param int     $horasSemanales Total de horas a distribuir en la semana.
     * @param int     $userId        ID del profesor al que se asigna el horario.
     */
    private function crearHorario(Subject $asignatura, int $horasSemanales, int $userId): void
    {
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $horasRestantes = $horasSemanales;
        $indiceDia = 0; // Índice para recorrer los días de la semana

        while ($horasRestantes > 0) {
            // Asignar máximo 2 horas por día (o las que queden si son menos de 2)
            $horasHoy = min(2, $horasRestantes);

            Schedule::create([
                'dia_semana' => $diasSemana[$indiceDia % 5], // % 5 para volver a lunes si se supera viernes
                'horas' => $horasHoy,
                'subject_id' => $asignatura->id,
                'user_id' => $userId,
            ]);

            $horasRestantes -= $horasHoy;
            $indiceDia++;
        }
    }
}
