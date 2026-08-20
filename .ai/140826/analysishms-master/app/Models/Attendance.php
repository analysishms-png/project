<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;
    protected $primaryKey = 'sn';
    protected $table = 'attendance';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'sn',
        'propertyid',
        'vdate',
        'date_from',
        'date_to',
        'vprefix',
        'empcode',
        'firstshift',
        'secondshift',
        'u_entdt',
        'u_updatedt',
        'u_name',
        'u_ae'
    ];

}
