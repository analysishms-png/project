<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaxStructure extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $table = 'taxstru';

    protected $fillable = [
        'sn',
        'propertyid',
        'str_code',
        'name',
        'sno',
        'tax_code',
        'nature',
        'rate',
        'limits',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'sysYN',
        'condapp',
        'comp_operator',
        'limit1'
    ];
}
