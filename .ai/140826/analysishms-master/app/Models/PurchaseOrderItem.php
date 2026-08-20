<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;


    protected $table = 'porder1';

    protected $primaryKey = 'sn';
    const CREATED_AT = 'u_entdt';

    protected $fillable = [
        'propertyid',
        'docid',
        'sno',
        'vno',
        'vdate',
        'vtype',
        'vprefix',
        'partycode', // 'partyname',
        'itemcode',
        'qty',
        'instock',
        'unit',
        'rate',
        'amount',
        'indentdocid',
        'indentsno',
        'specification',
        'taxstru',
        'taxamt',
        'total',
        'u_name',
        'u_entdt',
        'u_updatedt',
    ];
}
