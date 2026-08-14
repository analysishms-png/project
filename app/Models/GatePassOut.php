<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GatePassOut extends Model
{
    use HasFactory;

    protected $table = 'gate_pass_out';
    protected $primaryKey = 'sn';
    public $timestamps = false;

    protected $fillable = [
        'propertyid',
        'docid',
        'gatepassno',
        'inout',
        'type',
        'vtype',
        'mtype',
        'date',
        'time',
        'visitiorname',
        'partycode',
        'mobileno',
        'vehicleno',
        'materinouyn',
        'item_name',
        'qty',
        'unit',
        'department',
        'remark',
        'inwordduedate',
        'approvedby',
        'securitychkyn',
        'exitstatus',
        'u_name',
        'u_entdt',
        'u_ae',
    ];

    protected $casts = [
        'date' => 'datetime',
        'time' => 'datetime',
        'u_entdt' => 'datetime',
        'inwordduedate' => 'date',
    ];
}
