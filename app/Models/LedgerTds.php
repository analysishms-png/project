<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerTds extends Model
{
    use HasFactory;

    protected $fillable = [
        'propertyid',
        'docid',
        'vsno',
        'vprefix',
        'vdate',
        'tdscode',
        'tdsdrcode',
        'onamt',
        'tds',
        'tdsamt',
        'tdsdocid',
        'tdsvsno',
        'u_name',
        'u_ae'
    ];
}
