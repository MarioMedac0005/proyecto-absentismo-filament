<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Teacher extends Model
{

    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nombre',
        'email',
        'telefono',
    ];

    public function subjectTeachers()
    {
        return $this->hasMany(SubjectTeacher::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'subject_teachers');
    }
}
