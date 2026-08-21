<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuntranEst extends Model
{
    protected $guarded = [];

     const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $table = 'suntranest';
}
