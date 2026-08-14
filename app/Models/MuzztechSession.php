<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MuzztechSession extends Model
{
    use HasFactory;
    protected $table = 'muzztech_session';

    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'propertyid',
        'docid',
        'header_handle',
        'media_id',
        'expire_at'
    ];

}
