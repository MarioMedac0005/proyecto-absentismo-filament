<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'dia_semana' => fake()->randomElement(['lunes', 'martes', 'miercoles', 'jueves', 'viernes']),
            'horas' => fake()->numberBetween(1, 4),
            'subject_id' => \App\Models\Subject::factory(),
        ];
    }
}
