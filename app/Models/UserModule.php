<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserModule extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $table = 'usermodule';

    protected $fillable = [
        'propertyid',
        'opt1',
        'opt2',
        'opt3',
        'route',
        'code',
        'module',
        'module_name',
        'flag',
        'outletcode',
        'u_entdt',
        'u_updatedt',
    ];
}
