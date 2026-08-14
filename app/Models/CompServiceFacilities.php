<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompServiceFacilities extends Model
{
    protected $table = 'compservicefacillities';

    protected $primaryKey = 'sn';

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'displayorder',
        'service',
        'servicehdr',
        'remark',
        'isactive',
        'U_name',
        'U_Entdt',
        'u_ae',
    ];
}