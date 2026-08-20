<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerLog extends Model
{
    use HasFactory;

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
        'u_ae',
        'deleted_by',
        'deleted_at',
        'verifyuser',
        'verifyremark',
        'verifydate',
        'rejectremark',
        'rejectuser',
        'rejectdate'
    ];
}
