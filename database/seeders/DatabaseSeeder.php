<?php

namespace Database\Seeders;

use App\Models\Calendar;
use App\Models\Course;
use App\Models\Schedule;
use App\Models\Subject;
use App\Models\SubjectTeacher;
use App\Models\Teacher;
use App\Models\Type;
use App\Models\User;
use Database\Seeders\RealDataSeeder; // Added this line
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RoleSeeder::class);
        $this->call(RealDataSeeder::class);

        $admin = User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Usuario123'),
        ]);
        $admin->assignRole('admin');

        $profesor = User::factory()->create([
            'name' => 'profesor',
            'email' => 'profesor@gmail.com',
            'password' => \Illuminate\Support\Facades\Hash::make('Usuario123'),
        ]);
        $profesor->assignRole('profesor');

        $javi = User::factory()->create([
            'name' => 'Javier Ruiz',
            'email' => 'javier.ruiz@doc.medac.es',
            'password' => \Illuminate\Support\Facades\Hash::make('Usuario123'),
        ]);

        $javi->assignRole('admin');
    }
}
