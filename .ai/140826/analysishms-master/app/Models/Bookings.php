<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    use HasFactory;
    const CREATED_AT = 'u_entdt';
    const UPDATED_AT = 'u_updatedt';
    protected $table = 'booking';

    protected $fillable = [
        'Property_ID',
        'DocId',
        'GuestName',
        'BookNo',
        'Vtype',
        'advdeposit',
        'Vprefix',
        'vdate',
        'GuestProf',
        'vehiclenum',
        'TravelAgency',
        'purpofvisit',
        'BussSource',
        'MarketSeg',
        'RRServiceChrg',
        'BookedBy',
        'ResStatus',
        'ResMode',
        'TravelMode',
        'CancelDate',
        'Cancel',
        'Company',
        'ArrFrom',
        'Destination',
        'U_EntDt',
        'U_Name',
        'U_AE',
        'NoofRooms',
        'Remarks',
        'pickupdrop',
        'Authorization',
        'Verified',
        'CancelUName',
        'MobNo',
        'Email',
        'RRTaxInc',
        'RDisc',
        'RSDisc',
        'AdvDueDate',
        'RefCode',
        'RefBookNo'
    ];

    public function bookingdetails()
    {
        return $this->hasMany(GrpBookinDetail::class, 'BookingDocid', 'DocId');
    }

    public function guestProfile()
    {
        return $this->hasOne(GuestProf::class, 'docid', 'DocId');
    }
}
