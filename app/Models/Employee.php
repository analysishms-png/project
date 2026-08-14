<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;
    protected $table = 'employee';

    protected $primaryKey = 'sn';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'propertyid',
        'code',
        'name',
        'sex',
        'designation',
        'category',
        'f_name',
        'birth_date',
        'add1',
        'add2',
        'marital',
        'spouse',
        'qualification',
        'joining_date',
        'resign_date',
        'basic',
        'da',
        'hra',
        'tds',
        'other_allow',
        'other_deduc',
        'conveyance',
        'medical',
        'lta',
        'pf_code',
        'esi_code',
        'pf_yn',
        'op_pf_balance',
        'op_loan',
        'op_inst',
        'op_advance',
        'tot_cl_allow',
        'tot_el_allow',
        'op_el',
        'op_cl',
        'curr_el',
        'curr_cl',
        'ac_code',
        'phone',
        'pan',
        'increment',
        'off_day',
        'off_day_allow',
        'incrmth',
        'otrate',
        'loanac',
        'u_updatedt',
        'u_ae',
        'activeyn',
        'esi_yn',
        'u_name',
        'pic_path',
        'idpicpath',
        'department',
        'idproof',
        'idproofno',
        'type',
        'u_entdt',
        'bio_metric_id',
        'bank_account',
        'ac_holder_name',
        'ifsc_code'
    ];

    // 👇 Magic: toArray me key lowercase kar denge
    public function toArray()
    {
        $original = parent::toArray();
        return array_change_key_case($original, CASE_LOWER);
    }
}
