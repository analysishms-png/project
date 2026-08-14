<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ledger extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'ledger';
    protected $primaryKey = 'docid';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'propertyid',
        'docid',
        'vsno',
        'vtype',
        'vno',
        'vprefix',
        'vdate',
        'subcode',
        'amtcr',
        'amtdr',
        'contrasub',
        'chqno',
        'chqdate',
        'delflag',
        'clgdate',
        'narration',
        'groupcode',
        'groupnature',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae'
    ];
}
