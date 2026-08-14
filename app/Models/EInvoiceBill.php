<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EInvoiceBill extends Model
{
    use HasFactory;
    protected $table = 'einvoicebill';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = null;

    protected $fillable = [
        'propertyid',
        'docid',
        'docdtls_no',
        'billno',
        'comp_code',
        'docdtls_dt',
        'buyerdtls_gstin',
        'buyerdtls_lglnm',
        'buyerdtls_trdnm',
        'buyerdtls_pos',
        'valdtls_assval',
        'valdtls_cgstval',
        'valdtls_sgstval',
        'valdtls_igstval',
        'valdtls_totInvval',
        'jsonresponse',
        'ackno',
        'ackdt',
        'irn',
        'signedinvoice',
        'signedqrcode',
        'qrcodeimage',
        'status',
        'u_name',
        'u_entdt'
    ];
}
