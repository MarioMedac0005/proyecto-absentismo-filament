<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use function Laravel\Prompts\text;
use function Laravel\Prompts\password;

class MakeAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:user {--role=admin : Rol de usuario (admin|profesor)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comando para poder crear un usuario administrador en el sistema';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $role = $this->option('role');

        if (!in_array($role, ['admin', 'profesor'])) {
            $this->error('Rol no válido. Usa admin o profesor');
            return Command::FAILURE;
        }

        $name = text(
            label: "Nombre del {$role}",
            placeholder: "{$role}",
            required: true
        );

        $email = text(
            label: "Correo del {$role}",
            placeholder: "{$role}@gmail.com",
            required: true,
        );

        $password = password(
            label: "Contraseña del {$role}",
            required: true,  
        );

        if (User::where('email', $email)->exists()) {
            $this->error('El correo ya existe en la base de datos');
            return Command::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => $password,
        ]);

        $user->assignRole($role);

        $this->info("✅ Usuario con rol {$role} creado correctamente");
        return Command::SUCCESS;
    }
}
