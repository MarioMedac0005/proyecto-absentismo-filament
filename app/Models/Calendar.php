<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Calendar — Representa un día especial en el calendario escolar.
 *
 * Almacena fechas que NO son lectivas (festivos, vacaciones, evaluaciones, exámenes).
 * El modelo Subject consulta esta tabla para descontar esos días al calcular las horas.
 */
class Calendar extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables masivamente.
     * - 'fecha'      : La fecha del día especial (ej: '2024-12-25').
     * - 'descripcion': Texto descriptivo (ej: 'Navidad', 'Día de Andalucía').
     * - 'type_id'    : Referencia al tipo de día (festivo, vacaciones, examen...).
     */
    protected $fillable = [
        'fecha',
        'descripcion',
        'type_id'
    ];

    /**
     * Relación: Cada entrada del calendario pertenece a un tipo de día.
     * El tipo define el nombre y el color con el que se muestra en el panel.
     */
    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
