<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Type extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'nombre',
        'color',
    ];

    public function calendars()
    {
        return $this->hasMany(Calendar::class);
    }
}
