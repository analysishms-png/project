<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherPrefix extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $primaryKey = 'sn';
    protected $table = 'voucher_prefix';

    protected $fillable = [
        'propertyid',
        'short_name',
        'v_type',
        'date_from',
        'date_to',
        'prefix',
        'description',
        'start_srl_no',
        'u_name',
        'u_ae'
    ];
}
