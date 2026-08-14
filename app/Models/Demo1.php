<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


    class Demo1 extends Model
{
    protected $table = 'demo1';

    protected $primaryKey = 'orderno';

    protected $fillable = [
        'remark',
        'nextfollowdate'
    ];
}
