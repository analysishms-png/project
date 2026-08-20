<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanDetail extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'plandetails';

    protected $fillable = [
        'propertyid',
        'foliono',
        'docid',
        'sno',
        'sno1',
        'roomno',
        'room_rate_before_tax',
        'total_rate',
        'pcode',
        'noofdays',
        'rev_code',
        'fixrate',
        'planper',
        'amount',
        'netplanamt',
        'taxinc',
        'taxstru',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae'
    ];
}
