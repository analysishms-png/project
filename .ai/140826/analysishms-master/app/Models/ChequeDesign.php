<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChequeDesign extends Model
{

    protected $fillable = [
        'propertyid',
        'design_name',
        'layout_json',
        'u_name'
    ];

    // protected $casts = [
    //     'layout_json' => 'array',
    //     'is_active' => 'boolean',
    // ];
}
