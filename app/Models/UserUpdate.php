<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserUpdate extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'userupdate';

    protected $fillable = [
        'user',
        'propertyid',
        'oldvalue',
        'newvalue',
        'form_type',
        'u_entdt',
        'u_updatedt',
    ];
}
