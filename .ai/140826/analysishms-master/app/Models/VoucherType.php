<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VoucherType extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'voucher_type';

    protected $fillable = [
        'propertyid',
        'category',
        'ncat',
        'short_name',
        'v_type',
        'contratype',
        'description',
        'description_help',
        'number_method',
        'start_no',
        'last_ent_date',
        'separate_narr',
        'common_narr',
        'narration',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'chqno',
        'chqdt',
        'clgdt',
        'restcode',
        'defaultcrac',
        'defaultdrac',
        'firstdrcr',
        'sysYN'
    ];
}
