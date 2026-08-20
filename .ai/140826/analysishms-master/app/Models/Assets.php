<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assets extends Model
{
    use HasFactory;

    const CREATED_AT = 'create_at';
    const UPDATED_AT = 'update_at';

    protected $table = 'assets';
    protected $primaryKey = 'sn';


    protected $fillable =
    [
        'sn',
        'propertyid',
        'code',
        'name',
        'location',
        'type',
        'company_name',
        'suppler_name',
        'purchase_date',
        'purchase_bill_no',
        'assets_image',
        'bill_image',
        'status',
    ];
}
