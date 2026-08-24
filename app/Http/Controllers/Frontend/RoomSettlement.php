<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\DateHelper;
use App\Helpers\ResHelper;
use App\Helpers\UpdateRepeat;
use App\Helpers\WhatsappSend;
use App\Models\ACGroup;
use App\Models\Bookings;
use App\Services\RoomInclusivePosting;
use App\Models\BookinPlanDetail;
use App\Models\ChannelEnviro;
use App\Models\ChannelPushes;
use App\Models\Cities;
use App\Models\CompanyDiscount;
use App\Models\FomBillDetail;
use App\Models\Happyhour;
use App\Models\PlanMast;
use App\Models\RoomInclusive;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\CompanyLog;
use App\Models\Companyreg;
use App\Models\Countries;
use App\Models\UserModule;
use App\Models\MenuHelp;
use App\Models\Paycharge;
use App\Models\UserPermission;
use App\Models\Items;
use App\Models\ItemMast;
use App\Models\ItemRate;
use App\Models\ItemCatMast;
use App\Models\ItemGrp;
use App\Models\Guestfolio;
use App\Models\Kot;
use App\Models\Revmast;
use App\Models\RoomMast;
use App\Models\GuestProf;
use App\Models\Sale1;
use App\Models\SubGroup;
use App\Models\Depart;
use App\Models\Depart1;
use App\Models\EInvoiceBill;
use App\Models\EnviroEinvoice;
use App\Models\EnviroFom;
use App\Models\EnviroGeneral;
use App\Models\EnviroPos;
use App\Models\EnviroWhatsapp;
use App\Models\GrpBookinDetail;
use App\Models\GuestFolioProfDetail;
use App\Models\Ledger;
use App\Models\NightAuditLog;
use App\Models\PlanDetail;
use App\Models\PrintingSetup;
use App\Models\RoomBlockout;
use App\Models\RoomCat;
use App\Models\Sagar;
use App\Models\Stock;
use App\Models\RoomOcc;
use App\Models\States;
use App\Models\SundryMast;
use App\Models\SundryTypeFix;
use App\Models\Suntran;
use App\Models\TaxStructure;
use App\Models\User;
use App\Models\VoucherPrefix;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Pipeline\Pipeline;
use Illuminate\Support\Facades\Hash;
use Psr\Http\Client\NetworkExceptionInterface;
use Symfony\Component\Routing\Matcher\Dumper\MatcherDumper;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\Kot as KotModal;
use App\Models\RoomInclusiveLog;
use App\Models\Sundrytype;
use App\Services\AccountPosting;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

use function App\Helpers\endsWith;

class RoomSettlement extends Controller
{
    protected $username;
    protected $email;
    protected $propertyid;
    protected $currenttime;
    protected $ptlngth;
    protected $prpid;
    protected $compcode;
    protected $ncurdate;
    protected $datemanage;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->email = Auth::user()->email;
            $this->prpid = $this->propertyid;
            $propertydata = DB::table('users')->where('propertyid', $this->prpid)->first();
            $this->compcode = Companyreg::where('propertyid', $this->propertyid)->value('comp_code');
            $this->ncurdate = DB::table('enviro_general')->where('propertyid', $this->propertyid)->value('ncur');
            $this->propertyid = $propertydata->propertyid;
            $this->ptlngth = strlen($this->propertyid);
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');
            $this->datemanage = DateHelper::calculateDateRanges($this->ncurdate);
            return $next($request);
        });
    }

    public function submitRoomSettle(Request $request)
    {
        // Validate incoming request
        $request->validate([
            'charge' => 'required',
            'amount' => 'required',
        ]);

        // Constants and frequently used values
        $propertyId = $this->propertyid;
        $docId = $request->input('docid');
        $sno = $request->input('sno');
        $sno1Main = $request->input('sno1main');
        $amount = $request->input('amount');
        $voucherType = 'REC';
        $currentDate = $this->ncurdate;
        $currentTime = $this->currenttime;
        $userName = Auth::user()->u_name;
        $currentHour = $request->input('curtime');

        // return 'jj';

        // Begin transaction to ensure data integrity
        DB::beginTransaction();

        try {
            // Get voucher prefix information
            $voucherPrefix = VoucherPrefix::where('propertyid', $propertyId)
                ->where('v_type', $voucherType)
                ->whereDate('date_from', '<=', $currentDate)
                ->whereDate('date_to', '>=', $currentDate)
                ->first();

            if (!$voucherPrefix) {
                throw new \Exception('Voucher prefix not found');
            }

            $voucherNumber = $voucherPrefix->start_srl_no + 1;
            $prefix = $voucherPrefix->prefix;
            $generatedDocId = $propertyId . $voucherType . ' ‎ ‎' . $prefix . ' ‎  ‎ ' . $voucherNumber;

            // Get room occupancy information
            $roomOccupancy = DB::table('roomocc')
                ->where('propertyid', $propertyId)
                ->where('docid', $docId)
                ->where('sno', $sno)
                ->where('sno1', $sno1Main)
                ->first();

            if (!$roomOccupancy) {
                throw new Exception('Room occupancy record not found');
            }

            // Common update arrays
            $payChargeUpdate = [
                'settledate' => $currentDate,
                'u_updatedt' => $currentTime,
            ];

            $roomOccUpdate = [
                'userchkoutdate' => $currentDate,
                'chkoutuser' => $userName,
                'type' => 'O',
                'chkoutdate' => $currentDate,
                'u_ae' => 'e',
                'chkouttime' => $currentHour,
                'u_updatedt' => $currentTime,
            ];

            $grpBookingUpdate = [
                'chkoutyn' => 'Y',
                'U_AE' => 'e',
                'u_updatedt' => $currentTime,
            ];

            // Process leader room or individual room
            $leaderId = null;
            $billNumber = null;

            $leaderRoomOcc = RoomOcc::where('propertyid', $propertyId)
                ->where('docid', $docId)
                ->where('leaderyn', 'Y')
                ->first();

            // return $leaderRoomOcc;

            if ($leaderRoomOcc) {
                $leaderId = $leaderRoomOcc->sno1;
                // echo 'leader';
                // echo $propertyId . ' - ' . $leaderRoomOcc->docid . ' - ' . $leaderId;
                // exit;
                $chkrelatedgroup1 = Paycharge::where('propertyid', $this->propertyid)
                    ->where('folionodocid', $leaderRoomOcc->docid)
                    ->where('msno1', $leaderId)
                    ->groupBy('relatedfolionodocid')
                    ->get();

                $chkrelatedgroup = Paycharge::where('propertyid', $this->propertyid)
                    ->where('folionodocid', $leaderRoomOcc->docid)
                    ->where('msno1', $leaderId)
                    ->whereNotNull('relatedfolionodocid')
                    ->where('relatedfolionodocid', '!=', '')
                    ->groupBy('relatedfolionodocid')
                    ->first();
                $tbl = DB::table('paycharge')
                    ->select(DB::raw('SUM(amtdr) as amtdr'), DB::raw('SUM(amtcr) as amtcr'), DB::raw('(SUM(amtdr) - SUM(amtcr)) as balance'))
                    ->where('folionodocid', $request->input('docid'))
                    ->where('msno1', $leaderRoomOcc->sno1)
                    ->first();
                // exit;

                $checkedrooms = RoomOcc::where('propertyid', $this->propertyid)
                    ->where('docid', $leaderRoomOcc->docid)
                    ->get();

                if ($checkedrooms) {
                    foreach ($checkedrooms as $row) {
                        RoomMast::where('propertyid', $this->propertyid)
                            ->where('rcode', $row->roomno)->where('type', 'RO')
                            ->where('inclcount', 'Y')
                            ->update(['room_stat' => 'D']);
                    }
                }

                if (is_null($chkrelatedgroup)) {
                    // echo 'leaderempty';
                    // exit;
                    RoomOcc::where('propertyid', $propertyId)
                        ->where('docid', $leaderRoomOcc->docid)
                        ->where(function ($q) {
                            $q->whereNull('type')
                                ->orWhere('type', 'O');
                        })
                        ->update($roomOccUpdate);

                    GrpBookinDetail::where('Property_ID', $propertyId)
                        ->where('ContraDocId', $leaderRoomOcc->docid)
                        ->update($grpBookingUpdate);
                } else {
                    // echo 'leadernotempty';
                    // exit;
                    $relatedDocIds = $chkrelatedgroup1->pluck('relatedfolionodocid');

                    RoomOcc::where('propertyid', $propertyId)
                        ->whereIn('docid', $relatedDocIds)
                        ->where(function ($q) {
                            $q->whereNull('type')
                                ->orWhere('type', 'O');
                        })
                        ->update($roomOccUpdate);

                    GrpBookinDetail::where('Property_ID', $propertyId)
                        ->whereIn('ContraDocId', $relatedDocIds)
                        ->update($grpBookingUpdate);
                }

                // return 'sagar';

                $billNumber = Paycharge::where('folionodocid', $leaderRoomOcc->docid)
                    ->where('msno1', $leaderId)
                    ->value('billno');

                Paycharge::where('propertyid', $propertyId)
                    ->where('folionodocid', $leaderRoomOcc->docid)
                    ->where('msno1', $leaderId)
                    ->update($payChargeUpdate);

                $rooms = DB::table('roomocc')
                    ->where('propertyid', $propertyId)
                    ->where('docid', $leaderRoomOcc->docid)
                    ->get();

                foreach ($rooms as $row) {
                    RoomMast::where('propertyid', $this->propertyid)->where('rcode', $row->roomno)->where('type', 'RO')->where('inclcount', 'Y')
                        ->update(['room_stat' => 'D']);
                }
            } else {
                // echo 'nonleader';
                // exit;
                $tbl = DB::table('paycharge')
                    ->select(DB::raw('SUM(amtdr) as amtdr'), DB::raw('SUM(amtcr) as amtcr'), DB::raw('(SUM(amtdr) - SUM(amtcr)) as balance'))
                    ->where('folionodocid', $request->input('docid'))
                    ->where('sno1', $request->input('sno1main'))
                    ->first();
                $billNumber = DB::table('paycharge')
                    ->where('folionodocid', $docId)
                    ->where('sno1', $sno1Main)
                    ->value('billno');

                DB::table('paycharge')
                    ->where('propertyid', $propertyId)
                    ->where('folionodocid', $docId)
                    ->where('sno1', $sno1Main)
                    ->update($payChargeUpdate);

                DB::table('roomocc')
                    ->where('propertyid', $propertyId)
                    ->where('docid', $docId)
                    ->where('sno1', $sno1Main)
                    ->where('sno', $sno)
                    ->where(function ($q) {
                        $q->whereNull('type')
                            ->orWhere('type', 'O');
                    })
                    ->update($roomOccUpdate);

                $checkedrooms = RoomOcc::where('propertyid', $this->propertyid)
                    ->where('docid', $docId)
                    ->where('sno1', $sno1Main)
                    ->where('sno', $sno)
                    ->get();

                if ($checkedrooms) {
                    foreach ($checkedrooms as $row) {
                        RoomMast::where('propertyid', $this->propertyid)
                            ->where('rcode', $row->roomno)->where('type', 'RO')
                            ->where('inclcount', 'Y')
                            ->update(['room_stat' => 'D']);
                    }
                }

                GrpBookinDetail::where('Property_ID', $propertyId)
                    ->where('ContraDocId', $docId)
                    ->where('ContraSno', $sno1Main)
                    ->update($grpBookingUpdate);

                $rooms = DB::table('roomocc')
                    ->where('propertyid', $propertyId)
                    ->where('docid', $docId)
                    ->where('sno1', $sno1Main)
                    ->where('sno', $sno)
                    ->get();

                foreach ($rooms as $row) {
                    RoomMast::where('propertyid', $this->propertyid)->where('rcode', $row->roomno)->where('type', 'RO')->where('inclcount', 'Y')
                        ->update(['room_stat' => 'D']);
                }
            }

            // return 'sagar';

            // Update bill details
            DB::table('fombilldetails')
                ->where('folionodocid', $docId)
                ->where('billno', $billNumber)
                ->update(['settamt' => $amount]);

            // Process payment charges
            $chargeCount = 0;
            foreach ($request->input() as $key => $value) {
                if (strpos($key, 'chargecode') === 0) {
                    $chargeCount++;
                }
            }

            $serialNumber = 1;
            $chargeEntries = [];

            for ($i = 1; $i <= $chargeCount; $i++) {
                $chargeCode = $request->input('chargecode' . $i);
                $chargeAmount = $request->input('amtrow' . $i);

                // Skip empty rows
                if (empty($chargeCode) || empty($chargeAmount) || $chargeAmount == 0) {
                    continue;
                }

                $payCodeInfo = Revmast::where('propertyid', $propertyId)
                    ->where('rev_code', $chargeCode)
                    ->first();

                if (!$payCodeInfo) {
                    continue;
                }

                if ($chargeCode == "ROOM{$this->propertyid}") {
                    $roomnoval = $request->input('roomnoval' . $i);

                    if (empty($roomnoval)) {
                        return back()->with('error', 'Room charge cannot be added as room number is provided in the charge details. Please remove room number from charge details to proceed.');
                    }

                    $roomoccrval = Roomocc::where('propertyid', $propertyId)
                        ->where('roomno', $roomnoval)
                        ->whereNull('chkoutdate')
                        ->whereNull('type')
                        ->first();

                    $chargeEntries[] = [
                        'propertyid' => $propertyId,
                        'docid' => $generatedDocId,
                        'vno' => $voucherNumber,
                        'vtype' => $voucherType,
                        'sno' => $serialNumber,
                        'sno1' => $sno1Main,
                        'msno1' => $leaderId ?? 0,
                        'chqno' => '',
                        'cardno' => $request->input('crnumber'),
                        'cardholder' => '',
                        'expdate' => $request->input('expdatecr'),
                        'bookno' => '',
                        'vdate' => $currentDate,
                        'vtime' => date('H:i:s'),
                        'vprefix' => $prefix,
                        'comp_code' => '',
                        'paycode' => $chargeCode,
                        'paytype' => $payCodeInfo->pay_type ?? '',
                        'comments' => "Room Adjustment for Room No: {$roomoccrval->roomno}",
                        'guestprof' => $roomoccrval->guestprof,
                        'roomno' => $roomOccupancy->roomno,
                        'amtcr' => 0.00,
                        'amtdr' => $chargeAmount,
                        'roomtype' => $roomOccupancy->roomtype,
                        'roomcat' => $roomOccupancy->roomcat,
                        'foliono' => $roomoccrval->folioNo,
                        'restcode' => 'FOM' . $propertyId,
                        'billamount' => 0.00,
                        'taxper' => 0,
                        'onamt' => 0.00,
                        'folionodocid' => $roomoccrval->docid,
                        'taxcondamt' => 0,
                        'taxstru' => '',
                        'u_entdt' => $currentTime,
                        'settledate' => null,
                        'u_name' => $userName,
                        'u_ae' => 'a',
                        'modeset' => '',
                    ];

                    $serialNumber++;
                }

                // return 'sagar';

                $chargeEntries[] = [
                    'propertyid' => $propertyId,
                    'docid' => $generatedDocId,
                    'vno' => $voucherNumber,
                    'vtype' => $voucherType,
                    'sno' => $serialNumber,
                    'sno1' => $sno1Main,
                    'msno1' => $leaderId ?? 0,
                    'chqno' => $request->input('checkno') ?: $request->input('referencenoupi'),
                    'cardno' => $request->input('crnumber'),
                    'cardholder' => $request->input('holdername'),
                    'expdate' => $request->input('expdatecr'),
                    'bookno' => $request->input('batchno'),
                    'vdate' => $currentDate,
                    'vtime' => date('H:i:s'),
                    'vprefix' => $prefix,
                    'comp_code' => $request->input('compcode' . $i) ?? '',
                    'paycode' => $chargeCode,
                    'paytype' => $payCodeInfo->pay_type ?? '',
                    'comments' => $request->input('chargenarration' . $i),
                    'guestprof' => $roomOccupancy->guestprof,
                    'roomno' => $request->input('rooomoccroomno') ?? $roomOccupancy->roomno,
                    'amtdr' => 0.00,
                    'amtcr' => $chargeAmount,
                    'roomtype' => $roomOccupancy->roomtype,
                    'roomcat' => $roomOccupancy->roomcat,
                    'foliono' => $roomOccupancy->folioNo,
                    'restcode' => 'FOM' . $propertyId,
                    'billamount' => 0.00,
                    'taxper' => 0,
                    'onamt' => 0.00,
                    'folionodocid' => $roomOccupancy->docid,
                    'taxcondamt' => 0,
                    'taxstru' => '',
                    'u_entdt' => $currentTime,
                    'settledate' => $currentDate,
                    'u_name' => $userName,
                    'u_ae' => 'a',
                    'modeset' => 'S',
                ];

                $serialNumber++;
            }


            // Bulk insert charge entries for better performance
            if (!empty($chargeEntries)) {
                DB::table('paycharge')->insert($chargeEntries);
            }

            // Verify inserted records match expected count
            $expectedRows = $request->input('countrows');
            $actualRows = Paycharge::select('paycharge.*', 'revmast.name as revname')
                ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
                ->where('paycharge.propertyid', $propertyId)
                ->where('paycharge.folionodocid', $roomOccupancy->docid)
                ->where('vtype', $voucherType)
                ->whereNotNull('paycharge.paycode')
                ->whereNotNull('paycharge.paytype')
                ->whereNotNull('paycharge.modeset')
                ->where('sno1', $sno1Main)
                ->whereNot('paycharge.amtcr', 0)
                ->count();

            // if ($expectedRows != $actualRows) {
            //     // Clean up incomplete records
            //     Paycharge::where('propertyid', $propertyId)
            //         ->where('vtype', $voucherType)
            //         ->whereNotNull('paycharge.paycode')
            //         ->whereNotNull('paycharge.paytype')
            //         ->whereNotNull('paycharge.modeset')
            //         ->where('folionodocid', $roomOccupancy->docid)
            //         ->where('billno', 0)
            //         ->where('sno1', $sno1Main)
            //         ->delete();

            //     throw new Exception('Row count mismatch');
            // }
            // return 'sagar2';

            // Update voucher prefix
            VoucherPrefix::where('propertyid', $propertyId)
                ->where('v_type', $voucherType)
                ->where('prefix', $prefix)
                ->increment('start_srl_no');

            $guestprof = GuestProf::where('propertyid', $propertyId)
                ->where('docid', $docId)->first();


            // if ($wpenv != null) {
            //     if ($wpenv->checkyn == 'Y' && $wpenv->checkoutmsg != '' && $wpenv->checkouttemplate != '' && $guestprof->mobile_no != '') {
            //         $whatsapp = new WhatsappSend();
            //         $whatsapp->CheckoutSend($tbl->balance, $roomOccupancy->roomno, $roomOccupancy->name, $guestprof->mobile_no);
            //     }
            // }



            // exit;
            // Room move / settle updated roomocc + grpbookingdetails — availability changed.
            \App\Helpers\MasterDataCache::flushAvailability($this->propertyid);
            DB::commit();
            $wpenv = EnviroWhatsapp::where('propertyid', $this->propertyid)->first();

            if ($wpenv != null) {
                if (whatsappparameter()->checkyn == 'Y') {
                    if (whatsappparameter()->sendpdfcheckout == 1) {
                        $printData = buildPrintDataFOM($docId, $sno, $sno1Main);

                        // return $printData;

                        if ($printData) {
                            $timestamp = now()->format('Y-m-d_H-i-s');
                            $filename = 'FomBill_' . '_' . $timestamp . '.pdf';
                            // return $filename;
                            // return view('property.prints.fom.billprint_pdf', $printData);

                            try {
                                $pdf = Pdf::loadView('property.prints.fom.billprint_pdf', $printData)
                                    ->setPaper('a4', 'portrait')
                                    ->setOption('isHtml5ParserEnabled', true)
                                    ->setOption('isRemoteEnabled', true)
                                    ->setOption('defaultFont', 'Arial')
                                    ->setOption('dpi', 96)
                                    ->setOption('enable_php', false)
                                    ->setOption('enable_javascript', false)
                                    ->setOption('margin-top', 0)
                                    ->setOption('margin-bottom', 0)
                                    ->setOption('margin-left', 0)
                                    ->setOption('margin-right', 0);

                                // return $pdf->download('fombill.pdf');
                                $pdfContent = $pdf->output();

                                $whatsapp = new WhatsappSend();
                                $whatsapp->sendPdfToApiDynamic($filename, $pdfContent, $docId, $printData['invoiceno'], $printData['netamount'], $printData['paidss'], $printData['fombilldetail'], $printData['guest']);
                            } catch (Exception $e) {
                                Log::error('PDF Generation Error: ' . $e->getMessage());
                                // return back()->with('error', 'Error generating PDF: ' . $e->getMessage());
                            }
                        }
                    }
                }

                $mob = GuestProf::where('propertyid', $this->propertyid)->where('docid', $roomOccupancy->docid)->value('mobile_no');
                if (
                    $wpenv->checkyn == 'Y' &&
                    $wpenv->checkoutmsg != '' &&
                    $wpenv->checkoutmsgarray != '' &&
                    $wpenv->checkouttemplate != '' &&
                    $mob != ''
                ) {
                    $checkoutmsgarray = json_decode($wpenv->checkoutmsgarray, true);

                    $msgdata = [];
                    foreach ($checkoutmsgarray as $row) {
                        [$colname, $table] = $row;
                        if (endsWith($colname, 'billamount')) {
                            $value = $tbl->balance;
                        } else {
                            $value = DB::table($table)->where('sno', $sno)->where('sno1', $sno1Main)->where('propertyid', $this->propertyid)->where('docid', $roomOccupancy->docid)->value($colname);
                        }
                        $msgdata[] = $value;
                    }

                    $whatsapp = new WhatsappSend();
                    $whatsapp->MuzzTech($msgdata, $mob, 'Checkout', 'checkouttemplate');
                }

                if (
                    $wpenv->checkyn == 'Y' &&
                    $wpenv->checkoutmsgadmin != '' &&
                    $wpenv->checkoutmsgadminarray != '' &&
                    $wpenv->checkoutmsgadmintemplate != '' &&
                    $wpenv->managementmob != ''
                ) {
                    $checkoutmsgadminarray = json_decode($wpenv->checkoutmsgadminarray, true);

                    $msgdata = [];
                    foreach ($checkoutmsgadminarray as $row) {
                        [$colname, $table] = $row;
                        if (endsWith($colname, 'billamount')) {
                            $value = $tbl->balance;
                        } else {
                            if ($table == 'paycharge') {
                                $value = DB::table($table)->where('vtype', 'REC')->where('sno', $sno)->where('sno1', $sno1Main)->where('propertyid', $this->propertyid)->where('folionodocid', $roomOccupancy->docid)->value($colname);
                            } else {
                                $value = DB::table($table)->where('sno', $sno)->where('sno1', $sno1Main)->where('propertyid', $this->propertyid)->where('docid', $roomOccupancy->docid)->value($colname);
                            }
                        }
                        $msgdata[] = $value;
                    }

                    $whatsapp = new WhatsappSend();
                    $whatsapp->MuzzTech($msgdata, $wpenv->managementmob, 'Checkout Admin', 'checkoutmsgadmintemplate');
                }
            }

            return redirect('autorefreshmain');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Unable To Submit Room Re Settlement: ' . $e->getMessage());
        }
    }
}
