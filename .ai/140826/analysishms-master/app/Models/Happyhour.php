<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Happyhour extends Model
{
    use HasFactory;

    protected $table = 'schememast';

    protected $primaryKey = 'sn';
    const CREATED_AT = 'u_entdt';
    const UPDATE_AT = 'updated_at';

    protected $fillable = [
        'sn',
        'propertyid',
        'code',
        'name',
        'startdate',
        'enddate',
        'fromtime',
        'totime',
        'qty',
        'restcode',
        'itemcode',
        'freeitem',
        'freeqty',
        'days',
        'activeyn',
        'u_name',
        'u_entdt',
        'u_ae',
        'updated_at'
    ];
}
