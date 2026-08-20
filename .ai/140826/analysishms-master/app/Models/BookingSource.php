<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSource extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $primaryKey = 'sn';
    protected $table = 'bookingsource';

    protected $fillable = [
        'sn',
        'propertyid',
        'name',
        'bcode',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'activeYN',
        'sysYN',
    ];
}
