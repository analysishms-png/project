<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hkroomassign extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'propertyid',
        'vdate',
        'vtime',
        'roomno',
        'status',
        'u_name',
        'assno' 
    ];
}
