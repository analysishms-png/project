<?php

namespace App\Http\Controllers;

use App\Helpers\ResHelper;
use App\Helpers\WhatsappSend;
use App\Mail\ReservationConfirmation;
use App\Models\BookinPlanDetail;
use App\Models\ChannelEnviro;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Companyreg;
use App\Models\Guestfolio;
use App\Models\Suntran;
use App\Models\RoomMast;
use App\Models\Sale1;
use App\Models\Sale2;
use App\Models\Stock;
use App\Models\Paycharge;
use App\Models\MenuHelp;
use App\Models\Depart;
use App\Models\Revmast;
use App\Models\ServerMast;
use App\Models\EnviroPos;
use App\Models\GuestProf;
use App\Models\PrintingSetup;
use App\Models\UserPermission;
use App\Models\RoomOcc;
use App\Models\GuestReward;
use App\Models\Cities;
use App\Models\EnviroFom;
use App\Models\EnviroWhatsapp;
use App\Models\GrpBookinDetail;
use App\Models\SubGroup;
use App\Models\Sale1log;
use App\Models\Sale2log;
use App\Models\Stocklog;
use App\Models\Suntranlog;
use App\Models\Kot;
use App\Models\RoomCat;
use App\Models\RoomInclusive;
use App\Models\TaxStructure;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Filesystem\AwsS3V3Adapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\Finder\Iterator\VcsIgnoredFilterIterator;

use function App\Helpers\endsWith;
use function App\Helpers\removeSuffixIfExists;

class Reservation extends Controller
{
    protected $username;
    protected $email;
    protected $propertyid;
    protected $currenttime;
    protected $ptlngth;
    protected $prpid;
    protected $ncurdate;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->email = Auth::user()->email;
            $this->prpid = Auth::user()->propertyid;
            $propertydata = DB::table('users')->where('propertyid', $this->prpid)->first();
            $this->ncurdate = DB::table('enviro_general')->where('propertyid', Auth::user()->propertyid)->value('ncur');
            $this->propertyid = $propertydata->propertyid;
            $this->ptlngth = strlen($this->propertyid);
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');
            return $next($request);
        });
    }
    # Warning: Abandon hope, all who enter here. 😱


    public function revokeopen($code)
    {
        $value = Menuhelp::where('propertyid', $this->propertyid)->where('username', Auth::user()->name)->where('code', $code)->first();
        return $value;
    }

    public function ncurfetch()
    {
        $ncurdate = DB::table('enviro_general')
            ->where('propertyid', $this->propertyid)
            ->value('ncur');
        return $ncurdate;
    }

    public function ExportTable()
    {
        echo '<link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet" />';
        echo '<link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet" />';
        echo '<script src="https://code.jquery.com/jquery-3.5.1.js"></script>';
        echo '<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>';
        echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>';
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>';
        echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>';
        echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>';
    }

    public function DownloadTable($tableName, $title, $columnsToExport, $columnToSearch)
    {
        $exportColumnsJS = json_encode($columnsToExport);
        $searchColumnsJS = json_encode($columnToSearch);

        echo "<script>$(document).ready(function() {
        let table = $('#$tableName').DataTable({
            dom: 'Bfrtip',
            pageLength: 15,
            buttons: [
                {
                    extend: 'excelHtml5',
                    text: 'Excel <i class=\"fa fa-file-excel-o\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'Csv <i class=\"fa-solid fa-file-csv\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                },
                {
                    extend: 'pdfHtml5',
                    text: 'Pdf <i class=\"fa fa-file-pdf-o\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                },
                {
                    extend: 'print',
                    text: 'Print <i class=\"fa-solid fa-print\"></i>',
                    title: '$title',
                    filename: '$title',
                    exportOptions: {
                        columns: $exportColumnsJS
                    }
                }
            ],
            initComplete: function() {
                // Configure column-specific search inputs based on the specified columns
                let searchColumns = $searchColumnsJS;
                this.api().columns(searchColumns).every(function() {
                    let column = this;
                    let title = column.header().textContent;
                    let input = document.createElement('input');
                    input.placeholder = title;
                    $(input).appendTo($(column.footer()).empty()); // Use jQuery for better compatibility
                    $(input).on('keyup', function () {
                        if (column.search() !== this.value) {
                            column.search(this.value).draw();
                        }
                    });
                });
            }
        });
    });</script>";
    }

    public function openresletter(Request $request)
    {
        $bookno = $request->input('bookno');
        $sno = $request->input('sno');
        $vprefix = $request->input('year');

        $data = GrpBookinDetail::select(
            'grpbookingdetails.*',
            'paycharge.amtcr',
            'paycharge.vdate',
            'paycharge.foliono',
            'paycharge.vno',
            DB::raw("CONCAT(COALESCE(guestprof.add1, ''), ' ', COALESCE(guestprof.add2, ''), ' ', COALESCE(guestprof.city_name)) as guestadd"),
            'guestprof.state_name',
            'guestprof.bill_to',
            'guestprof.mobile_no',
            'guestprof.email_id',
            's.name as companyname',
            's.gstin as companygstin',
            't.name as travelname',
            't.gstin as travelgstin',
            'booking.ResStatus',
            'booking.vdate as bookvdate',
            'plan_mast.name as planname',
            'booking.BookedBy',
            'booking.Remarks as remarks'
        )
            ->leftJoin('paycharge', function ($query) {
                $query->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
                    ->on('paycharge.sno1', '=', 'grpbookingdetails.Sno');
            })
            ->leftJoin('guestprof', function ($query) {
                $query->on('guestprof.docid', '=', 'grpbookingdetails.BookingDocid');
            })
            ->leftJoin('booking', function ($query) use ($vprefix) {
                $query->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid')
                    ->on('booking.Property_ID', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('subgroup as s', function ($query) {
                $query->on('s.sub_code', '=', 'booking.Company')
                    ->on('s.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('subgroup as t', function ($query) {
                $query->on('t.sub_code', '=', 'booking.TravelAgency')
                    ->on('t.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('plan_mast', function ($query) {
                $query->on('plan_mast.pcode', '=', 'grpbookingdetails.Plan_Code')
                    ->on('plan_mast.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->where('grpbookingdetails.BookNo', $bookno)
            ->where('booking.Vprefix', $vprefix)
            // ->where('grpbookingdetails.Sno', $sno)
            ->where('grpbookingdetails.Property_ID', $this->propertyid)
            ->first();

        // return $data;

        $companydata = DB::table('company')->where('propertyid', $this->propertyid)->first();

        $chkplanb = BookinPlanDetail::where('propertyid', $this->propertyid)->where('foliono', $bookno)->first();
        $docid = $data->BookingDocid;
        if (!is_null($chkplanb)) {
            $rooms = BookinPlanDetail::select(
                'bookingplandetails.netplanamt as Tarrif',
                'room_cat.name as roomcatname',
                'grpbookingdetails.NoDays',
                DB::raw('SUM(grpbookingdetails.Adults) as total_adults'),
                DB::raw('SUM(grpbookingdetails.Childs) as total_childs'),
                'grpbookingdetails.IncTax',
                DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet')
            )
                ->leftJoin('grpbookingdetails', function ($join) {
                    $join->on('grpbookingdetails.BookingDocid', '=', 'bookingplandetails.docid')
                        ->on('grpbookingdetails.Sno', '=', 'bookingplandetails.sno1');
                })
                ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                ->where('bookingplandetails.propertyid', $this->propertyid)
                ->where('room_cat.propertyid', $this->propertyid)
                ->where('bookingplandetails.docid', $docid)
                ->groupBy(
                    'grpbookingdetails.RoomCat',
                    'room_cat.name',
                    'bookingplandetails.netplanamt'
                )
                ->get();
        } else {

            $rooms = GrpBookinDetail::select(
                'grpbookingdetails.Tarrif',
                'grpbookingdetails.NoDays',
                'room_cat.name as roomcatname',
                DB::raw('SUM(grpbookingdetails.Adults) as total_adults'),
                DB::raw('SUM(grpbookingdetails.Childs) as total_childs'),
                DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet'),
                'grpbookingdetails.IncTax',
            )
                ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                ->where('grpbookingdetails.BookingDocid', $docid)
                ->where('grpbookingdetails.Property_ID', $this->propertyid)
                ->groupBy(
                    'grpbookingdetails.RoomCat',
                    'room_cat.name',
                    'grpbookingdetails.RoomNo',
                    'grpbookingdetails.Tarrif'
                )
                ->get();
        }

        // return $rooms;

        $advance = Paycharge::where('propertyid', $this->propertyid)->where(['refdocid' => $docid, 'vtype' => 'ADRES', 'sno' => 1, 'sno1' => $data->Sno])->get();
        $enviro = EnviroFom::where('propertyid', $this->propertyid)->first();
        $curdate = $data->bookvdate;
        $roominclusive = RoomInclusive::select(
            'room_inclusive.*',
            'revmast.name as revmastname',
            'grpbookingdetails.NoDays',
            DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet')
        )
            ->leftJoin('grpbookingdetails', function ($join) {
                $join->on('grpbookingdetails.BookingDocid', '=', 'room_inclusive.docid');
            })
            ->leftJoin('revmast', function ($join) {
                $join->on('revmast.rev_code', '=', 'room_inclusive.rev_code')
                    ->on('revmast.propertyid', '=', 'room_inclusive.propertyid');
            })
            ->where('room_inclusive.propertyid', $this->propertyid)
            ->where('room_inclusive.docid', $docid)
            ->groupBy('room_inclusive.rev_code')
            ->get();

        // return $rooms;


        return view('property.prints.reservation.resletter', [
            'data' => $data,
            'company' => $companydata,
            'rooms' => $rooms,
            'advance' => $advance,
            'enviro' => $enviro,
            'curdate' => $curdate,
            'roominclusive' => $roominclusive,
        ]);
    }

    public function openrescard(Request $request)
    {
        $bookno = $request->input('bookno');
        $sno = $request->input('sno');
        $vprefix = $request->input('year');

        $data = GrpBookinDetail::select(
            'grpbookingdetails.*',
            'paycharge.amtcr',
            'paycharge.vdate',
            'paycharge.foliono',
            'paycharge.vno',
            DB::raw("CONCAT(COALESCE(guestprof.add1, ''), ' ', COALESCE(guestprof.add2, ''), ' ', COALESCE(guestprof.city_name)) as guestadd"),
            'guestprof.state_name',
            'guestprof.bill_to',
            'guestprof.mobile_no',
            'guestprof.email_id',
            's.name as companyname',
            's.gstin as companygstin',
            't.name as travelname',
            't.gstin as travelgstin',
            'booking.ResStatus',
            'booking.vdate as bookvdate',
            'plan_mast.name as planname',
            'booking.BookedBy',
            'booking.Remarks as remarks'
        )
            ->leftJoin('paycharge', function ($query) {
                $query->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
                    ->on('paycharge.sno1', '=', 'grpbookingdetails.Sno');
            })
            ->leftJoin('guestprof', function ($query) {
                $query->on('guestprof.docid', '=', 'grpbookingdetails.BookingDocid');
            })
            ->leftJoin('booking', function ($query) use ($vprefix) {
                $query->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid')
                    ->on('booking.Property_ID', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('subgroup as s', function ($query) {
                $query->on('s.sub_code', '=', 'booking.Company')
                    ->on('s.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('subgroup as t', function ($query) {
                $query->on('t.sub_code', '=', 'booking.TravelAgency')
                    ->on('t.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('plan_mast', function ($query) {
                $query->on('plan_mast.pcode', '=', 'grpbookingdetails.Plan_Code')
                    ->on('plan_mast.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->where('grpbookingdetails.BookNo', $bookno)
            ->where('booking.Vprefix', $vprefix)
            // ->where('grpbookingdetails.Sno', $sno)
            ->where('grpbookingdetails.Property_ID', $this->propertyid)
            ->first();

        // return $data;

        $companydata = DB::table('company')->where('propertyid', $this->propertyid)->first();

        $chkplanb = BookinPlanDetail::where('propertyid', $this->propertyid)->where('foliono', $bookno)->first();
        $docid = $data->BookingDocid;
        if (!is_null($chkplanb)) {
            $rooms = BookinPlanDetail::select(
                'bookingplandetails.netplanamt as Tarrif',
                'room_cat.name as roomcatname',
                DB::raw('SUM(grpbookingdetails.Adults) as total_adults'),
                DB::raw('SUM(grpbookingdetails.Childs) as total_childs'),
                'grpbookingdetails.IncTax',
                DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet')
            )
                ->leftJoin('grpbookingdetails', function ($join) {
                    $join->on('grpbookingdetails.BookingDocid', '=', 'bookingplandetails.docid')
                        ->on('grpbookingdetails.Sno', '=', 'bookingplandetails.sno1');
                })
                ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                ->where('bookingplandetails.propertyid', $this->propertyid)
                ->where('room_cat.propertyid', $this->propertyid)
                ->where('bookingplandetails.docid', $docid)
                ->groupBy(
                    'grpbookingdetails.RoomCat',
                    'room_cat.name',
                    'bookingplandetails.netplanamt'
                )
                ->get();
        } else {

            $rooms = GrpBookinDetail::select(
                'grpbookingdetails.Tarrif',
                'room_cat.name as roomcatname',
                DB::raw('SUM(grpbookingdetails.Adults) as total_adults'),
                DB::raw('SUM(grpbookingdetails.Childs) as total_childs'),
                DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet'),
                'grpbookingdetails.IncTax',
            )
                ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                ->where('grpbookingdetails.BookingDocid', $docid)
                ->where('grpbookingdetails.Property_ID', $this->propertyid)
                ->groupBy(
                    'grpbookingdetails.RoomCat',
                    'room_cat.name',
                    'grpbookingdetails.RoomNo',
                    'grpbookingdetails.Tarrif'
                )
                ->get();
        }

        $advance = Paycharge::where('propertyid', $this->propertyid)->where(['refdocid' => $docid, 'vtype' => 'ADRES', 'sno' => 1, 'sno1' => $data->Sno])->get();
        $enviro = EnviroFom::where('propertyid', $this->propertyid)->first();
        $curdate = $data->bookvdate;
        $roominclusive = RoomInclusive::select(
            'room_inclusive.*',
            'revmast.name as revmastname',
            DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet')
        )
            ->leftJoin('grpbookingdetails', function ($join) {
                $join->on('grpbookingdetails.BookingDocid', '=', 'room_inclusive.docid');
            })
            ->leftJoin('revmast', function ($join) {
                $join->on('revmast.rev_code', '=', 'room_inclusive.rev_code')
                    ->on('revmast.propertyid', '=', 'room_inclusive.propertyid');
            })
            ->where('room_inclusive.propertyid', $this->propertyid)
            ->where('room_inclusive.docid', $docid)
            ->groupBy('room_inclusive.rev_code')
            ->get();

        $paymode = DB::table('revmast')
            ->select('revmast.name as taxname', 'taxstru.name as taxstruname', 'subgroup.name as subname', 'sundrymast.name as sundryname', 'revmast.*')
            ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'revmast.ac_code')
            ->leftJoin('sundrymast', 'sundrymast.sundry_code', '=', 'revmast.sundry')
            ->leftJoin('taxstru', 'taxstru.str_code', '=', 'revmast.tax_stru')
            ->where('revmast.propertyid', $this->propertyid)
            ->where('revmast.field_type', 'P')
            ->orderBy('taxname', 'ASC')
            ->get();
        // return $rooms;


        return view('property.prints.reservation.rescard', [
            'data' => $data,
            'company' => $companydata,
            'rooms' => $rooms,
            'advance' => $advance,
            'enviro' => $enviro,
            'curdate' => $curdate,
            'roominclusive' => $roominclusive,
            'paymode' => $paymode,
        ]);
    }

    public function resmailposting(Request $request)
    {
        $bookno = $request->input('bookno');
        $sno = $request->input('sno');
        $data = GrpBookinDetail::select(
            'grpbookingdetails.*',
            'paycharge.amtcr',
            'paycharge.vdate',
            'paycharge.foliono',
            DB::raw("CONCAT(COALESCE(guestprof.add1, ''), ' ', COALESCE(guestprof.add2, ''), ' ', COALESCE(guestprof.city_name)) as guestadd"),
            'guestprof.state_name',
            's.name as companyname',
            's.gstin as companygstin',
            't.name as travelname',
            't.gstin as travelgstin',
            'booking.ResStatus',
            'booking.Email',
            'plan_mast.name as planname'
        )
            ->leftJoin('paycharge', function ($query) {
                $query->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
                    ->on('paycharge.sno1', '=', 'grpbookingdetails.Sno');
            })
            ->leftJoin('guestprof', function ($query) {
                $query->on('guestprof.docid', '=', 'grpbookingdetails.BookingDocid');
            })
            ->leftJoin('booking', function ($query) {
                $query->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid')
                    ->on('booking.Property_ID', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('subgroup as s', function ($query) {
                $query->on('s.sub_code', '=', 'booking.Company')
                    ->on('s.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('subgroup as t', function ($query) {
                $query->on('t.sub_code', '=', 'booking.TravelAgency')
                    ->on('t.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('plan_mast', function ($query) {
                $query->on('plan_mast.pcode', '=', 'grpbookingdetails.Plan_Code')
                    ->on('plan_mast.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->where('grpbookingdetails.BookNo', $bookno)
            ->where('grpbookingdetails.Sno', $sno)
            ->first();
        $companydata = DB::table('company')->where('propertyid', $this->propertyid)->first();

        $rooms = GrpBookinDetail::select('grpbookingdetails.*', 'room_cat.name as roomcatname')
            ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
            ->where('grpbookingdetails.BookNo', $bookno)
            ->get();
        $advance = Paycharge::where('propertyid', $this->propertyid)->where('sno', 1)->where('sno1', $data->Sno)->where('refdocid', $data->BookingDocid)->get();
        $enviro = EnviroFom::where('propertyid', $this->propertyid)->first();
        $curdate = $this->ncurdate;

        try {
            Mail::to($data->Email)->send(new ReservationConfirmation($companydata, $data, $rooms, $advance, $curdate, $enviro));
            return response()->json(['message' => 'Mail Sent Successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Unknown Error: ' . $e->getMessage()], 500);
        }
    }

    public function opencancelletter(Request $request)
    {
        $bookno = $request->input('bookno');
        $sno = $request->input('sno');
        $vprefix = $request->input('year');

        $data = GrpBookinDetail::select(
            'grpbookingdetails.*',
            DB::raw("COALESCE(grpbookingdetails.Adults, 0) + COALESCE(grpbookingdetails.Childs, 0) as pax"),
            'paycharge.amtcr',
            'paycharge.vdate',
            'paycharge.foliono',
            'paycharge.vno',
            DB::raw("CONCAT(COALESCE(guestprof.add1, ''), ' ', COALESCE(guestprof.add2, ''), ' ', COALESCE(guestprof.city_name)) as guestadd"),
            'guestprof.state_name',
            's.name as companyname',
            's.gstin as companygstin',
            't.name as travelname',
            't.gstin as travelgstin',
            'booking.ResStatus',
            'booking.vdate as bookvdate',
            'plan_mast.name as planname'
        )
            ->leftJoin('paycharge', function ($query) {
                $query->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
                    ->on('paycharge.sno1', '=', 'grpbookingdetails.Sno');
            })
            ->leftJoin('guestprof', function ($query) {
                $query->on('guestprof.docid', '=', 'grpbookingdetails.BookingDocid');
            })
            ->leftJoin('booking', function ($query) {
                $query->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid')
                    ->on('booking.Property_ID', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('subgroup as s', function ($query) {
                $query->on('s.sub_code', '=', 'booking.Company')
                    ->on('s.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('subgroup as t', function ($query) {
                $query->on('t.sub_code', '=', 'booking.TravelAgency')
                    ->on('t.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('plan_mast', function ($query) {
                $query->on('plan_mast.pcode', '=', 'grpbookingdetails.Plan_Code')
                    ->on('plan_mast.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->where('grpbookingdetails.BookNo', $bookno)
            ->where('booking.Vprefix', $vprefix)
            ->where('grpbookingdetails.Sno', $sno)
            ->first();
        $companydata = DB::table('company')->where('propertyid', $this->propertyid)->first();

        $chkplanb = BookinPlanDetail::where('propertyid', $this->propertyid)->where('foliono', $bookno)->first();
        $docid = $data->BookingDocid;
        if (!is_null($chkplanb)) {
            $rooms = BookinPlanDetail::select(
                'bookingplandetails.netplanamt as Tarrif',
                'room_cat.name as roomcatname',
                DB::raw("COALESCE(grpbookingdetails.Adults, 0) + COALESCE(grpbookingdetails.Childs, 0) as pax"),
                'grpbookingdetails.IncTax',
                DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet')
            )
                ->leftJoin('grpbookingdetails', function ($join) {
                    $join->on('grpbookingdetails.BookingDocid', '=', 'bookingplandetails.docid')
                        ->on('grpbookingdetails.Sno', '=', 'bookingplandetails.sno1');
                })
                ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                ->where('bookingplandetails.propertyid', $this->propertyid)
                ->where('room_cat.propertyid', $this->propertyid)
                ->where('grpbookingdetails.Property_ID', $this->propertyid)
                ->where('bookingplandetails.docid', $docid)
                ->groupBy('grpbookingdetails.RoomCat', 'room_cat.name', 'bookingplandetails.netplanamt')
                ->get();
        } else {
            $rooms = GrpBookinDetail::select(
                'grpbookingdetails.Tarrif',
                'room_cat.name as roomcatname',
                DB::raw("COALESCE(grpbookingdetails.Adults, 0) + COALESCE(grpbookingdetails.Childs, 0) as pax"),
                DB::raw('SUM(grpbookingdetails.RoomDet) as total_roomdet')
            )
                ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                ->where('grpbookingdetails.BookingDocid', $docid)
                ->where('grpbookingdetails.Property_ID', $this->propertyid)
                ->groupBy('grpbookingdetails.RoomCat', 'room_cat.name')
                ->get();
        }

        $rs = GrpBookinDetail::select(
            'grpbookingdetails.*',
            'room_cat.name as roomcatname',
            DB::raw("COALESCE(grpbookingdetails.Adults, 0) + COALESCE(grpbookingdetails.Childs, 0) as pax")
        )
            ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
            ->where('grpbookingdetails.BookNo', $bookno)
            ->get();
        $advance = Paycharge::where('propertyid', $this->propertyid)->where('sno', 1)->where('sno1', $data->Sno)->where('refdocid', $data->BookingDocid)->get();
        $enviro = EnviroFom::where('propertyid', $this->propertyid)->first();
        $curdate = $data->bookvdate;
        return view('property.cancletter', [
            'data' => $data,
            'company' => $companydata,
            'rooms' => $rooms,
            'advance' => $advance,
            'enviro' => $enviro,
            'curdate' => $curdate
        ]);
    }

    public function openreservationlist()
    {
        $permission = revokeopen(131112);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $datar = DB::table('grpbookingdetails')
            ->select(
                'grpbookingdetails.GuestName',
                'grpbookingdetails.ArrDate',
                'grpbookingdetails.DepDate',
                'grpbookingdetails.RoomNo',
                'grpbookingdetails.Cancel',
                'grpbookingdetails.U_Name',
                'grpbookingdetails.ContraDocId',
                'grpbookingdetails.Sno',
                'grpbookingdetails.BookingDocid',
                'grpbookingdetails.sn',
                'grpbookingdetails.Property_ID',
                'grpbookingdetails.BookNo',
                'grpbookingdetails.U_EntDt',
                DB::raw('SUM(paycharge.amtcr) - SUM(paycharge.amtdr) as amtcr'),
                'booking.vdate',
                'booking.Vprefix',
                'booking.RefBookNo',
                'booking.U_Name'
            )
            ->leftJoin('paycharge', function ($join) {
                $join->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
                    // ->where('paycharge.vtype', 'ADRES')
                    ->whereNull('paycharge.folionodocid')
                    ->where('paycharge.sno', '1');
            })
            ->leftJoin('booking', 'booking.DocId', '=', 'grpbookingdetails.BookingDocid')
            ->joinSub(
                DB::table('grpbookingdetails')
                    ->select('BookingDocid', DB::raw('MAX(sn) as max_id'))
                    ->where('Property_ID', $this->propertyid)
                    ->groupBy('BookingDocid'),
                'sub',
                function ($join) {
                    $join->on('grpbookingdetails.BookingDocid', '=', 'sub.BookingDocid')
                        ->on('grpbookingdetails.sn', '=', 'sub.max_id');
                }
            )
            ->where('grpbookingdetails.Property_ID', $this->propertyid)
            ->orderByDesc('booking.Vprefix')
            ->orderByDesc('booking.BookNo')
            ->groupBy([
                'grpbookingdetails.BookingDocid',
                'grpbookingdetails.sn',
                'grpbookingdetails.Property_ID',
                'grpbookingdetails.BookNo',
                'grpbookingdetails.U_EntDt',
                'booking.vdate',
                'booking.Vprefix'
            ]);

        //  if (Auth::user()->superwiser == 1) {
        $data = $datar->get();
        // } else {
        //     $data = $datar->where('grpbookingdetails.ArrDate', $this->ncurdate)->get();
        // }

        $channelenviro = ChannelEnviro::where('propertyid', $this->propertyid)->first();

        return view('property.reservationlist', [
            'data' => $data,
            'channelenviro' => $channelenviro
        ]);
    }

    public function updatecancel(Request $request)
    {
        $DocId = base64_decode($request->input('DocId'));

        $guestprof = GuestProf::where('docid', $DocId)->first();
        if (!$guestprof) {
            return back()->with('error', 'Guest profile not found.');
        }

        $guestcode = $guestprof->guestcode;
        $channelenviro = ChannelEnviro::where('propertyid', $this->propertyid)->first();

        if (optional($channelenviro)->checkyn === 'Y') {
            $updatecancel = ResHelper::UpdateCancel($DocId, $guestcode);
            // Log::info('UpdateCancel Response: ' . print_r($updatecancel, true));

            if (is_array($updatecancel) && isset($updatecancel['httpcode']) && $updatecancel['httpcode'] === 400) {
                return $this->cancelBooking($DocId);
            }
        }

        return $this->cancelBooking($DocId);
    }

    private function cancelBooking($DocId)
    {

        try {
            DB::beginTransaction();

            DB::table('booking')
                ->where('Property_ID', $this->propertyid)
                ->where('DocId', $DocId)
                ->update([
                    'Cancel' => 'Y',
                    'CancelUName' => Auth::user()->u_name,
                    'ResStatus' => 'Cancel'
                ]);

            DB::table('grpbookingdetails')
                ->where('Property_ID', $this->propertyid)
                ->where('BookingDocid', $DocId)
                ->update([
                    'Cancel' => 'Y',
                    'CancelUName' => Auth::user()->u_name,
                    'CancelDate' => $this->currenttime,
                ]);

            $wpenv = EnviroWhatsapp::where('propertyid', $this->propertyid)->first();

            if ($wpenv != null) {
                if (
                    $wpenv->checkyn == 'Y' &&
                    $wpenv->reservation != '' &&
                    $wpenv->reservationcancelarray != '' &&
                    $wpenv->reservationcanceltemplate != ''
                ) {
                    $reservationcancelarray = json_decode($wpenv->reservationcancelarray, true);

                    $msgdata = [];
                    foreach ($reservationcancelarray as $row) {
                        [$colname, $table] = $row;
                        if (endsWith($colname, 'sum')) {
                            $value = DB::table($table)->where('propertyid', $this->propertyid)->where('refdocid', $DocId)->sum(removeSuffixIfExists($colname, 'sum'));
                        } else {
                            $value = DB::table($table)->where('Property_ID', $this->propertyid)->where('BookingDocid', $DocId)->value($colname);
                        }

                        $mob = GuestProf::where('propertyid', $this->propertyid)->where('docid', $DocId)->value('mobile_no');
                        $msgdata[] = $value;
                    }

                    $whatsapp = new WhatsappSend();
                    $whatsapp->MuzzTech($msgdata, $mob, 'Reservation Cancel', 'reservationcanceltemplate');
                }
            }

            if ($wpenv != null) {
                if (
                    $wpenv->checkyn == 'Y' &&
                    $wpenv->adminreservation != '' &&
                    $wpenv->adminreservationcancelarray != '' &&
                    $wpenv->adminreservationcanceltemplate != '' &&
                    $wpenv->managementmob != ''
                ) {
                    $adminreservationcancelarray = json_decode($wpenv->adminreservationcancelarray, true);

                    $msgdata = [];
                    foreach ($adminreservationcancelarray as $row) {
                        [$colname, $table] = $row;
                        if (endsWith($colname, 'sum')) {
                            $value = DB::table($table)->where('propertyid', $this->propertyid)->where('refdocid', $DocId)->sum(removeSuffixIfExists($colname, 'sum'));
                        } else {
                            $value = DB::table($table)->where('Property_ID', $this->propertyid)->where('BookingDocid', $DocId)->value($colname);
                        }
                        $mob = GuestProf::where('propertyid', $this->propertyid)->where('docid', $DocId)->value('mobile_no');
                        $msgdata[] = $value;
                    }

                    $whatsapp = new WhatsappSend();
                    $whatsapp->MuzzTech($msgdata, $wpenv->managementmob, 'Reservation Cancel Admin', 'adminreservationcanceltemplate');
                }
            }

            DB::commit();
            return back()->with('success', 'Reservation Cancelled successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Unable to Cancel Reservation! ' . $e->getMessage());
        }
    }

    public function openpreformainvoice(Request $request)
    {
        $docid = base64_decode($request->query('docid'));

        if (empty($docid)) {
            $docid = base64_decode($request->input('DocId'));
        }

        $sno = base64_decode($request->input('Sno'));

        if ($sno == '') {
            $sno = GrpBookinDetail::where('Property_ID', $this->propertyid)->where('BookingDocid', $docid)->max('Sno');
        }

        $totals = DB::table('grpbookingdetails')
            ->select(
                'BookingDocid',
                'Property_ID',
                DB::raw('SUM(Adults) as total_adults'),
                DB::raw('SUM(Childs) as total_childs')
            )
            ->where('BookingDocid', $docid)
            ->where('Property_ID', $this->propertyid)
            ->groupBy('BookingDocid', 'Property_ID');

        $data = GrpBookinDetail::query()
            ->select(
                'grpbookingdetails.*',
                'tot.total_adults',
                'tot.total_childs',
                'paycharge.amtcr',
                'paycharge.vdate',
                'paycharge.foliono',
                'paycharge.vno',
                'guestprof.bill_to',
                DB::raw("CONCAT(COALESCE(guestprof.add1, ''), ' ', COALESCE(guestprof.add2, ''), ' ', COALESCE(guestprof.city_name)) as guestadd"),
                'guestprof.state_name',
                's.name as companyname',
                's.gstin as companygstin',
                't.name as travelname',
                't.gstin as travelgstin',
                'booking.ResStatus',
                'booking.vdate as bookvdate',
                'plan_mast.name as planname',
                'booking.BookedBy',
                'booking.Company',
                'room_cat.name as roomcatname'
            )
            ->joinSub($totals, 'tot', function ($join) {
                $join->on('tot.BookingDocid', '=', 'grpbookingdetails.BookingDocid')
                    ->on('tot.Property_ID', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('paycharge', function ($query) {
                $query->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
                    ->on('paycharge.sno1', '=', 'grpbookingdetails.Sno');
            })
            ->leftJoin('guestprof', function ($query) {
                $query->on('guestprof.docid', '=', 'grpbookingdetails.BookingDocid');
            })
            ->leftJoin('booking', function ($query) {
                $query->on('booking.DocId', '=', 'grpbookingdetails.BookingDocid')
                    ->on('booking.Property_ID', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('subgroup as s', function ($query) {
                $query->on('s.sub_code', '=', 'booking.Company')
                    ->on('s.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('subgroup as t', function ($query) {
                $query->on('t.sub_code', '=', 'booking.TravelAgency')
                    ->on('t.propertyid', '=', 'booking.Property_ID');
            })
            ->leftJoin('plan_mast', function ($query) {
                $query->on('plan_mast.pcode', '=', 'grpbookingdetails.Plan_Code')
                    ->on('plan_mast.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->leftJoin('room_cat', function ($query) {
                $query->on('room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                    ->on('room_cat.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->where('grpbookingdetails.BookingDocid', $docid)
            ->where('grpbookingdetails.Property_ID', $this->propertyid)
            ->first();

        // return $data;

        $taxRates = DB::table('taxstru')
            ->where('str_code', $data->RoomTaxStru)
            ->where('propertyid', $this->propertyid)
            ->get();

        $roomTaxCodes = $taxRates->pluck('tax_code')->filter()->unique()->implode(',');

        // Slab boundary + rates computed in PHP with null guards (Between lower/upper, else <= row)
        $betweenRow = $taxRates->where('comp_operator', 'Between')->first();
        $cgstBetween = $taxRates->where('tax_code', "CGSS{$this->propertyid}")->where('comp_operator', 'Between')->first();
        $sgstBetween = $taxRates->where('tax_code', "SGSS{$this->propertyid}")->where('comp_operator', 'Between')->first();
        $cgstElse = $taxRates->where('tax_code', "CGSS{$this->propertyid}")->where('comp_operator', '<=')->first();
        $sgstElse = $taxRates->where('tax_code', "SGSS{$this->propertyid}")->where('comp_operator', '<=')->first();

        $betweenUpper = $betweenRow ? (float)$betweenRow->limit1 : 0;
        $betweenLower = $betweenRow ? (float)$betweenRow->limits : 0;
        $cgstBetweenRate = $cgstBetween ? (float)$cgstBetween->rate : ($cgstElse ? (float)$cgstElse->rate : 0);
        $sgstBetweenRate = $sgstBetween ? (float)$sgstBetween->rate : ($sgstElse ? (float)$sgstElse->rate : 0);
        $cgstElseRate = $cgstElse ? (float)$cgstElse->rate : $cgstBetweenRate;
        $sgstElseRate = $sgstElse ? (float)$sgstElse->rate : $sgstBetweenRate;

        $rooms = GrpBookinDetail::select(
            'grpbookingdetails.Tarrif',
            'grpbookingdetails.RoomCat',
            'grpbookingdetails.NoDays',
            'grpbookingdetails.Adults',
            'grpbookingdetails.Childs',
            'grpbookingdetails.RoomDet',
            'grpbookingdetails.IncTax',
            'room_cat.name as roomcatname',
            DB::raw("CASE 
                WHEN grpbookingdetails.Tarrif >= {$betweenLower} AND grpbookingdetails.Tarrif <= {$betweenUpper}
                    THEN {$cgstBetweenRate}
                ELSE {$cgstElseRate}
            END as cgst_rate"),
            DB::raw("CASE 
                WHEN grpbookingdetails.Tarrif >= {$betweenLower} AND grpbookingdetails.Tarrif <= {$betweenUpper}
                    THEN {$sgstBetweenRate}
                ELSE {$sgstElseRate}
            END as sgst_rate")
        )
            ->leftJoin('room_cat', function ($join) {
                $join->on('room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
                    ->on('room_cat.propertyid', '=', 'grpbookingdetails.Property_ID');
            })
            ->where('grpbookingdetails.BookingDocid', $docid)
            ->where('grpbookingdetails.Property_ID', $this->propertyid)
            ->get();

        $advance = Paycharge::where('propertyid', $this->propertyid)->where('sno', 1)->where('sno1', $data->Sno)->where('refdocid', $docid)->get();
        $enviro = EnviroFom::where('propertyid', $this->propertyid)->first();
        $curdate = $data->bookvdate;
        $grpBookingSub = DB::table('grpbookingdetails')
            ->selectRaw('BookingDocid, SUM(RoomDet) as total_roomdet, COUNT(*) as total_rows, SUM(NoDays) as total_nights')
            ->groupBy('BookingDocid');

        $taxSub = DB::table('taxstru')
            ->selectRaw("str_code, propertyid, GROUP_CONCAT(DISTINCT tax_code ORDER BY tax_code SEPARATOR ',') as tax_codes")
            ->groupBy('str_code', 'propertyid');

        $roominclusive = DB::table('room_inclusive as ri')
            ->select(
                'ri.*',
                'rm.name as revmastname',
                'rm.hsn_code',
                DB::raw('COALESCE(gbd.total_roomdet, 0) as total_roomdet'),
                DB::raw('COALESCE(gbd.total_nights, 0) as total_nights'),
                'rm.tax_inc as IncTax',
                DB::raw('COALESCE(gbd.total_rows, 0) as total_rows'),
                'ts.tax_codes',
                DB::raw("
            CASE
                WHEN ri.amount <= 7500.00 THEN 2.5
                ELSE 9
            END as cgst_rate
        "),
                DB::raw("
            CASE
                WHEN ri.amount <= 7500.00 THEN 2.5
                ELSE 9
            END as sgst_rate
        ")
            )
            ->leftJoin('revmast as rm', function ($join) {
                $join->on('rm.rev_code', '=', 'ri.rev_code')
                    ->on('rm.propertyid', '=', 'ri.propertyid');
            })
            ->leftJoinSub($grpBookingSub, 'gbd', function ($join) {
                $join->on('gbd.BookingDocid', '=', 'ri.docid');
            })
            ->leftJoinSub($taxSub, 'ts', function ($join) {
                $join->on('ts.str_code', '=', 'rm.tax_stru')
                    ->on('ts.propertyid', '=', 'rm.propertyid');
            })
            ->where('ri.propertyid', $this->propertyid)
            ->where('ri.docid', $docid)
            ->orderBy('ri.sno')
            ->get();

        $allroomcats = $rooms->pluck('RoomCat')->unique()->toArray();
        $roomcatsnameswithcomma = '';
        foreach ($allroomcats as $key => $value) {
            $roomcatname = RoomCat::where('propertyid', $this->propertyid)->where('cat_code', $value)->value('name');
            if ($key == 0) {
                $roomcatsnameswithcomma .= $roomcatname;
            } else {
                $roomcatsnameswithcomma .= ', ' . $roomcatname;
            }
        }

        // return $roominclusive;

        $taxCodes = $roomTaxCodes;
        $taxCodesArray = $taxCodes ? explode(',', $taxCodes) : [];

        $txnames = [];
        if (!empty($taxCodesArray)) {
            $taxDetails = Revmast::where('propertyid', $this->propertyid)
                ->whereIn('rev_code', $taxCodesArray)
                ->get(['rev_code', 'name']);

            foreach ($taxDetails as $taxDetail) {
                $taxRate = TaxStructure::where('propertyid', $this->propertyid)
                    ->where('tax_code', $taxDetail->rev_code)
                    ->value('rate');

                $txnames[] = [
                    'code' => $taxDetail->rev_code,
                    'name' => $taxDetail->name,
                    'rate' => $taxRate,
                ];
            }
        }

        // return $rooms;

        return view('property.prints.reservation.preforma', [
            'data' => $data,
            'rooms' => $rooms,
            'advance' => $advance,
            'enviro' => $enviro,
            'curdate' => $curdate,
            'roominclusive' => $roominclusive,
            'txnames' => $txnames,
            'roomcatsnameswithcomma' => $roomcatsnameswithcomma,
        ]);
    }
}
