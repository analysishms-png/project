<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermission extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'userpermission';

    protected $fillable = [
        'propertyid',
        'username',
        'posdiscountallowupto',
        'possettlementyn',
        'editelementinkot',
        'freeitemallow',
        'refundcashcardamt',
        'fomdiscuntallowupto',
        'cancelguestbill',
        'changeroomdetail',
        'deleteguestcharges',
        'changeguestcharges',
        'changeguestprofile',
        'cancelreservation',
        'system_name',
        'allowchkouttimechange',
        'allowadvancechargeedit',
        'posrateedit',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
    ];
}
