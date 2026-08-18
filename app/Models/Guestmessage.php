<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guestmessage extends Model
{
    protected $table = 'guestmessage';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'roomno',
        'roomcat',
        'caller',
        'telephone',
        'message',
        'recddate',
        'recdtime',
        'guestprof',
        'folionodocid',
        'status',
        'deliveredby',
        'u_name',
        'u_entdt',
        'u_ae',
    ];
}
