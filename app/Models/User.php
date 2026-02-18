<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * Modelo User — Representa a los usuarios del sistema (profesores y administradores).
 *
 * Implementa FilamentUser para controlar quién puede acceder al panel de administración.
 */
class User extends Authenticatable implements FilamentUser
{
    /**
     * HasFactory    → Permite generar usuarios de prueba con factories.
     * Notifiable    → Permite enviar notificaciones al usuario (email, etc.).
     * HasRoles      → Añade el sistema de roles de Spatie (admin, profesor).
     * SoftDeletes   → El borrado no elimina el registro de la BD, solo lo marca
     *                 con una fecha en 'deleted_at'. Se puede restaurar.
     */
    use HasFactory, Notifiable, HasRoles, SoftDeletes;

    /**
     * Campos que se pueden asignar masivamente (mass assignment).
     * Solo estos campos se aceptan al hacer User::create([...]).
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone'
    ];

    /**
     * Campos que se ocultan cuando el modelo se convierte a JSON o array.
     * Así la contraseña nunca se expone en respuestas de la API.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Conversiones automáticas de tipos al leer/escribir estos campos.
     * - 'email_verified_at' se convierte a objeto Carbon (fecha).
     * - 'password' se hashea automáticamente al asignarlo.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relación: Un usuario tiene muchos registros en la tabla pivote subject_users.
     * Útil para acceder a los datos extra de la relación (ej: fechas de asignación).
     */
    public function subjectUsers()
    {
        return $this->hasMany(SubjectUser::class);
    }

    /**
     * Relación muchos a muchos: Un usuario (profesor) puede impartir varias asignaturas
     * y una asignatura puede ser impartida por varios profesores.
     * La tabla intermedia es 'subject_users'.
     */
    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_users');
    }

    /**
     * Controla el acceso al panel de administración de Filament.
     * Solo los usuarios con rol 'admin' o 'profesor' pueden entrar.
     *
     * @param Panel $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole('admin') || $this->hasRole('profesor');
    }
}
