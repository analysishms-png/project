<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HkAmentiesMaster extends Model
{
    protected $table = 'hkamentiesmaster';

    protected $primaryKey = 'sn';

    protected $keyType = 'int';

    public $incrementing = true;

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'item',
        'type',
        'u_name',
        'u_entdt',
        'u_ae',
        'srno',
    ];
}
