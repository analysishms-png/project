<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuestProf extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'guestprof';

    protected $fillable = [
        'propertyid',
        'docid',
        'folio_no',
        'u_entdt',
        'u_name',
        'u_ae',
        'complimentry',
        'guestcode',
        'name',
        'state_code',
        'country_code',
        'add1',
        'add2',
        'city',
        'type',
        'mobile_no',
        'email_id',
        'nationality',
        'anniversary',
        'guest_status',
        'comments1',
        'comments2',
        'comments3',
        'city_name',
        'state_name',
        'country_name',
        'gender',
        'marital_status',
        'zip_code',
        'con_prefix',
        'dob',
        'age',
        'pic_path',
        'id_proof',
        'idproof_no',
        'issuingcitycode',
        'issuingcityname',
        'issuingcountrycode',
        'issuingcountryname',
        'expiryDate',
        'paymentMethod',
        'idpic_path',
        'm_prof',
        'father_name',
        'fom',
        'pos'
    ];
}
