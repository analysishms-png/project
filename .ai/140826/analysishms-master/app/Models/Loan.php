<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'loan';

    protected $fillable = [
        'sn',
        'propertyid',
        'vtype',
        'vno',
        'vprefix',
        'vdate',
        'empcode',
        'amount',
        'installment',
        'remark',
        'accode',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'empcode', 'code')->where('propertyid', $this->propertyid);
    }
}

