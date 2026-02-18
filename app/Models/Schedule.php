<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Schedule — Representa el horario semanal de un profesor para una asignatura.
 *
 * Cada registro indica cuántas horas imparte un profesor una asignatura concreta
 * en un día de la semana específico. Estos datos son la base del cálculo de horas.
 *
 * Ejemplo: Mario imparte Programación los lunes 2 horas y los martes 2 horas.
 */
class Schedule extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables masivamente.
     * - 'dia_semana' : Día en texto ('lunes', 'martes', 'miercoles', 'jueves', 'viernes').
     * - 'horas'      : Número de horas lectivas ese día para esa asignatura.
     * - 'subject_id' : Asignatura a la que pertenece este horario.
     * - 'user_id'    : Profesor que imparte esas horas.
     */
    protected $fillable = [
        'dia_semana',
        'horas',
        'subject_id',
        'user_id',
    ];

    /**
     * Relación: Este horario pertenece a una asignatura concreta.
     */
    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * Relación: Este horario pertenece a un usuario (profesor) concreto.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
