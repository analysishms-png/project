<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HkFloor extends Model
{
    protected $table = 'hkfloors';

    protected $fillable = [
        'propertyid',
        'code',
        'name',
        'superviser',
        'isactive',
        'u_name',
    ];
}
