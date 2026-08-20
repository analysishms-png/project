<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestReward extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'guestreward';
    protected $primaryKey = 'sn';
    protected $fillable = [
        'propertyid',
        'docid',
        'custcode',
        'vdate',
        'vtime',
        'restcode',
        'departname',
        'billno',
        'total',
        'billamt',
        'rewardpoint',
        'redeempoint',
        'mobileno',
        'discamt',
        'schemecode',
        'saleupto',
        'rppointonamt',
        'rewardvalue',
        'reedemvalue',
        'regid',
        'discper',
        'u_entdt',
        'u_name',
        'u_ae',
        'u_updatedt',
    ];
}
