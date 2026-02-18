<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // Admin del sistema
        $admin = User::factory()->create([
            'name'     => 'admin',
            'email'    => 'admin@gmail.com',
            'password' => Hash::make('Usuario123'),
        ]);
        $admin->assignRole('admin');
    }
}
