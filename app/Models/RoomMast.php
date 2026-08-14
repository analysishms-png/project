<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomMast extends Model
{
    use HasFactory;

    protected $table = 'room_mast';

    protected $primaryKey = 'sno';

    public $incrementing = false;

    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'sno',
        'cs',
        'propertyid',
        'rcode',
        'name',
        'type',
        'room_cat',
        'multiper',
        'maid_station',
        'inclcount',
        'rest_code',
        'room_stat',
        'pic_path',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'sysYN',
    ];
}
