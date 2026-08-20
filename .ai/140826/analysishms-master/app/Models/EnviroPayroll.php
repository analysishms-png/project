<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnviroPayroll extends Model
{
    use HasFactory;

    protected $table = 'enviro_payroll';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $primaryKey = 'propertyid';

    protected $fillable = [
        'sn',
        'propertyid',
        'pflimit',
        'pfemployee',
        'pfemployer',
        'esilimit',
        'esiemployee',
        'salaryac',
        'loanac',
        'advanceac',
        'esebasic',
        'esida',
        'esihra',
        'esiconvey',
        'esiother',
        'esilta',
        'gappcompyear',
        'gmonthconsidered',
        'gworkingdaysInamonth',
        'gdayssalary',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae'
    ];
}
