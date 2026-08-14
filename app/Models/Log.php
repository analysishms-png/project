<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';

    protected $fillable = [
        'propertyid',
        'username',
        'log_type',
        'message',
        'line',
        'file',
        'ip_address'
    ];

    public $timestamps = false;
}
