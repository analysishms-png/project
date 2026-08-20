<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Paycharge extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'paycharge';

    protected $fillable = [
        'propertyid',
        'docid',
        'sno',
        'sno1',
        'vtype',
        'vno',
        'vprefix',
        'vdate',
        'vtime',
        'guestprof',
        'comp_code',
        'travel_agent',
        'comments',
        'paycode',
        'paytype',
        'amtcr',
        'amtdr',
        'tipamt',
        'roomcat',
        'roomtype',
        'roomno',
        'foliono',
        'msno1',
        'cardno',
        'cardholder',
        'chqno',
        'chqdate',
        'expdate',
        'bookno',
        'booktype',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'restcode',
        'billamount',
        'contraid',
        'dbtchkin',
        'taxper',
        'onamt',
        'split',
        'billno',
        'modeset',
        'settledate',
        'batchno',
        'plancharge',
        'fixedchargecode',
        'relatdfoliono',
        'folionodocid',
        'refno',
        'plancode',
        'seqno',
        'relatedfolionodocid',
        'refdocid',
        'remarks',
        'au_name',
        'au_entdt',
        'au_updatedt',
        'taxcondamt',
        'taxstru',
        'agac',
        'txnno',
        'posted'
    ];
}
