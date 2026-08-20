<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salary extends Model
{
    use HasFactory;
    protected $table = 'salary';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'sn',
        'propertyid',
        'mth_year',
        'emp_code',
        'work_day',
        'cl',
        'leave',
        'sunday',
        'holiday',
        'absent',
        'Basic',
        'da',
        'hra',
        'other_allow',
        'other_deduc',
        'conveyance',
        'medical',
        'lta',
        'pf',
        'epf',
        'esi',
        'loan',
        'advance',
        'net_salary',
        'loan_bal',
        'overtime',
        'overtimeamt',
        'adv_bal',
        'emp_basic',
        'department',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae'
    ];
}
