<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Subject>
 */
class SubjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->word(),
            'horas_semanales' => fake()->numberBetween(1, 10),
            'grado' => fake()->randomElement(['primero', 'segundo']),
            'course_id' => \App\Models\Course::factory(),
        ];
    }
}
