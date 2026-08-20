<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HkChecklistMast extends Model
{
    protected $table = 'hkchecklistmast';

    protected $primaryKey = 'sn';

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'code',
        'sno',
        'name',
        'u_name',
        'u_entdt',
        'u_ae',
    ];
}