<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnviroFom extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'enviro_form';

    protected $fillable = [
        'propertyid',
        'arrdatetimeedit',
        'cancellationac',
        'pageopenwalkin',
        'advanceroomrentac',
        'addroomreservation',
        'grcmandatory',
        'printfoodsaccode',
        'roomrateeditable',
        'roominctaxeditable',
        'roominclusive',
        'rrinctaxdefault',
        'blockinvalidtarrifinctaxyn',
        'fombillcopies',
        'checkout',
        'allowcheckinsubmit',
        'checkintime',
        'plancalc',
        'autofillroomres',
        'emptyroomyn',
        'noshowatnightaudit',
        'billprintingsummerised',
        'taxsummary',
        'variationbefore',
        'variationafter',
        'roomrentcharge',
        'roomrentbefchkout',
        'roomrentchkoutpost',
        'autosplityn',
        'roomcheckoutclearanceyn',
        'roomchrgdueac',
        'postroomdiscseparately',
        'plantariffnarration',
        'guestchargesdeletelog',
        'rate1',
        'rate2',
        'rate3',
        'rate4',
        'rate5',
        'resinstruction1',
        'resinstruction2',
        'resinstruction3',
        'resinstruction4',
        'resinstruction5',
        'resinstruction6',
        'resinstruction7',
        'resinstruction8',
        'resinstruction9',
        'resinstruction10',
        'resinstruction11',
        'resinstruction12',
        'roomcheckinclearanceyn',
        'logoyn',
        'emailyn',
        'websiteyn',
        'fssaicode',
        'seperatereservationletterasperstatusyn',
        'newtarrifforoldguest',
        'reservationexpondonsaveyn',
        'increservationinblankgrc',
        'tentativedays',
        'displayrackrow',
        'displayrackcol',
        'displayrackfontsize',
        'grcdatetime',
        'u_name',
        'u_entdt',
        'u_updatedt',
        'u_ae',
        'sysYN',
        'roundofftype',
        'allowbelow18'
    ];
}
