<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Type;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class RealDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Types
        $typeFestivo = Type::create(['nombre' => 'Festivo']);
        $typeLectivo = Type::create(['nombre' => 'Lectivo']);
        $typeVacaciones = Type::create(['nombre' => 'Vacaciones']);

        // 2. Calendar (Holidays 2024-2025)
        $holidays = [
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

        foreach ($holidays as $date) {
            Calendar::create([
                'fecha' => Carbon::parse($date),
                'descripcion' => 'Festivo',
                'type_id' => $typeFestivo->id,
            ]);
        }

        // 3. Teachers
        $teachersNames = [
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

        $teachers = [];
        foreach ($teachersNames as $name) {
            $teachers[] = \App\Models\Teacher::create([
                'nombre' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@example.com',
                'telefono' => '600' . rand(100000, 999999),
            ]);
        }

        // 4. Courses
        $coursesData = [
            ['nombre' => '1º DAW', 'grado' => 'primero'],
            ['nombre' => '2º DAW', 'grado' => 'segundo'],
            ['nombre' => '1º DAM', 'grado' => 'primero'],
            ['nombre' => '2º DAM', 'grado' => 'segundo'],
            ['nombre' => '1º ASIR', 'grado' => 'primero'],
            ['nombre' => '2º ASIR', 'grado' => 'segundo'],
        ];

        $trimesterDates = [
            'trimestre_1_inicio' => '2024-09-15',
            'trimestre_1_fin' => '2024-12-22',
            'trimestre_2_inicio' => '2025-01-08',
            'trimestre_2_fin' => '2025-04-13',
            'trimestre_3_inicio' => '2025-04-21',
            'trimestre_3_fin' => '2025-06-20',
        ];

        foreach ($coursesData as $data) {
            $course = Course::create(array_merge($data, $trimesterDates));
            $this->createSubjectsForCourse($course, $teachers);
        }
    }

    private function createSubjectsForCourse(Course $course, array $teachers)
    {
        $subjects = [];

        if (str_contains($course->nombre, 'DAW')) {
            if ($course->grado === 'primero') {
                $subjects = [
                    'Programación' => 8,
                    'Bases de Datos' => 6,
                    'Entornos de Desarrollo' => 3,
                    'Lenguajes de Marcas' => 4,
                    'Sistemas Informáticos' => 5,
                    'Formación y Orientación Laboral' => 3,
                ];
            } else {
                $subjects = [
                    'Desarrollo Web en Entorno Cliente' => 7,
                    'Desarrollo Web en Entorno Servidor' => 8,
                    'Despliegue de Aplicaciones Web' => 4,
                    'Diseño de Interfaces Web' => 6,
                    'Empresa e Iniciativa Emprendedora' => 3,
                ];
            }
        } elseif (str_contains($course->nombre, 'DAM')) {
            if ($course->grado === 'primero') {
                $subjects = [
                    'Programación' => 8,
                    'Bases de Datos' => 6,
                    'Entornos de Desarrollo' => 3,
                    'Lenguajes de Marcas' => 4,
                    'Sistemas Informáticos' => 5,
                    'Formación y Orientación Laboral' => 3,
                ];
            } else {
                $subjects = [
                    'Acceso a Datos' => 6,
                    'Desarrollo de Interfaces' => 6,
                    'Programación Multimedia y Disp. Móviles' => 5,
                    'Programación de Servicios y Procesos' => 4,
                    'Sistemas de Gestión Empresarial' => 5,
                    'Empresa e Iniciativa Emprendedora' => 3,
                ];
            }
        } elseif (str_contains($course->nombre, 'ASIR')) {
            if ($course->grado === 'primero') {
                $subjects = [
                    'Fundamentos de Hardware' => 3,
                    'Gestión de Bases de Datos' => 5,
                    'Implantación de Sist. Operativos' => 8,
                    'Lenguajes de Marcas' => 3,
                    'Planificación y Admin. de Redes' => 6,
                    'Formación y Orientación Laboral' => 3,
                ];
            } else {
                $subjects = [
                    'Admin. de Sist. Gestores de Bases de Datos' => 3,
                    'Admin. de Sistemas Operativos' => 6,
                    'Implantación de Aplicaciones Web' => 5,
                    'Servicios de Red e Internet' => 6,
                    'Seguridad y Alta Disponibilidad' => 5,
                    'Empresa e Iniciativa Emprendedora' => 3,
                ];
            }
        }

        foreach ($subjects as $name => $hours) {
            $subject = Subject::create([
                'nombre' => $name,
                'horas_semanales' => $hours,
                'grado' => $course->grado,
                'course_id' => $course->id,
            ]);

            // Assign a random teacher
            $teacher = $teachers[array_rand($teachers)];
            \App\Models\SubjectTeacher::create([
                'subject_id' => $subject->id,
                'teacher_id' => $teacher->id,
            ]);

            $this->createScheduleForSubject($subject, $hours);
        }
    }

    private function createScheduleForSubject(Subject $subject, int $weeklyHours)
    {
        // Simple distribution logic: distribute hours across M-F
        $days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];
        $hoursLeft = $weeklyHours;
        $dayIndex = 0;

        while ($hoursLeft > 0) {
            $hoursToday = min(2, $hoursLeft); // Max 2 hours per session per day
            
            Schedule::create([
                'dia_semana' => $days[$dayIndex % 5],
                'horas' => $hoursToday,
                'subject_id' => $subject->id,
            ]);

            $hoursLeft -= $hoursToday;
            $dayIndex++;
        }
    }
}
