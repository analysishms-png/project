<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnviroEinvoice extends Model
{
    use HasFactory;

    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'enviro_einvoice';

    protected $primaryKey = 'propertyid';

    protected $fillable = [
        'sn',
        'propertyid',
        'apiid',
        'apisecret',
        'einvusername',
        'customerid',
        'einvpwd',
        'activeyn'
    ];
}
