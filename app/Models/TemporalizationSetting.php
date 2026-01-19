<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TemporalizationSetting extends Model
{

    protected $table = 'temporalization_settings';

    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'key',
        'value',
    ];
}
