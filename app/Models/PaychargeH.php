<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaychargeH extends Model
{
    protected $guarded = [];

    use HasFactory;

    protected $table = 'paychargeh';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
}
