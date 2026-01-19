<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Course extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'nombre',
        'grado',
        'trimestre_1_inicio',
        'trimestre_1_fin',
        'trimestre_2_inicio',
        'trimestre_2_fin',
        'trimestre_3_inicio',
        'trimestre_3_fin',
    ];

    protected $casts = [
        'trimestre_1_inicio' => 'date',
        'trimestre_1_fin' => 'date',
        'trimestre_2_inicio' => 'date',
        'trimestre_2_fin' => 'date',
        'trimestre_3_inicio' => 'date',
        'trimestre_3_fin' => 'date',
    ];

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }
}
