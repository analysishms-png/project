<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hrpayrolls extends Model
{
    use HasFactory;
    protected $primaryKey = 'id';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'desig';

    protected $fillable = [
        'propertyid',
        'code',
        'name',
        'Activ',
        'u_name',
        'u_entdt',
        'u_ae',
        'u_updatedt'
    ];
}
