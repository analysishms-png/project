<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purch2 extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'purch2';
    protected $primaryKey = 'docid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'propertyid',
        'docid',
        'sno',
        'vtype',
        'vno',
        'vprefix',
        'vdate',
        'partycode',
        'restcode',
        'mrno',
        'contradocid',
        'contrasno',
        'item',
        'qtyiss',
        'qtyrec',
        'unit',
        'rate',
        'amount',
        'taxper',
        'taxamt',
        'discper',
        'discamt',
        'remarks',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'total',
        'discapp',
        'roundoff',
        'departcode',
        'godcode',
        'chalqty',
        'recdqty',
        'accqty',
        'rejqty',
        'recdunit',
        'specification',
        'itemrate',
        'delflag',
        'convratio',
        'postval',
        'landval',
        'issqty',
        'issuunit',
        'taxstru',
        'accode'
    ];
}
