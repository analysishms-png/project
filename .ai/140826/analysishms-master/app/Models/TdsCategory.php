<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TdsCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'propertyid',
        'code',
        'name',
        'tdspercentage',
        'account',
        'u_name',
    ];
}
