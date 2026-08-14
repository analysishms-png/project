<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HkSupervisor extends Model
{
    protected $table = 'hksupervisor';

    protected $primaryKey = 'sn';

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'code',
        'name',
        'empcode',
        'activeyn',
        'u_name',
        'u_entdt',
        'u_ae',
    ];
}
