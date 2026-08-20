<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderRequest extends Model
{
    use HasFactory;

    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'order_requests';

    protected $fillable = [
        'sn',
        'propertyid',
        'order_id',
        'rest_code',
        'baserestcode',
        'roomno',
        'roomtype',
        'itemcode',
        'item',
        'qty',
        'waiter',
        'status',
        'u_entdt',
        'u_updatedt'
    ];
}
