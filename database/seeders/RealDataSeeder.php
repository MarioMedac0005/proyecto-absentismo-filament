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

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tipos de días
        $tipoFestivo = Type::create([
            'nombre' => 'Festivo',
            'color' => '#ff4141', // Rojo
        ]);

        $tipoLectivo = Type::create([
            'nombre' => 'Lectivo',
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

        // 2. Calendario de Festivos
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

        // 3. Vacaciones - Periodos escolares
        $periodosVacaciones = [
            // Vacaciones de Navidad
            ['inicio' => '2024-12-23', 'fin' => '2025-01-07', 'descripcion' => 'Vacaciones de Navidad'],
            // Vacaciones de Semana Santa
            ['inicio' => '2025-04-14', 'fin' => '2025-04-21', 'descripcion' => 'Vacaciones de Semana Santa'],
        ];

        foreach ($periodosVacaciones as $periodo) {
            $inicio = Carbon::parse($periodo['inicio']);
            $fin = Carbon::parse($periodo['fin']);
            
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

        // 4. Días de evaluación
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

        // 5. Días de examen (ejemplo)
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

        // 6. Profesores
        $nombresProfesores = [
            'Juan Pérez',
            'María García',
            'Antonio López',
            'Carmen Rodríguez',
            'Manuel González',
            'Ana Martínez',
            'Francisco Hernández',
            'Isabel Ruiz',
            'David Sánchez',
            'Laura Díaz',
        ];

        $profesoresCreados = [];

        foreach ($nombresProfesores as $nombre) {
            $profesor = User::create([
                'name' => $nombre,
                'email' => strtolower(str_replace(' ', '.', $nombre)) . '@gmail.com',
                'phone' => '600' . rand(100000, 999999),
                'password' => Hash::make('Usuario123!'),
            ]);

            $profesor->assignRole('profesor');
            $profesoresCreados[] = $profesor;
        }

        // 4. Configuración de Cursos y Fechas
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

        $listadoCursos = [
            ['nombre' => '1º DAW', 'grado' => 'primero'],
            ['nombre' => '2º DAW', 'grado' => 'segundo'],
            ['nombre' => '1º DAM', 'grado' => 'primero'],
            ['nombre' => '2º DAM', 'grado' => 'segundo'],
            ['nombre' => '1º ASIR', 'grado' => 'primero'],
            ['nombre' => '2º ASIR', 'grado' => 'segundo'],
        ];

        // 5. Configuración de Asignaturas (Malla Curricular)
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

        // Bucle principal de creación
        foreach ($listadoCursos as $datosCurso) {
            // Crear el curso con las fechas de trimestres
            $curso = Course::create(array_merge($datosCurso, $fechasTrimestres));

            $asignaturasDelCurso = $mallaCurricular[$curso->nombre] ?? [];

            foreach ($asignaturasDelCurso as $nombreAsignatura => $horasSemanales) {
                $asignatura = Subject::create([
                    'nombre' => $nombreAsignatura,
                    'horas_semanales' => $horasSemanales,
                    'grado' => $curso->grado,
                    'course_id' => $curso->id,
                ]);

                // Asignar profesor aleatorio
                $profesor = $profesoresCreados[array_rand($profesoresCreados)];
                
                SubjectUser::create([
                    'subject_id' => $asignatura->id,
                    'user_id' => $profesor->id,
                ]);

                $this->crearHorario($asignatura, $horasSemanales);
            }
        }
    }

    private function crearHorario(Subject $asignatura, int $horasSemanales): void
    {
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $horasRestantes = $horasSemanales;
        $indiceDia = 0;

        while ($horasRestantes > 0) {
            $horasHoy = min(2, $horasRestantes); // Máximo 2 horas por bloque

            Schedule::create([
                'dia_semana' => $diasSemana[$indiceDia % 5],
                'horas' => $horasHoy,
                'subject_id' => $asignatura->id,
            ]);

            $horasRestantes -= $horasHoy;
            $indiceDia++;
        }
    }
}
