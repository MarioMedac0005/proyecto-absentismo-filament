<?php

namespace Tests\Feature;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrimesterHoursTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_trimester_hours_correctly()
    {
        // 1. Create a Course with trimester dates
        $course = Course::factory()->create([
            'trimestre_1_inicio' => '2024-01-01', // Monday
            'trimestre_1_fin' => '2024-01-14',    // Sunday (2 weeks)
        ]);

        // 2. Create a Subject for that course
        $subject = Subject::factory()->create([
            'course_id' => $course->id,
        ]);

        // 3. Create a Schedule for the subject (Monday 2 hours)
        Schedule::factory()->create([
            'subject_id' => $subject->id,
            'dia_semana' => 'lunes',
            'horas' => 2,
        ]);

        // 4. Create a Calendar entry (holiday) on the first Monday
        $type = Type::factory()->create(['nombre' => 'Festivo']);
        Calendar::factory()->create([
            'fecha' => \Carbon\Carbon::parse('2024-01-01'),
            'type_id' => $type->id,
        ]);

        // 5. Calculate hours
        // Mondays in range: 2024-01-01, 2024-01-08
        // 2024-01-01 is holiday
        // Only 2024-01-08 counts -> 2 hours
        
        $hours = $subject->calculateTrimesterHours(1);

        $this->assertEquals(2, $hours);
    }

    public function test_returns_zero_if_dates_missing()
    {
        $course = Course::factory()->create([
            'trimestre_1_inicio' => null,
            'trimestre_1_fin' => null,
        ]);

        $subject = Subject::factory()->create([
            'course_id' => $course->id,
        ]);

        $hours = $subject->calculateTrimesterHours(1);

        $this->assertEquals(0, $hours);
    }
}
