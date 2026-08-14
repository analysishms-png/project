<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guestfolio extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'guestfolio';

    protected $fillable = [
        'sn',
        'propertyid',
        'docid',
        'folio_no',
        'sno1',
        'vtype',
        'vdate',
        'vprefix',
        'guestprof',
        'name',
        'add1',
        'add2',
        'city',
        'nodays',
        'remarks',
        'pickupdrop',
        'company',
        'purvisit',
        'arrfrom',
        'destination',
        'travelmode',
        'vehiclenum',
        'remark',
        'rodisc',
        'rsdisc',
        'bookingdocid',
        'u_name',
        'u_ae',
        'busssource',
        'booking_source',
        'depdate',
        'travelagent',
        'roomcount',
        'mfoliono',
        'comp',
        'mfolionodocid',
        'refno',
        'bookingsno',
        'refbookno',
        'whatsappcheckout',
        'suppressrate',
    ];
}
