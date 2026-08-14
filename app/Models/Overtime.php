<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overtime extends Model
{
    use HasFactory;

    protected $table = 'overtime';
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';

    protected $fillable = [
        'propertyid',
        'empcode',
        'otdate',
        'ottime',
        'rate',
        'amount',
        'remark',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae'
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'empcode', 'code');
    }
}
