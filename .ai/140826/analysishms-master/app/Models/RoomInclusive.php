<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomInclusive extends Model
{
    protected $table = 'room_inclusive';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'propertyid',
        'docid',
        'bookno',
        'vtype',
        'vprefix',
        'vdate',
        'sno',
        'rev_code',
        'amount',
        'chargepost',
        'contradocid',
        'contrasno',
        'u_entdt',
        'u_updatedt',
        'u_name',
        'u_ae'
    ];

    public function grpbookingdetail()
    {
        return $this->belongsTo(GrpBookinDetail::class, 'docid', 'BookingDocid');
    }
}
