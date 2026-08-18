<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Guestwakeup extends Model
{
    protected $table = 'guestwakeup';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'docid',
        'vno',
        'roomno',
        'roomcat',
        'extension',
        'remreqd',
        'foodord',
        'otherreq',
        'wdate',
        'wtime',
        'guestprof',
        'folionodocid',
        'u_name',
        'u_entdt',
        'u_ae',
    ];
}
