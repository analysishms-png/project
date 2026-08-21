<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DenominationFormat extends Model
{
    protected $table = 'denominationformat';
    protected $primaryKey = 'sn';
    public $timestamps = false;
    protected $guarded = [];
}
