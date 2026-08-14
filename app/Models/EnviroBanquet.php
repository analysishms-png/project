<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnviroBanquet extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'enviro_banquet';

    protected $primaryKey = 'propertyid';
    
    protected $fillable = [
        'propertyid',
        'outdoorcatering',
        'cataloglimit',
        'roundoffac',
        'discountac',
        'indoorsaleac',
        'indoorpartyac',
        'resinstructionfp1',
        'resinstructionfp2',
        'resinstructionfp3',
        'resinstructionfp4',
        'resinstructionfp5',
        'resinstructionbillno1',
        'resinstructionbillno2',
        'resinstructionbillno3',
        'u_name',
        'u_ae',
        'banquet_edit_date',
        'booking_edit',
        'adv_tax_on_bill',
        'u_entdt',
        'u_updatedt' ,
        'companyname',
        'companyaddress',
        'gstin',
        'logo',
    ];
}
