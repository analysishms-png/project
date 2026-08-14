<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApiClient extends Model
{
    protected $table = 'api_clients';

    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $primaryKey = 'sn';

    protected $fillable = [
        'propertyid',
        'api_key',
        'bearer_token',
        'is_active'
    ];
}
