<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubjectUser extends Model
{
    protected $fillable = [
        'subject_id',
        'user_id'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
