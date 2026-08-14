<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ACGroup extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'acgroup';

    protected $fillable = [
        'group_code',
        'group_name',
        'maingroupcode',
        'maingroupname',
        'nature',
        'propertyid',
        'u_entdt',
        'u_updatedt',
        'u_name'
    ];
}
