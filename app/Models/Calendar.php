<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'fecha',
        'descripcion',
        'type_id'
    ];

    public function type()
    {
        return $this->belongsTo(Type::class);
    }
}
