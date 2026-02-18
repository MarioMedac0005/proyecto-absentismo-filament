<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Modelo SubjectUser — Tabla pivote que relaciona profesores con asignaturas.
 *
 * Gestiona la relación muchos a muchos entre User y Subject:
 * - Un profesor puede impartir varias asignaturas.
 * - Una asignatura puede ser impartida por varios profesores.
 *
 * Se usa como modelo explícito (en lugar de solo la tabla pivote) para poder
 * añadir lógica o campos extra a la relación si fuera necesario.
 */
class SubjectUser extends Model
{
    /**
     * Campos asignables masivamente.
     * - 'subject_id': ID de la asignatura asignada.
     * - 'user_id'   : ID del profesor al que se asigna.
     */
    protected $fillable = [
        'subject_id',
        'user_id'
    ];

    /**
     * Relación: Esta asignación pertenece a una asignatura concreta.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relación: Esta asignación pertenece a un usuario (profesor) concreto.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
