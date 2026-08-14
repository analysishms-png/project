<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnviroFinance extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'enviro_finance';

    protected $primaryKey = 'propertyid';

    protected $fillable = [
        'propertyid',
        'openingstock',
        'closingstock',
        'negtivecashbalance',
        'u_name',
        'u_ae'
    ];
}
