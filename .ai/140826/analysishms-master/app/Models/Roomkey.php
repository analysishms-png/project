<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roomkey extends Model
{
    use HasFactory;

    protected $fillable = [
        'propertyid',
        'docid',
        'sno1',
        'data',
        'u_name',
        'status',
        'reason'
    ];

    protected $casts = [
        'data' => 'array'
    ];
}
