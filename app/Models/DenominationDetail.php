<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DenominationDetail extends Model
{
    protected $table = 'denominationdetail';
    protected $primaryKey = 'sn';
    public $timestamps = false;
    protected $guarded = [];
}
