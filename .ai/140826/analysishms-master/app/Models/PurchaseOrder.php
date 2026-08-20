<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'porder';

    protected $primaryKey = 'sn';
    const CREATED_AT = 'u_entdt';

    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'propertyid',
        'docid',
        'vno',
        'vdate',
        'vtype',
        'vprefix',
        'quotno',
        'quotdate',
        'partycode',
        'remark',
        'exp_delivery',
        'dispatchmode',
        'despatchthru',
        'paymentterms',
        'packcharge',
        'forwardcharges',
        'discper',
        'taxper',
        'u_name',
        'u_ae',
        'u_entdt',
    ];
}
