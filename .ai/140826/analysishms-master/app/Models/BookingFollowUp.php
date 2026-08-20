<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingFollowUp extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    protected $table = 'booking_follow_ups';
    protected $fillable = [
        'propertyid',
        'inqno',
        'sno',
        'date',
        'time',
        'nextfollowupdate',
        'remark',
        'status',
        'u_name',
        'u_entdt',
        'u_ae',
    ];
}
