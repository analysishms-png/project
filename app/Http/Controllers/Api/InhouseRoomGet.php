<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Companyreg;
use App\Models\GrpBookinDetail;
use App\Models\Paycharge;
use App\Models\RoomBlockout;
use App\Models\RoomOcc;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InhouseRoomGet extends Controller
{
    public function bookedrooms(Request $request)
    {
        $client = $request->attributes->get('api_client');

        $company = Companyreg::where('propertyid', $client->propertyid)->first();

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found'
            ], 404);
        }

        $propertyid = $client->propertyid;
        try {
            $bookedroomdata = RoomOcc::select([
                'roomocc.docid',
                'roomocc.folioNo',
                'roomocc.sno1',
                'roomocc.sno',
                'roomocc.roomno',
                'roomocc.roomcat',
                'roomocc.plancode',
                'roomocc.guestprof',
                'roomocc.name as name',
                'roomocc.chkindate',
                'roomocc.depdate',
                'roomocc.leaderyn',
                'roomocc.propertyid',
                'roomocc.roomrate',
                'roomocc.adult',
                'roomocc.children',
                'booking.BookedBy',
                DB::raw('DATE_SUB(roomocc.depdate, INTERVAL 1 DAY) as depdate_minus_one'),
                DB::raw('COALESCE(paycharge.billno, 0) as billno'),
                'enviro_form.checkout as envcheck',
                'room_cat.cat_code',
                'room_cat.name as roomcatname',
                'guestprof.con_prefix',
                'guestprof.complimentry',
                'guestprof.mobile_no',
                'guestprof.guestcode',
                'plan_mast.pcode',
                'guestfolio.company',
                'guestfolio.pickupdrop',
                'guestfolio.remarks',
                'plan_mast.name as planname',
                'sc.name as companyname',
                'st.name as travelname'
            ])
                ->where('roomocc.propertyid', $propertyid)
                ->whereNull('roomocc.type')
                ->leftJoin('guestprof', function ($join) use ($propertyid) {
                    $join->on('guestprof.guestcode', '=', 'roomocc.guestprof')
                        ->where('guestprof.propertyid', '=', $propertyid);
                })

                ->leftJoin('guestfolio', function ($join) use ($propertyid) {
                    $join->on('guestfolio.docid', '=', 'roomocc.docid')
                        ->on('guestfolio.guestprof', '=', 'roomocc.guestprof');
                })

                ->leftJoin('grpbookingdetails', function ($join) use ($propertyid) {
                    $join->on('grpbookingdetails.ContraDocId', '=', 'roomocc.docid')
                        ->where('grpbookingdetails.Property_ID', '=', $propertyid);
                })

                ->leftJoin('booking', function ($join) {
                    $join->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid');
                })

                ->join('room_cat', 'roomocc.roomcat', '=', 'room_cat.cat_code')

                ->leftJoin('plan_mast', 'roomocc.plancode', '=', 'plan_mast.pcode')

                ->leftJoin('enviro_form', 'enviro_form.propertyid', '=', 'roomocc.propertyid')

                ->leftJoin('subgroup as sc', 'sc.sub_code', '=', 'guestfolio.company')

                ->leftJoin('subgroup as st', 'st.sub_code', '=', 'guestfolio.travelagent')

                ->leftJoin('paycharge', function ($join) {
                    $join->on('paycharge.folionodocid', '=', 'roomocc.docid')
                        ->on('paycharge.sno1', '=', 'roomocc.sno1')
                        ->whereIn('paycharge.vtype', ['RC', 'REV']);
                })

                ->groupBy([
                    'roomocc.docid',
                    'roomocc.sno1',
                    'roomocc.sno',
                    'roomocc.roomno',
                    'roomocc.roomcat',
                    'roomocc.plancode',
                    'roomocc.guestprof',
                    'roomocc.name',
                    'roomocc.chkindate',
                    'roomocc.depdate',
                    'roomocc.leaderyn',
                    'roomocc.propertyid',
                    'booking.BookedBy',
                    'enviro_form.checkout',
                    'room_cat.cat_code',
                    'room_cat.name',
                    'guestprof.con_prefix',
                    'guestprof.mobile_no',
                    'guestprof.guestcode',
                    'plan_mast.pcode',
                    'guestfolio.company',
                    'guestfolio.pickupdrop',
                    'guestfolio.remarks',
                    'plan_mast.name',
                    'sc.name',
                    'st.name'
                ])
                ->orderBy('roomocc.roomno')
                ->get();

            $amountdetails = RoomOcc::select([
                'roomocc.name as guestname',
                'roomocc.docid',
                'roomocc.sno1',
                'roomocc.sno',
                'roomocc.leaderyn',
                'roomocc.roomno',
                DB::raw('COALESCE(MAX(paycharge.msno1), 0) AS msno1'),
                DB::raw('COALESCE(SUM(CASE WHEN paycharge.amtdr IS NOT NULL THEN paycharge.amtdr ELSE 0 END), 0.00) AS totalamt'),
                DB::raw('COALESCE(SUM(CASE WHEN paycharge.amtcr IS NOT NULL THEN paycharge.amtcr ELSE 0 END), 0.00) AS paidamt'),
                DB::raw('COALESCE(SUM(CASE WHEN paycharge.amtdr IS NOT NULL THEN paycharge.amtdr ELSE 0 END) - SUM(CASE WHEN paycharge.amtcr IS NOT NULL THEN paycharge.amtcr ELSE 0 END), 0.00) as balance'),
                DB::raw('COALESCE(MAX(paycharge.billno), 0) AS billno')
            ])
                ->leftJoin('paycharge', function ($join) {
                    $join->on('paycharge.folionodocid', '=', 'roomocc.docid')
                        ->on('paycharge.sno1', '=', 'roomocc.sno1');
                })
                ->where('roomocc.propertyid', $propertyid)
                ->whereNotNull('roomocc.docid')
                ->whereNull('roomocc.type')
                ->groupBy(['roomocc.docid', 'roomocc.sno1', 'roomocc.name', 'roomocc.sno', 'roomocc.leaderyn', 'roomocc.roomno'])
                ->orderBy('roomocc.roomno')
                ->get();

            $roomblockout = RoomBlockout::select(['roomcode', 'fromdate', 'reasons', 'propertyid', 'todate', 'block'])
                ->where('propertyid', $propertyid)
                ->whereNull('cleardate')
                ->orderBy('roomcode')
                ->get();

            $data = [
                'bookedroomdata' => $bookedroomdata,
                'amountdetails' => $amountdetails,
                'roomblockout' => $roomblockout
            ];

            return response()->json([
                'status' => true,
                'message' => count($bookedroomdata) . ' In-house Booked room details retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving in-house Booked room details:'
            ], 500);
        }
    }

    public function reservedrooms(Request $request)
    {
        $client = $request->attributes->get('api_client');

        $company = Companyreg::where('propertyid', $client->propertyid)->first();

        if (!$company) {
            return response()->json([
                'status' => false,
                'message' => 'Company not found'
            ], 404);
        }

        try {
            $propertyid = $client->propertyid;
            $bookedroomdata = DB::table('grpbookingdetails')
                ->select(
                    'booking.BookedBy',
                    'booking.Remarks',
                    'booking.pickupdrop',
                    'grpbookingdetails.*',
                    DB::raw('DATE_SUB(grpbookingdetails.DepDate, INTERVAL 1 DAY) as depdate_minus_one'),
                    'room_cat.cat_code',
                    'room_cat.name as roomcatname',
                    'guestprof.bill_to',
                    'guestprof.con_prefix',
                    'guestprof.mobile_no',
                    'guestprof.guestcode',
                    'grpbookingdetails.GuestProf',
                    'plan_mast.pcode',
                    'plan_mast.name as planname',
                    'bookingplandetails.sno1 as bsno1',
                    'bookingplandetails.netplanamt as plannetamt',
                )
                ->join('guestprof', 'guestprof.guestcode', '=', 'grpbookingdetails.GuestProf')
                ->join('room_cat', 'grpbookingdetails.RoomCat', '=', 'room_cat.cat_code')
                ->leftJoin('plan_mast', 'grpbookingdetails.Plan_Code', '=', 'plan_mast.pcode')
                ->leftJoin('bookingplandetails', function ($join) {
                    $join->on('bookingplandetails.docid', '=', 'grpbookingdetails.BookingDocid')
                        ->on('bookingplandetails.sno1', '=', 'grpbookingdetails.Sno');
                })
                ->leftJoin('booking', function ($query) use ($propertyid) {
                    $query->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid')
                        ->where('booking.Property_ID', $propertyid);
                })
                ->where('grpbookingdetails.Property_ID', $propertyid)
                ->where('grpbookingdetails.Cancel', 'N')
                ->where(function ($query) {
                    $query->whereNotNull('grpbookingdetails.Plan_Code')
                        ->orWhereNull('grpbookingdetails.Plan_Code');
                })
                ->where(function ($query) {
                    $query->where('grpbookingdetails.ContraDocId', '')
                        ->orWhereNull('grpbookingdetails.ContraDocId');
                })
                ->groupBy(
                    'grpbookingdetails.BookingDocid',
                    'grpbookingdetails.Sno',
                )
                ->get();

            // Batch the per-booking advance lookup (was 1 query per booking row).
            $bookingDocids = $bookedroomdata->pluck('BookingDocid')->unique()->filter()->values();
            $advances = collect();
            if ($bookingDocids->isNotEmpty()) {
                $advances = Paycharge::where('propertyid', $propertyid)
                    ->where('sno', 1)
                    ->whereIn('refdocid', $bookingDocids)
                    ->get()
                    ->groupBy('refdocid');
            }

            foreach ($bookedroomdata as $row) {
                $row->advance = $advances->get($row->BookingDocid, collect());
            }

            $emptycategory = GrpBookinDetail::select(
                'RoomCat as room_cat',
                'BookingDocid',
                'ArrDate',
                'DepDate',
                DB::raw('COUNT(*) as emptycategory')
            )
                ->where('RoomNo', '=', 0)
                ->where('Property_ID', '=', $propertyid)
                ->groupBy('RoomCat')
                ->get();

            $emptyrooms = GrpBookinDetail::where('Property_ID', $propertyid)->where('RoomNo', '=', '0')
                ->groupBy('BookingDocid')
                ->get();

            $data = [
                'bookedroomdata' => $bookedroomdata,
                'emptycategory' => $emptycategory,
                'emptyrooms' => $emptyrooms
            ];

            return response()->json([
                'status' => true,
                'message' => count($bookedroomdata) . ' In-house Reserved room details retrieved successfully',
                'data' => $data,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving in-house Reserved room details:'
            ], 500);
        }
    }
}
