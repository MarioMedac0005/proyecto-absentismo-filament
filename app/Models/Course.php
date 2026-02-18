<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Modelo Course — Representa un curso académico (ej: "1º DAW", "2º DAM").
 *
 * Almacena el año escolar y las fechas exactas de inicio y fin de cada trimestre,
 * que son usadas por Subject::calcularHorasPorTrimestre() para acotar el cálculo.
 */
class Course extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Campos asignables masivamente.
     * - 'inicio_curso' / 'fin_curso': año de inicio y fin (enteros, ej: 2024 / 2025).
     * - 'trimestre_X_inicio' / 'trimestre_X_fin': fechas de cada trimestre.
     */
    protected $fillable = [
        'nombre',
        'inicio_curso',
        'fin_curso',
        'grado',
        'trimestre_1_inicio',
        'trimestre_1_fin',
        'trimestre_2_inicio',
        'trimestre_2_fin',
        'trimestre_3_inicio',
        'trimestre_3_fin',
    ];

    /**
     * Conversiones automáticas de tipo al leer estos campos de la BD.
     * - Los años se tratan como enteros.
     * - Las fechas de trimestre se convierten a objetos Carbon automáticamente.
     */
    protected $casts = [
        'inicio_curso' => 'integer',
        'fin_curso' => 'integer',
        'trimestre_1_inicio' => 'date',
        'trimestre_1_fin' => 'date',
        'trimestre_2_inicio' => 'date',
        'trimestre_2_fin' => 'date',
        'trimestre_3_inicio' => 'date',
        'trimestre_3_fin' => 'date',
    ];

    /**
     * Relación: Un curso tiene muchas asignaturas.
     * Ejemplo: "1º DAW" tiene Programación, Bases de Datos, etc.
     */
    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
