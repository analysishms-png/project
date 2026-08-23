<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
use App\Services\PayChargeLogService;
use App\Services\DailyReportSnapshotService;
use App\Models\Bookings;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Guestfolio;
use App\Models\Suntran;
use App\Models\Sale1;
use App\Models\Sale2;
use App\Models\Stock;
use App\Models\SubGroup;
use App\Models\MenuHelp;
use App\Models\Paycharge;
use App\Models\Companyreg;
use App\Models\RoomOcc;
use App\Models\FomBillDetail;
use App\Models\BussSource;
use App\Models\EnviroFom;
use App\Models\Depart;
use App\Models\EInvoiceBill;
use App\Models\EnviroGeneral;
use App\Models\EnviroInventory;
use App\Models\Focc;
use App\Models\GrpBookinDetail;
use App\Models\ItemCatMast;
use App\Models\ItemMast;
use App\Models\Kot;
use App\Models\PaychargeH;
use App\Models\Revmast;
use App\Models\RoomCat;
use App\Models\RoomMast;
use App\Models\States;
use App\Models\TaxStructure;
use App\Models\VoucherType;
use Illuminate\Support\Facades\Auth;
use Exception;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Laravel\Ui\Presets\React;
use Laravel\Ui\UiCommand;
use Monolog\Formatter\GoogleCloudLoggingFormatter;
use Monolog\Handler\FlowdockHandler;
use Symfony\Component\CssSelector\Parser\Handler\NumberHandler;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Mailer\Transport\Dsn;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\FlareClient\Report;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use function PHPSTORM_META\type;
use function Termwind\ask;

// use PhpOffice\PhpSpreadsheet\Spreadsheet;
// use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Reporting extends Controller
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
   # Warning: Abandon hope, all who enter here. ðŸ˜±

   public function ncurfetch()
   {
      $ncurdate = DB::table('enviro_general')
         ->where('propertyid', $this->propertyid)
         ->value('ncur');
      return $ncurdate;
      $paycharge = Paycharge::$encrypter->value;
   }

   public function showheader()
   {
      $username = Auth::user()->name;
      return view('property.layouts.header', ['data' => $username]);
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
      echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>';
      echo '<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>';
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

   public function revokeopen($code)
   {
      $value = Menuhelp::where('propertyid', $this->propertyid)->where('username', Auth::user()->name)->where('code', $code)->first();
      return $value;
   }

   public function report_bulkcharge(Request $request)
   {
      $permission = revokeopen(141212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $todate = $this->ncurdate;
      $fromdate = date('Y-m-d', strtotime('-1 month', strtotime($this->ncurdate)));
      $bsource = DB::table('busssource')
         ->where('propertyid', $this->propertyid)
         ->orderBy('name', 'ASC')->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      $companysub = SubGroup::where('propertyid', $this->propertyid)->whereIn('comp_type', ['Corporate'])
         ->orderBy('name')->groupBy('sub_code')->get();
      $travelagents = \App\Helpers\MasterDataCache::travelAgents($this->propertyid);
      $bussdata = BussSource::where('propertyid', $this->propertyid)->get();

      $uniqpay = Paycharge::where('propertyid', $this->propertyid)->groupBy('paytype')->get();

      $roundoff = 'ROFF' . $this->propertyid;
      $disc = 'DISC' . $this->propertyid;
      $revmast = Revmast::where('revmast.propertyid', $this->propertyid)
         ->where('field_type', 'C')
         ->where('Desk_code', '=', 'FOM' . $this->propertyid)
         ->whereNotIn('revmast.rev_code', [$roundoff, $disc])
         ->whereNot('seq_no', '0')
         ->distinct()
         ->orderBy('seq_no', 'ASC')
         ->get();

      return view('property.report_bulkcharge', [
         'fromdate' => $fromdate,
         'todate' => $todate,
         'statename' => $statename,
         'company' => $company,
         'bsource' => $bsource,
         'revmast' => $revmast,
         'companysub' => $companysub,
         'travelagents' => $travelagents,
         'bussdata' => $bussdata,
         'uniqpay' => $uniqpay
      ]);
   }

   public function fetchdatabillprint(Request $request)
   {
      $docid = $request->input('docid');
      $sno1 = $request->input('sno1');
      $rocc = Roomocc::where('propertyid', $this->propertyid)->where('docid', $docid)->where('leaderyn', 'Y')->first();
      if ($rocc) {
         $paychargedata = Paycharge::select('paycharge.*', 'revmast.field_type')->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
            ->where('paycharge.propertyid', $this->propertyid)
            ->where('paycharge.folionodocid', $docid)
            ->whereNull('paycharge.modeset')->orderBy('paycharge.vdate', 'ASC')->orderBy('paycharge.vno', 'ASC')->orderBy('paycharge.sno', 'ASC')->get();
      } else {
         $paychargedata = Paycharge::select('paycharge.*', 'revmast.field_type')->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
            ->where('paycharge.propertyid', $this->propertyid)
            ->where('paycharge.folionodocid', $docid)
            ->where('paycharge.sno1', $sno1)
            ->whereNull('paycharge.modeset')->orderBy('paycharge.vdate', 'ASC')->orderBy('paycharge.vno', 'ASC')->orderBy('paycharge.sno', 'ASC')->get();
      }

      return json_encode($paychargedata);
   }

   public function fetchbilldata(Request $request)
   {
      $billno = $request->input('billno');
      $guestname = $request->input('guestname');
      $vprefix = $request->vprefix;
      $aprilstart = $vprefix . '-04-01';

      if ($guestname != '') {

         $fetchguestnameroomocc = Roomocc::where('propertyid', $this->propertyid)
            ->where('name', $guestname)
            ->where('vprefix', $vprefix)
            ->first();
         if (!$fetchguestnameroomocc) {
            return json_encode('Invalid');
         }

         if ($fetchguestnameroomocc->chkindate <=  $this->ncurdate && $fetchguestnameroomocc)

            if ($fetchguestnameroomocc->userchkoutdate == null) {
               return json_encode('Invalid');
            }

         $fechbillno = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $fetchguestnameroomocc->docid)
            ->where('vprefix', $vprefix)
            ->where('vtype', 'RC')->first();

         $billno = $fechbillno->billno;
      }

      $chkbilltrue = DB::table('paycharge')
         ->where('propertyid', $this->propertyid)
         ->where('billno', $billno)
         ->where('vprefix', $vprefix)
         ->whereNull('modeset')
         ->whereIn('vtype', ['RC', 'REV'])
         ->limit(1)
         ->first();

      if (!$chkbilltrue) {
         return json_encode('Invalid');
      }

      $paychargedata = DB::table('paycharge')
         ->where('propertyid', $this->propertyid)
         ->where('vprefix', $vprefix)
         ->where('billno', $billno)
         ->whereNull('modeset')
         ->get();

      foreach ($paychargedata as $data) {
         $docid = $data->folionodocid;
         // $sno1 = $data->sno1;
         $sno = $data->sno;
      }

      $fomdata = FomBillDetail::where('propertyid', $this->propertyid)
         ->where('billno', $billno)
         ->where('status', 'settle')
         ->where('folionodocid', $docid)
         ->first();

      $sno1 = $fomdata->sno1;

      $paymode = Paycharge::select('paycharge.paycode', 'paycharge.comp_code', 'revmast.pay_type')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.vprefix', $vprefix)
         ->where('paycharge.propertyid', $this->propertyid)->where('paycharge.folionodocid', $docid)
         ->where('paycharge.modeset', 'S')->whereNot('paycharge.vtype', 'REV')->get();
      $paymodedata = [];
      foreach ($paymode as $row) {
         $pay_type = $row->pay_type;
         $paydata = null;
         if ($pay_type == 'Company') {
            $paydata = SubGroup::where('propertyid', $this->propertyid)
               ->where('sub_code', $row->comp_code)
               ->first();
         }

         $paymodedata[] = [
            'pay_type' => $pay_type,
            'paycompname' => ($paydata) ? $paydata->name : null
         ];
      }

      $companydata = DB::table('company')->where('propertyid', $this->propertyid)->first();
      $rocc = Roomocc::where('propertyid', $this->propertyid)->where('docid', $docid)->where('leaderyn', 'Y')->first();
      if ($rocc) {

         $roomoccdata = RoomOcc::select(
            'roomocc.*',
            'cities.cityname',
            'states.name as statename',
            'room_cat.name as roomcategory',
            'company.name as companyname',
            'company.gstin as companygst',
            'travelagent.name as travelname',
            'travelagent.gstin as travelgst',
            'guestfolio.add1',
            'guestfolio.add2',
            'guestprof.guestsign'
         )
            ->leftJoin('guestprof', 'guestprof.docid', '=', 'roomocc.docid')
            ->leftJoin('cities', 'cities.city_code', '=', 'guestprof.city')
            ->leftJoin('states', 'states.state_code', '=', 'guestprof.state_code')
            ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'roomocc.roomcat')
            ->leftJoin('guestfolio', 'guestfolio.docid', '=', 'roomocc.docid')
            ->leftJoin('subgroup as company', 'company.sub_code', '=', 'guestfolio.company')
            ->leftJoin('subgroup as travelagent', 'travelagent.sub_code', '=', 'guestfolio.travelagent')
            ->where('roomocc.propertyid', $this->propertyid)
            ->where('roomocc.docid', $docid)
            // ->where('roomocc.vprefix', $vprefix)
            ->where(function ($query) {
               $query->whereNull('roomocc.type')
                  ->orWhere('roomocc.type', 'O');
            })
            ->first();
      } else {
         // return 'sagar';
         $roomoccdata = RoomOcc::select(
            'roomocc.*',
            'cities.cityname',
            'states.name as statename',
            'room_cat.name as roomcategory',
            'company.name as companyname',
            'company.gstin as companygst',
            'travelagent.name as travelname',
            'travelagent.gstin as travelgst',
            'guestfolio.add1',
            'guestfolio.add2',
            'guestprof.guestsign'
         )
            ->leftJoin('guestprof', 'guestprof.docid', '=', 'roomocc.docid')
            ->leftJoin('cities', 'cities.city_code', '=', 'guestprof.city')
            ->leftJoin('states', 'states.state_code', '=', 'guestprof.state_code')
            ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'roomocc.roomcat')
            ->leftJoin('guestfolio', 'guestfolio.docid', '=', 'roomocc.docid')
            ->leftJoin('subgroup as company', 'company.sub_code', '=', 'guestfolio.company')
            ->leftJoin('subgroup as travelagent', 'travelagent.sub_code', '=', 'guestfolio.travelagent')
            ->where('roomocc.propertyid', $this->propertyid)
            ->where('roomocc.docid', $docid)
            ->where('roomocc.sno1', $sno1)
            // ->where('roomocc.vprefix', $vprefix)
            ->where(function ($query) {
               $query->whereNull('roomocc.type')
                  ->orWhere('roomocc.type', 'O');
            })
            ->first();
      }

      $totaldebit = 0;
      $totalcredit = 0;

      foreach ($paychargedata as $data) {
         $totaldebit += $data->amtdr;
         $totalcredit += $data->amtcr;
      }
      $onamt = $paychargedata[0]->onamt;
      $billamt = str_replace(',', '', number_format($totaldebit - $totalcredit, 2));
      $divcode = DB::table('company')->where('propertyid', $this->propertyid)->value('division_code');
      $ranges = DateHelper::calculateDateRanges($aprilstart);
      if ($divcode == null) {
         $invoiceno = 'BCNT/' . $ranges['finyear']['current'] . '-' . substr($ranges['finyear']['nextyear'], 2) . '/' . $billno;
      } else {
         $invoiceno = $divcode . '/' . $ranges['finyear']['current'] . '-' . substr($ranges['finyear']['nextyear'], 2) . '/' . $billno;
      }
      $sumfieldc = DB::table('paycharge')
         ->join('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.folionodocid', $docid)
         ->where('revmast.field_type', 'C')
         ->where('paycharge.vtype', 'REV')
         ->where('paycharge.vprefix', $vprefix)
         ->sum('paycharge.amtdr');

      $sumtyperev = DB::table('paycharge')
         ->where('propertyid', $this->propertyid)
         ->where('folionodocid', $docid)
         ->where('paycharge.vprefix', $vprefix)
         ->where('sno', 1)
         ->where('sno1', $sno1)
         ->where('vtype', 'REV')
         ->sum('amtdr');

      $einvoicebill = EInvoiceBill::where('propertyid', $this->propertyid)
         ->where('docid', $docid)
         ->where('cancelled', 'N')
         ->first();

      $data = [
         'einvoicebill' => $einvoicebill,
         'billno' => $billno,
         'paychargedata' => $paychargedata,
         'docid' => $docid,
         'sno1' => $sno1,
         'sumtyperev' => $sumtyperev,
         'companydata' => $companydata,
         'roomoccdata' => $roomoccdata,
         'billamt' => $billamt,
         'sumfieldc' => $sumfieldc,
         'onamt' => $onamt,
         'invoiceno' => $invoiceno,
         'paymodedata' => $paymodedata
      ];
      return json_encode($data);
   }

   public function fetchcompname(Request $request)
   {
      $settlemode = $request->input('settlemode');
      $settlefor = $request->input('settlefor');
      if ($settlefor == 'Company') {
         $data = \App\Helpers\MasterDataCache::corporates($this->propertyid);
      } else if ($settlefor == 'Travel Agent') {
         $data = \App\Helpers\MasterDataCache::travelAgents($this->propertyid);
      }
      return json_encode($data);
   }

   public function fetchpaydata(Request $request)
   {
      $cgst = 'CGSS' . $this->propertyid;
      $sgst = 'SGSS' . $this->propertyid;
      $roundoff = 'ROFF' . $this->propertyid;
      $disc = 'DISC' . $this->propertyid;
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');

      $allsettlement = $request->input('allsettlement');
      $allcompany = $request->input('allcompany');
      $alltravelagent = $request->input('alltravelagent');
      $allbusssource = $request->input('allbusssource');

      $settleyn = $request->input('settleyn');
      $companyyn = $request->input('companyyn');
      $travelyn = $request->input('travelyn');
      $bussyn = $request->input('bussyn');

      $seqrevcode = [];
      $revmast = Revmast::where('revmast.propertyid', $this->propertyid)
         ->where('field_type', 'C')
         ->where('Desk_code', '=', 'FOM' . $this->propertyid)
         ->whereNot('seq_no', '0')
         ->whereNotIn('revmast.rev_code', [$roundoff, $disc])
         ->distinct()
         ->orderBy('seq_no', 'ASC')
         ->get();

      $skipcode = [$roundoff, $disc];

      foreach ($revmast as $row) {
         $seqrevcode[] = $row->rev_code;
      }

      $roomoccJoinExpr = "CASE 
            WHEN paycharge.msno1 = 0 THEN paycharge.sno1 
            ELSE paycharge.msno1 
         END";

      $effectiveSnoExpr = "CASE 
            WHEN roomocc.leaderyn = 'Y' THEN CASE 
               WHEN paycharge.msno1 = 0 THEN paycharge.sno1 
               ELSE paycharge.msno1 
            END
            ELSE paycharge.sno1
         END";

      $selectFields = [
         'paycharge.folionodocid',
         DB::raw("{$effectiveSnoExpr} AS effective_sno"),
         DB::raw("SUM(CASE WHEN paycharge.sno IS NOT NULL and roomocc.type = 'O' THEN paycharge.amtdr ELSE 0 END) AS billamt"),
         DB::raw("SUM(CASE WHEN paycharge.sno = 1 and roomocc.type = 'O' THEN paycharge.amtdr ELSE 0 END) AS goods1"),
         DB::raw("SUM(CASE WHEN paycharge.paycode = '{$cgst}' and roomocc.type = 'O' THEN paycharge.amtdr - paycharge.amtcr ELSE 0 END) AS cgstsum"),
         DB::raw("SUM(CASE WHEN paycharge.paycode = '{$sgst}' and roomocc.type = 'O' THEN paycharge.amtdr - paycharge.amtcr ELSE 0 END) AS sgstsum"),
         DB::raw("SUM(CASE WHEN paycharge.paycode = '{$roundoff}' and roomocc.type = 'O' THEN paycharge.amtdr - paycharge.amtcr ELSE 0 END) AS roundoff"),
         DB::raw("SUM(CASE WHEN paycharge.paycode = '{$disc}' and roomocc.type = 'O' THEN paycharge.amtcr ELSE 0 END) AS discount"),
      ];

      $dynamicAliases = [];

      foreach ($seqrevcode as $code) {
         // $alias = "sum_" . strtolower($code);
         $alias = "sum_" . preg_replace('/[^a-z0-9_]/i', '_', strtolower($code));
         $selectFields[] = DB::raw("SUM(CASE WHEN paycharge.paycode = '{$code}' and roomocc.type = 'O' THEN paycharge.amtdr ELSE 0 END) AS {$alias}");
         $dynamicAliases[] = $alias;
      }

      $mainQuery = DB::table('paycharge')
         ->select([
            DB::raw("{$effectiveSnoExpr} AS effective_sno"),
            DB::raw("{$effectiveSnoExpr} AS sno1"),
            'paycharge.settledate',
            'paycharge.vprefix',
            'guestfolio.name as guestname',
            'guestfolio.vdate as checkindate',
            'roomocc.chkintime as checkintime',
            DB::raw("COALESCE(roomocc.chkoutdate, '') as chkoutdate"),
            'roomocc.leaderyn as leaderyn',
            'roomocc.chkouttime as chkouttime',
            'roomocc.docid as roomdocid',
            'roomocc.sno1 as rocc1',
            'guestfolio.busssource as bcode',
            'busssource.name as busssource',
            'guestprof.mobile_no as mobile_no',
            'guestfolio.company as compcode',
            'guestfolio.docid as folionodocid',
            'guestfolio.travelagent as travelcode',
            'paycharge.foliono',
            'fombilldetails.billno',
            'paycharge.docid',
            'roomocc.roomno as roomno',
            DB::raw('(roomocc.adult + roomocc.children) AS occ'),
            'roomocc.nodays as nights',
            'subcom.name as company',
            'subcom.gstin as compgstin',
            'travelcom.name as travelcompany',
            'travelcom.gstin as travelgstin',
            'booking.DocId as bookingdocid',
            'booking.BookNo AS bookno',
            'booking.RefBookNo AS refbookingid'
         ])
         ->leftJoin('roomocc', function ($join) use ($roomoccJoinExpr) {
            $join->on('roomocc.docid', '=', 'paycharge.folionodocid')
               ->on('roomocc.sno1', '=', DB::raw($roomoccJoinExpr));
         })
         ->leftJoin('fombilldetails', function ($join) use ($effectiveSnoExpr) {
            $join->on('fombilldetails.folionodocid', '=', 'paycharge.folionodocid')
               ->on('fombilldetails.sno1', '=', DB::raw($effectiveSnoExpr))
               ->where('fombilldetails.status', '=', 'settle');
         })
         ->leftJoin('guestfolio', 'paycharge.folionodocid', '=', 'guestfolio.docid')
         ->leftJoin('guestprof', 'roomocc.guestprof', '=', 'guestprof.guestcode')
         ->leftJoin('subgroup AS subcom', 'guestfolio.company', '=', 'subcom.sub_code')
         ->leftJoin('subgroup AS travelcom', 'guestfolio.travelagent', '=', 'travelcom.sub_code')
         ->leftJoin('busssource', 'busssource.bcode', '=', 'guestfolio.busssource')
         ->leftJoin('booking', 'booking.DocId', '=', 'guestfolio.bookingdocid')
         ->where('paycharge.propertyid', $this->propertyid)
         ->whereBetween('paycharge.settledate', [$fromdate, $todate])
         ->where('paycharge.roomtype', 'RO')
         ->where('paycharge.foliono', '!=', 0)
         ->where('roomocc.type', 'O');

      if ($companyyn == 'Y') {
         $mainQuery->whereIn('guestfolio.company', explode(',', $allcompany));
      }

      if ($travelyn == 'Y') {
         $mainQuery->whereIn('guestfolio.travelagent', explode(',', $alltravelagent));
      }

      if ($bussyn == 'Y') {
         $mainQuery->whereIn('guestfolio.busssource', explode(',', $allbusssource));
      }

      if (strtolower($settleyn) == 'y') {
         $mainQuery->whereIn('paycharge.paytype', explode(',', strtolower($allsettlement)));
      }

      $mainQuery->groupBy(
         'paycharge.folionodocid',
         DB::raw($effectiveSnoExpr),
         'fombilldetails.billno'
      )->orderBy('fombilldetails.billno')
         ->orderBy('paycharge.settledate');

      $cgstQuery = DB::table('paycharge')
         ->leftJoin('roomocc', function ($join) use ($roomoccJoinExpr) {
            $join->on('roomocc.docid', '=', 'paycharge.folionodocid')
               ->on('roomocc.sno1', '=', DB::raw($roomoccJoinExpr));
         })
         ->select($selectFields)
         ->where('paycharge.propertyid', $this->propertyid)
         ->whereBetween('paycharge.settledate', [$fromdate, $todate])
         ->groupBy('paycharge.folionodocid', DB::raw($effectiveSnoExpr));

      $resultQuery = DB::table(DB::raw("({$mainQuery->toSql()}) AS main_query"))
         ->mergeBindings($mainQuery)
         ->leftJoin(DB::raw("({$cgstQuery->toSql()}) AS cgst"), function ($join) {
            $join->on('main_query.folionodocid', '=', 'cgst.folionodocid')
               ->on('main_query.effective_sno', '=', 'cgst.effective_sno');
         })
         ->mergeBindings($cgstQuery)
         ->select([
            'main_query.sno1',
            'main_query.rocc1',
            'main_query.settledate',
            'main_query.guestname',
            'main_query.leaderyn',
            'main_query.checkindate',
            'main_query.checkintime',
            'main_query.roomdocid',
            'main_query.chkoutdate',
            'main_query.chkouttime',
            'main_query.mobile_no',
            'main_query.foliono',
            'main_query.billno',
            'main_query.folionodocid',
            'main_query.bookingdocid',
            'main_query.roomno',
            'main_query.occ',
            'main_query.nights',
            'main_query.vprefix',
            DB::raw('IFNULL(cgst.goods1, 0) AS goods1'),
            DB::raw('IFNULL(cgst.cgstsum, 0) AS cgstsum'),
            DB::raw('IFNULL(cgst.sgstsum, 0) AS sgstsum'),
            DB::raw('IFNULL(cgst.roundoff, 0) AS roundoff'),
            DB::raw('IFNULL(cgst.discount, 0) AS discount'),
            DB::raw('(IFNULL(cgst.cgstsum, 0) + IFNULL(cgst.sgstsum, 0)) AS total_tax'),
            DB::raw('IFNULL(cgst.billamt, 0) AS billamt'),
            'main_query.company',
            'main_query.compgstin',
            'main_query.travelcompany',
            'main_query.travelgstin',
            'main_query.compcode',
            'main_query.travelcode',
            'main_query.bookno',
            'main_query.refbookingid',
            'main_query.busssource',
            'main_query.bcode'
         ]);

      foreach ($dynamicAliases as $alias) {
         $resultQuery->addSelect(DB::raw("IFNULL(cgst.{$alias}, 0) AS {$alias}"));
      }

      $resulttmp = $resultQuery->get();

      if ($resulttmp->isEmpty()) {
         return json_encode([
            'skipcode' => $skipcode,
            'report' => [],
            'revmast' => $revmast,
            'resultQuery' => []
         ]);
      }

      $roomDocIds = $resulttmp->pluck('roomdocid')->filter()->unique()->values()->toArray();

      $bulkPaymentQuery = DB::table('paycharge')
         ->leftJoin('revmast', function ($join) {
            $join->on('revmast.rev_code', '=', 'paycharge.paycode')
               ->where('revmast.field_type', '=', 'P');
         })
         ->whereIn('paycharge.folionodocid', $roomDocIds)
         ->where('modeset', 'S')
         ->where('paycharge.paycode', '!=', 'ROFF' . $this->propertyid)
         ->select([
            'paycharge.folionodocid',
            'paycharge.sno1',
            'paycharge.paytype',
            DB::raw('SUM(paycharge.amtcr) AS totalamt'),
            DB::raw('MAX(paycharge.u_name) AS u_name')
         ])
         ->groupBy('paycharge.folionodocid', 'paycharge.sno1', 'paycharge.paytype')
         ->havingRaw('SUM(paycharge.amtcr) > 0');


      if (strtolower($settleyn) == 'y') {
         $bulkPaymentQuery->whereIn('paycharge.paytype', explode(',', strtolower($allsettlement)));
      }

      $bulkPaymentData = $bulkPaymentQuery->get()
         ->groupBy(function ($item) {
            return $item->folionodocid . '_' . $item->sno1;
         });

      $bulkAdvanceData = Paycharge::whereIn('paycharge.folionodocid', $roomDocIds)
         ->where('paycharge.propertyid', $this->propertyid)
         ->whereIn('vtype', ['REC', 'CHK'])
         ->whereNull('modeset')
         ->select([
            'folionodocid',
            'sno1',
            DB::raw('SUM(paycharge.amtcr) AS advance_sum'),
            'paycharge.paytype'
         ])
         ->groupBy('folionodocid', 'sno1', 'paycharge.paytype')
         ->get()
         ->groupBy(function ($item) {
            return $item->folionodocid . '_' . $item->sno1;
         });

      $result = [];

      foreach ($resulttmp as $row) {
         $paymentkey = $row->roomdocid . '_' . $row->rocc1;
         $paymentDataForRoom = $bulkPaymentData->get($paymentkey, collect());

         $paytypeStr = $paymentDataForRoom->pluck('paytype')->implode(', ');
         $paymentStr = $paymentDataForRoom->pluck('totalamt')->implode(', ');

         if (strtolower($settleyn) == 'y' && empty(trim($paytypeStr))) {
            continue;
         }

         $advanceKey = $row->roomdocid . '_' . $row->rocc1;
         $advanceDataForRoom = $bulkAdvanceData->get($advanceKey, collect());

         $advancePaytypeStr = $advanceDataForRoom->pluck('paytype')->implode(', ');
         $advanceSumStr = $advanceDataForRoom->pluck('advance_sum')->implode(', ');

         $row->paytype = $paytypeStr;
         $row->payment = $paymentStr;
         $row->advancepaytype = $advancePaytypeStr;
         $row->advance = $advanceSumStr;
         $row->u_name = $paymentDataForRoom->pluck('u_name')->first();

         $result[] = $row;
      }

      $data = [
         'skipcode' => $skipcode,
         'report' => $result,
         'revmast' => $revmast
      ];

      return json_encode($data);
   }

   public function fetchguesttraildata(Request $request)
   {
      $cgst = 'CGSS' . $this->propertyid;
      $sgst = 'SGSS' . $this->propertyid;
      $roundoff = 'ROFF' . $this->propertyid;
      $disc = 'DISC' . $this->propertyid;
      $fromdate = $request->input('fromdate');


      $seqrevcode = [];
      $revmast = Revmast::where('revmast.propertyid', $this->propertyid)
         ->where('field_type', 'C')
         ->where('Desk_code', '=', 'FOM' . $this->propertyid)
         ->whereNot('seq_no', '0')
         ->whereNotIn('revmast.rev_code', [$roundoff, $disc])
         ->distinct()
         ->orderBy('seq_no', 'ASC')
         ->get();

      $skipcode = [$roundoff, $disc];

      foreach ($revmast as $row) {
         $seqrevcode[] = $row->rev_code;
      }

      $selectFields = [
         'folionodocid',
         'sno1 as paysno1',
         DB::raw("SUM(CASE WHEN paycharge.sno IS NOT NULL THEN paycharge.amtdr ELSE 0 END) AS billamt"),
         DB::raw("SUM(CASE WHEN paycharge.sno = 1 THEN paycharge.amtdr ELSE 0 END) AS goods1"),
         DB::raw("SUM(CASE WHEN paycode = '{$cgst}' THEN amtdr - amtcr ELSE 0 END) AS cgstsum"),
         DB::raw("SUM(CASE WHEN paycode = '{$sgst}' THEN amtdr - amtcr ELSE 0 END) AS sgstsum"),
         DB::raw("SUM(CASE WHEN paycode = '{$roundoff}' THEN amtdr - amtcr ELSE 0 END) AS roundoff"),
         DB::raw("SUM(CASE WHEN paycode = '{$disc}' THEN amtcr ELSE 0 END) AS discount")
      ];

      $dynamicAliases = [];

      // foreach ($seqrevcode as $code) {
      //    $alias = "sum_" . strtolower(substr($code, 0, 3));
      //    $selectFields[] = DB::raw("SUM(CASE WHEN paycode = '{$code}' THEN amtdr ELSE 0 END) AS {$alias}");
      //    $dynamicAliases[] = $alias;
      // }

      foreach ($seqrevcode as $code) {
         $alias = "sum_" . strtolower($code);
         $selectFields[] = DB::raw("SUM(CASE WHEN paycharge.paycode = '{$code}' and roomocc.type = 'O' THEN paycharge.amtdr ELSE 0 END) AS {$alias}");
         $dynamicAliases[] = $alias;
      }

      $mainQuery = DB::table('roomocc')
         ->select([
            'roomocc.sno1',
            'paycharge.settledate',
            'paycharge.vprefix',
            'roomocc.name as guestname',
            'roomocc.roomrate as tarrif',
            'roomocc.chkindate as checkindate',
            'roomocc.chkintime as checkintime',
            DB::raw("COALESCE(roomocc.chkoutdate, '') as chkoutdate"),
            'roomocc.depdate',
            'roomocc.chkouttime as chkouttime',
            'roomocc.docid as roomdocid',
            'roomocc.sno1 as rocc1',
            'guestfolio.busssource as bcode',
            'busssource.name as busssource',
            'guestprof.mobile_no as mobile_no',
            'guestfolio.company as compcode',
            'guestfolio.travelagent as travelcode',
            'roomocc.folioNo as foliono',
            'paycharge.billno as paybillno',
            'paycharge.docid',
            'roomocc.roomno',
            DB::raw('(roomocc.adult + roomocc.children) AS occ'),
            'roomocc.nodays as nights',
            'subcom.name as company',
            'subcom.gstin as compgstin',
            'travelcom.name as travelcompany',
            'travelcom.gstin as travelgstin',
            'booking.DocId as bookingdocid',
            'booking.BookNo AS bookno',
            'booking.RefBookNo AS refbookingid',
            'plan_mast.Name as planname',
            'roomocc.planamt as planamt'
         ])
         ->leftJoin('plan_mast', 'roomocc.plancode', '=', 'plan_mast.pcode')
         ->leftJoin('paycharge', function ($join) {
            $join->on('paycharge.folionodocid', '=', 'roomocc.docid')
               ->where('paycharge.propertyid', '=', $this->propertyid)
               ->where('paycharge.roomtype', '=', 'RO');
         })
         ->leftJoin('guestfolio', function ($join) {
            $join->on('paycharge.folionodocid', '=', 'guestfolio.docid')
               ->on('guestfolio.sno1', '=', 'roomocc.sno1')
               ->where('guestfolio.propertyid', '=', $this->propertyid);
         })
         ->leftJoin('guestprof', function ($join) {
            $join->on('roomocc.guestprof', '=', 'guestprof.guestcode')
               ->on('guestprof.sno1', '=', 'roomocc.sno1')
               ->where('guestprof.propertyid', '=', $this->propertyid);
         })
         ->leftJoin('subgroup AS subcom', function ($join) {
            $join->on('guestfolio.company', '=', 'subcom.sub_code')
               ->where('subcom.propertyid', '=', $this->propertyid);
         })
         ->leftJoin('subgroup AS travelcom', function ($join) {
            $join->on('guestfolio.travelagent', '=', 'travelcom.sub_code')
               ->where('travelcom.propertyid', '=', $this->propertyid);
         })
         ->leftJoin('busssource', function ($join) {
            $join->on('busssource.bcode', '=', 'guestfolio.busssource')
               ->where('busssource.propertyid', '=', $this->propertyid);
         })
         ->leftJoin('booking', function ($join) {
            $join->on('booking.DocId', '=', 'guestfolio.bookingdocid')
               ->where('booking.Property_ID', '=', $this->propertyid);
         })
         ->where('roomocc.propertyid', $this->propertyid)
         ->where(function ($query) {
            $query->whereNull('roomocc.type')
               ->orWhere('roomocc.type', 'O');
         })
         ->whereDate('roomocc.chkindate', '<=', $fromdate)
         ->whereDate('roomocc.depdate', '>=', $fromdate);

      $mainQuery->groupBy('roomocc.docid', 'roomocc.sno1')
         ->orderBy('roomocc.sno1')
         ->orderBy('roomocc.chkindate');

      // return $mainQuery->get();

      $cgstQuery = DB::table('paycharge')
         ->select($selectFields)
         ->where('propertyid', $this->propertyid)
         ->where('vdate', $fromdate)
         ->whereIn('folionodocid', $mainQuery->pluck('roomdocid')->filter()->unique()->values()->toArray())
         ->whereIn('sno1', $mainQuery->pluck('rocc1')->filter()->unique()->values()->toArray())
         ->groupBy('folionodocid', 'sno1');

      $billnos = FomBillDetail::select('folionodocid', 'sno1', 'billno')
         ->where('propertyid', $this->propertyid)
         ->whereIn('folionodocid', $mainQuery->pluck('roomdocid')->filter()->unique()->values()->toArray())
         ->whereIn('sno1', $mainQuery->pluck('rocc1')->filter()->unique()->values()->toArray())
         ->where('status', 'settle');

      $resultQuery = DB::table(DB::raw("({$mainQuery->toSql()}) AS main_query"))
         ->mergeBindings($mainQuery)
         ->leftJoin(DB::raw("({$cgstQuery->toSql()}) AS cgst"), function ($join) {
            $join->on('main_query.roomdocid', '=', 'cgst.folionodocid')
               ->on('main_query.rocc1', '=', 'cgst.paysno1');
         })
         ->mergeBindings($cgstQuery)
         ->leftJoin(DB::raw("({$billnos->toSql()}) AS billdetails"), function ($join) {
            $join->on('main_query.roomdocid', '=', 'billdetails.folionodocid')
               ->on('main_query.rocc1', '=', 'billdetails.sno1');
         })
         ->mergeBindings($billnos->toBase())
         ->select([
            'main_query.sno1',
            'main_query.rocc1',
            'main_query.settledate',
            'main_query.guestname',
            'main_query.tarrif',
            'main_query.planname',
            'main_query.planamt',
            'main_query.checkindate',
            'main_query.checkintime',
            'main_query.roomdocid',
            'main_query.chkoutdate',
            'main_query.depdate',
            'main_query.chkouttime',
            'main_query.mobile_no',
            'main_query.foliono',
            'billdetails.billno as billno',
            'main_query.bookingdocid',
            'main_query.roomno',
            'main_query.occ',
            'main_query.nights',
            'main_query.vprefix',
            DB::raw('IFNULL(cgst.goods1, 0) AS goods1'),
            DB::raw('IFNULL(cgst.cgstsum, 0) AS cgstsum'),
            DB::raw('IFNULL(cgst.sgstsum, 0) AS sgstsum'),
            DB::raw('IFNULL(cgst.roundoff, 0) AS roundoff'),
            DB::raw('IFNULL(cgst.discount, 0) AS discount'),
            DB::raw('(IFNULL(cgst.cgstsum, 0) + IFNULL(cgst.sgstsum, 0)) AS total_tax'),
            DB::raw('IFNULL(cgst.billamt, 0) AS billamt'),
            'main_query.company',
            'main_query.compgstin',
            'main_query.travelcompany',
            'main_query.travelgstin',
            'main_query.compcode',
            'main_query.travelcode',
            'main_query.bookno',
            'main_query.refbookingid',
            'main_query.busssource',
            'main_query.bcode'
         ]);

      foreach ($dynamicAliases as $alias) {
         $resultQuery = $resultQuery->addSelect(DB::raw("IFNULL(cgst.{$alias}, 0) AS {$alias}"));
      }

      $resulttmp = $resultQuery->get();

      // return $resulttmp;

      if ($resulttmp->isEmpty()) {
         return json_encode([
            'skipcode' => $skipcode,
            'report' => [],
            'revmast' => $revmast,
            'resultQuery' => []
         ]);
      }

      $roomDocIds = $resulttmp->pluck('roomdocid')->filter()->unique()->values()->toArray();

      $bulkPaymentQuery = DB::table('paycharge')
         ->leftJoin('revmast', function ($join) {
            $join->on('revmast.rev_code', '=', 'paycharge.paycode')
               ->where('revmast.field_type', '=', 'P');
         })
         ->whereIn('paycharge.folionodocid', $roomDocIds)
         ->where('modeset', 'S')
         ->where('paycharge.paycode', '!=', 'ROFF' . $this->propertyid)
         ->select([
            'paycharge.folionodocid',
            'paycharge.paytype',
            DB::raw('SUM(paycharge.amtcr) AS totalamt')
         ])
         ->groupBy('paycharge.folionodocid', 'paycharge.paytype')
         ->havingRaw('SUM(paycharge.amtcr) > 0');

      $bulkPaymentData = $bulkPaymentQuery->get()->groupBy('folionodocid');

      $bulkAdvanceData = Paycharge::whereIn('paycharge.folionodocid', $roomDocIds)
         ->where('paycharge.propertyid', $this->propertyid)
         ->whereIn('vtype', ['REC', 'CHK'])
         ->whereNull('modeset')
         ->select([
            'folionodocid',
            'sno1',
            DB::raw('SUM(paycharge.amtcr) AS advance_sum')
         ])
         ->groupBy('folionodocid', 'sno1')
         ->get()
         ->keyBy(function ($item) {
            return $item->folionodocid . '_' . $item->sno1;
         });

      $result = [];

      foreach ($resulttmp as $row) {
         $paymentDataForRoom = $bulkPaymentData->get($row->roomdocid, collect());

         $paytypeStr = $paymentDataForRoom->pluck('paytype')->implode(', ');
         $paymentStr = $paymentDataForRoom->pluck('totalamt')->implode(', ');

         $advanceKey = $row->roomdocid . '_' . $row->rocc1;
         $advancesum = $bulkAdvanceData->get($advanceKey)->advance_sum ?? 0;

         $row->paytype = $paytypeStr;
         $row->payment = $paymentStr;
         $row->advance = $advancesum;

         $result[] = $row;
      }

      $data = [
         'skipcode' => $skipcode,
         'report' => $result,
         'revmast' => $revmast,
         'resultQuery' => $resultQuery->get()
      ];

      return json_encode($data);
   }

   public function guesttrail(Request $request)
   {
      $roundoff = 'ROFF' . $this->propertyid;
      $disc = 'DISC' . $this->propertyid;

      $revmast = Revmast::where('revmast.propertyid', $this->propertyid)
         ->where('field_type', 'C')
         ->where('Desk_code', '=', 'FOM' . $this->propertyid)
         ->whereNotIn('revmast.rev_code', [$roundoff, $disc])
         ->whereNot('seq_no', '0')
         ->distinct()
         ->orderBy('seq_no', 'ASC')
         ->get();
      return view('property.frontoffice.guesttrail', [
         'revmast' => $revmast
      ]);
   }

   public function billreprintsubmit(Request $request)
   {
      $permission = revokeopen(141115);
      if (empty($permission)) {
         return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
      }
      $validate = $request->validate([
         'billno' => 'required',
         'docid' => 'required',
         'folionodocid' => 'required',
         'sno1' => 'required',
      ]);

      $sno1 = $request->input('sno1');
      $folionodocid = $request->input('folionodocid');
      $count = $request->input('rowcount');
      $totalbalance = 0.00;
      $totalroomcharge = 0.00;
      $billprintingsummerised = $request->input('billprintingsummerised');
      $taxsummary = $request->input('taxsummary');
      $invoiceno = $request->input('invoiceno');

      for ($i = 1; $i <= $count; $i++) {
         $roomcharge = $request->input('room_charge_' . $i);
         $paydocid = $request->input('paydocid' . $i);
         $paysno = $request->input('paysno' . $i);
         $paysnoone = $request->input('paysnoone' . $i);
         if ($roomcharge !== null) {
            $updata = [
               'amtdr' => $request->input('room_charge_' . $i),
               'onamt' => $request->input('payonamt' . $i),
               'billamount' => $request->input('paybillamt' . $i),
               'u_updatedt' => $this->currenttime,
            ];
            // Log::info('Bill Reprint Update Data: ', $updata);

            Paycharge::where('propertyid', $this->propertyid)->where('docid', $paydocid)->where('sno', $paysno)
               ->where('sno1', $paysnoone)->update($updata);
         }
      }

      $company = Companyreg::where('propertyid', $this->propertyid)->where('role', 'Property')->first();

      $guest = RoomOcc::select('roomocc.*', 'guestprof.mobile_no', 'guestprof.guestsign')
         ->leftJoin('guestprof', function ($join) {
            $join->on('guestprof.guestcode', '=', 'roomocc.guestprof');
            // ->on('guestprof.sno1', '=', 'roomocc.sno1');
         })
         ->where('roomocc.propertyid', $this->propertyid)
         ->where('roomocc.docid', $folionodocid)
         ->where('roomocc.sno1', $sno1)
         ->first();

      // $paycharger = Paycharge::where('propertyid', $this->propertyid)->where('docid', $paydocid)->where('sno', $paysno)
      //    ->where('sno1', $paysnoone)->first();

      $paycharger = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $folionodocid)
         ->where('sno1', $sno1)->whereNot('billno', '0')->first();

      $chargedt = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $folionodocid)
         ->where('sno1', $sno1)->get();

      $paycode = ['RMCH' . $this->propertyid, 'MEGE' . $this->propertyid];
      foreach ($chargedt as $row) {
         $totalbalance += $row->amtdr;
      }

      $enviro = EnviroFom::where('propertyid', $this->propertyid)->first();
      $paycode = ['RMCH' . $this->propertyid, 'MEGE' . $this->propertyid];

      $igncode = [];
      $settlecodes = [];
      $revmasttax = Revmast::where('propertyid', $this->propertyid)->where('field_type', 'T')->where('type', 'Cr')->get();
      $revmastpay = Revmast::where('propertyid', $this->propertyid)->where('field_type', 'P')->where('type', 'Dr')->get();

      foreach ($revmasttax as $row) {
         $igncode[] = $row->rev_code;
      }

      foreach ($revmastpay as $row) {
         $settlecodes[] = $row->rev_code;
      }

      $charged = [];
      $rocc = RoomOcc::where('propertyid', $this->propertyid)->where('docid', $folionodocid)->where('leaderyn', 'Y')->first();
      if ($rocc) {
         $cond = ['paycharge.msno1' => $rocc->sno1];
      } else {
         $cond = ['paycharge.sno1' => $sno1];
      }
      if ($enviro->billprintingsummerised == 'Y') {
         $charged1 = Paycharge::select(
            'paycharge.sn',
            'paycharge.vdate',
            'paycharge.vtype',
            'paycharge.vno',
            'paycharge.comments',
            'paycharge.roomno',
            DB::raw("SUM(paycharge.amtdr) as amtdr"),
            DB::raw("SUM(paycharge.amtcr) as amtcr"),
            'plan_mast.name as plankanaam',
            'paycharge.split',
            'paycharge.paycode'
         )
            ->leftJoin('roomocc', function ($join) {
               $join->on('roomocc.docid', '=', 'paycharge.folionodocid')
                  ->on('roomocc.sno1', '=', 'paycharge.sno1')
                  ->where('roomocc.type', 'O')
                  ->where('roomocc.propertyid', $this->propertyid)
                  ->whereRaw("
                  roomocc.sno = (
                     SELECT MAX(ro.sno)
                     FROM roomocc ro
                     WHERE ro.docid = paycharge.folionodocid
                        AND ro.sno1 = paycharge.sno1
                        AND ro.type = 'O'
                        AND ro.propertyid = ?
                  )
            ", [$this->propertyid]);
            })
            ->leftJoin('plan_mast', function ($join) {
               $join->on('roomocc.plancode', '=', 'plan_mast.pcode')
                  ->where('plan_mast.propertyid', $this->propertyid);
            })
            ->where('paycharge.propertyid', $this->propertyid)
            ->where('paycharge.folionodocid', $folionodocid)
            ->whereNull('paycharge.modeset')
            ->where($cond)
            ->whereIn('paycharge.paycode', $paycode)
            ->groupBy('paycharge.roomno', 'paycharge.vdate')
            ->orderBy('paycharge.vdate', 'ASC')
            ->orderBy('paycharge.roomno', 'ASC')
            ->get();

         Log::info('charged1sum: ' . $charged1->sum('amtdr'));

         foreach ($charged1 as $row) {
            $totalroomcharge += $row->amtdr;
            $charged[] = [
               'sn' => $row->sn,
               'vdate' => $row->vdate,
               'vtype' => $row->vtype,
               'vno' => $row->vno,
               'comments' => $row->plankanaam . ' For Room ' . $row->roomno,
               'amtdr' => $row->amtdr,
               'amtcr' => $row->amtcr,
               'split' => $row->split,
               'paycode' => $row->paycode
            ];
         }

         $charged2 = Paycharge::select(
            'sn',
            'vdate',
            'vtype',
            'vno',
            'comments',
            'amtdr',
            'amtcr',
            'split',
            'paycode'
         )
            ->where('propertyid', $this->propertyid)
            ->where('folionodocid', $folionodocid)
            ->where($cond)
            ->whereNotIn('paycharge.paycode', $paycode)
            ->whereNot('paycharge.paycode', 'ROFF' . $this->propertyid)
            ->whereNull('paycharge.modeset')
            ->whereNotIn('paycharge.paycode', $igncode)
            ->orderBy('paycharge.vdate', 'ASC')
            ->orderBy('paycharge.roomno', 'ASC')
            ->get();

         foreach ($charged2 as $row2) {
            $totalroomcharge += $row2->amtdr;
            $charged[] = [
               'vdate' => $row2->vdate,
               'vtype' => $row2->vtype,
               'vno' => $row2->vno,
               'comments' => $row2->comments,
               'amtdr' => $row2->amtdr,
               'amtcr' => $row2->amtcr,
               'split' => $row2->split,
               'paycode' => $row2->paycode
            ];
         }
      } else {
         $charged = Paycharge::select(
            'vdate',
            'vtype',
            'vno',
            'comments',
            'amtdr',
            'amtcr',
            'split',
            'paycode'
         )
            ->where('propertyid', $this->propertyid)
            ->where('folionodocid', $folionodocid)
            ->whereNot('paycode', 'ROFF' . $this->propertyid)
            ->whereNull('paycharge.modeset')
            ->where($cond)
            ->orderBy('paycharge.vdate', 'ASC')
            ->orderBy('paycharge.roomno', 'ASC')
            ->get();

         $totalroomcharge = $charged->sum('amtdr');
      }

      return response()->json([
         'company' => $company,
         'guest' => $guest,
         'paycharger' => $paycharger,
         'totalbalance' => $totalbalance,
         'totalroomcharge' => $totalroomcharge,
         'billprintingsummerised' => $billprintingsummerised,
         'charged' => $charged,
         'taxsummary' => $taxsummary,
         'invoiceno' => $invoiceno,
         'igncode' => $igncode
      ]);
   }

   public function checkinreg(Request $request)
   {
      $permission = revokeopen(141211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;

      $data = DB::table('guestfolio')->where('propertyid', $this->propertyid)->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.checkinreg', [
         'data' => $data,
         'fromdate' => $fromdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }

   public function fetchcheckinregdata(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');

      // $checkval = Companyreg::where('propertyid', $this->propertyid)->first();
      // if ($fromdate < $checkval->start_dt) {
      //    return json_encode('1');
      // } else if ($todate > $checkval->end_dt) {
      //    return json_encode('2');
      // }
      $guestfolioData = Guestfolio::select(
         'guestfolio.Docid AS FolionoDocid',
         'guestfolio.folio_no',
         DB::raw('CASE WHEN guestfolio.mFoliono = 0 THEN guestfolio.folio_no ELSE guestfolio.mFoliono END AS FolioNo'),
         'guestprof.Name',
         'guestfolio.add1',
         'guestfolio.add2',
         'city_live.cityname AS city',
         'guestprof.nationality',
         'guestprof.mobile_no',
         'roomocc.RoomNo',
         DB::raw('IFNULL(roomocc.adult + roomocc.children, 0) AS TotalGuest'),
         'roomocc.RoomRate',
         'roomocc.planamt',
         'roomocc.ChkinDate',
         'roomocc.ChkinTime',
         'roomocc.chkoutdate',
         'roomocc.chkouttime',
         'city_from.cityname AS arrfrom',
         'city_to.cityname AS destination',
         'guestfolio.PurVisit',
         DB::raw('(SELECT SUM(paycharge.amtcr) FROM paycharge WHERE paycharge.folionodocid = guestfolio.Docid AND paycharge.modeset != "S") AS advance'),
         'guestfolio.U_Name',
         'subgroup.name AS travelagent'
      )
         ->join('roomocc', 'roomocc.Docid', '=', 'guestfolio.Docid')
         ->join('guestprof', 'guestprof.guestcode', '=', 'guestfolio.guestprof')
         ->leftJoin('countries', 'guestprof.Nationality', '=', 'countries.country_code')
         ->leftJoin('cities AS city_live', 'city_live.city_code', '=', 'guestfolio.city')
         ->leftJoin('cities AS city_from', 'city_from.city_code', '=', 'guestfolio.arrfrom')
         ->leftJoin('cities AS city_to', 'city_to.city_code', '=', 'guestfolio.destination')
         ->leftjoin('subgroup', 'subgroup.sub_code', '=', 'guestfolio.travelagent')
         ->whereBetween('guestfolio.vdate', [$fromdate, $todate])
         // ->where('roomocc.Sno', 1)
         ->where('guestfolio.propertyid', $this->propertyid)
         ->groupBy('roomocc.docid', 'roomocc.sno1')
         ->orderBy('roomocc.foliono', 'DESC')
         ->get();

      return json_encode($guestfolioData);
   }

   public function cashierreport(Request $request)
   {
      $permission = revokeopen(141213);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      $revheading = Revmast::where('propertyid', $this->propertyid)
         ->where('field_type', 'P')->get();
      $distinctuname = Paycharge::where('propertyid', $this->propertyid)->where('modeset', 'S')->distinct('u_name')->get(['u_name']);
      return view('property.cashierreport', [
         'fromdate' => $fromdate,
         'statename' => $statename,
         'distinctuname' => $distinctuname,
         'company' => $company,
         'revheading' => $revheading
      ]);
   }

   public function fetchusersname(Request $request)
   {
      $distinctuname = Paycharge::where('propertyid', $this->propertyid)->where('modeset', 'S')->distinct('u_name')->get(['u_name']);
      return json_encode($distinctuname);
   }

   public function fetchcashierreportdata(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $revheadingArray = [];
      $revheading = Revmast::where('propertyid', $this->propertyid)->where('field_type', 'P')->get();
      foreach ($revheading as $row) {
         $revheadingArray[] = $row->pay_type;
      }

      $firstQuery = DB::table('paycharge as PC')
         ->leftJoin('revmast as PY', 'PC.PayCode', '=', 'PY.rev_code')
         ->leftJoin('roomocc as RO', 'PC.FOLIONODOCID', '=', 'RO.DOCID')
         ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'PC.comp_code')
         ->select(
            'PC.DOCID',
            'PC.SNO',
            'PC.SNO1',
            DB::raw('MAX(PC.FOLIONO) AS FOLIONO'),
            'PC.FOLIONODOCID',
            'RO.roomno',
            'RO.name as GUESTNAME',
            DB::raw('MAX(PC.VDATE) AS VDATE'),
            DB::raw('MAX(PC.VTYPE) AS VTYPE'),
            DB::raw('MAX(PC.VNO) AS VNO'),
            DB::raw('PC.AmtCr - PC.AmtDr AS NetSale'),
            DB::raw('SUM(PC.TipAmt) AS TipAmt1'),
            DB::raw('MAX(PC.PAYCODE) AS PAYCODE'),
            DB::raw('MAX(PC.PayType) AS PType'),
            DB::raw('MAX(PC.U_NAME) AS UNAME'),
            DB::raw('MAX(PC.Comments) AS COMMENT'),
            DB::raw('MAX(PC.comp_code) AS comp_code'),
            DB::raw('MAX(SG.name) AS paycompanyname'),
            DB::raw("'PAYMENT RECD.' AS DEPARTNAME"),
            DB::raw('1 AS AA')
         )
         ->where(function ($query) {
            $query->where(function ($query) {
               $query->whereIn('PC.VTYPE', ['ARRES', 'ADRES'])
                  ->where('PC.DbtChkIn', '<>', 'Yes');
            })
               ->orWhere(function ($query) {
                  $query->whereNotIn('PC.VTYPE', ['ARRES', 'ADRES'])
                     ->where(function ($query) {
                        $query->whereNull('PC.refdocid')
                           ->orWhere('PC.refdocid', '=', '');
                     });
               });
         })
         ->where('PC.RESTCODE', 'FOM' . $this->propertyid)
         ->whereIn('PY.field_type', ['P'])
         ->whereNotIn('PC.VTYPE', ['CHK'])
         ->whereBetween('PC.VDate', [$fromdate, $todate])
         ->where('PC.propertyid', $this->propertyid)
         ->whereIn('PC.PAYTYPE', $revheadingArray)
         ->groupBy('PC.DOCID', 'PC.SNO', 'PC.SNO1')
         ->havingRaw('SUM(PC.AmtCr) - SUM(PC.AmtDr) > 0');

      $secondQuery = DB::table('paycharge as PC')
         ->leftJoin('revmast as PY', 'PC.PayCode', '=', 'PY.rev_code')
         ->leftJoin('roomocc as RO', 'PC.FOLIONODOCID', '=', 'RO.DOCID')
         ->leftJoin('subgroup as SG', 'SG.sub_code', '=', 'PC.comp_code')
         ->select(
            'PC.DOCID',
            'PC.SNO',
            'PC.SNO1',
            DB::raw('MAX(PC.FOLIONO) AS FOLIONO'),
            'PC.FOLIONODOCID',
            'RO.roomno',
            'RO.name as GUESTNAME',
            DB::raw('MAX(PC.VDATE) AS VDATE'),
            DB::raw('MAX(PC.VTYPE) AS VTYPE'),
            DB::raw('MAX(PC.VNO) AS VNO'),
            DB::raw('PC.AmtCr - PC.AmtDr AS NetSale'),
            DB::raw('SUM(PC.TipAmt) AS TipAmt1'),
            DB::raw('MAX(PC.PAYCODE) AS PAYCODE'),
            DB::raw('MAX(PC.PayType) AS PType'),
            DB::raw('MAX(PC.U_NAME) AS UNAME'),
            DB::raw('MAX(PC.Comments) AS COMMENT'),
            DB::raw('MAX(PC.comp_code) AS comp_code'),
            DB::raw('MAX(SG.name) AS paycompanyname'),
            DB::raw("'PAYMENT MADE' AS DEPARTNAME"),
            DB::raw('2 AS AA')
         )
         ->where(function ($query) {
            $query->where(function ($query) {
               $query->whereIn('PC.VTYPE', ['ARRES', 'ADRES'])
                  ->where('PC.DbtChkIn', '<>', 'Yes');
            })
               ->orWhere(function ($query) {
                  $query->whereNotIn('PC.VTYPE', ['ARRES', 'ADRES'])
                     ->where(function ($query) {
                        $query->whereNull('PC.refdocid')
                           ->orWhere('PC.refdocid', '=', '');
                     });
               });
         })
         ->where('PC.RESTCODE', 'FOM' . $this->propertyid)
         ->whereIn('PY.field_type', ['P'])
         ->whereNotIn('PC.VTYPE', ['CHK'])
         ->whereBetween('PC.VDate', [$fromdate, $todate])
         ->where('PC.propertyid', $this->propertyid)
         ->whereIn('PC.PAYTYPE', $revheadingArray)
         ->groupBy('PC.DOCID', 'PC.SNO', 'PC.SNO1')
         ->havingRaw('SUM(PC.AmtCr) - SUM(PC.AmtDr) < 0');

      $thirdQuery = DB::table('expsheet as E')
         ->select(
            'E.docid',
            DB::raw('1 AS SNO'),
            DB::raw('2 AS SNO1'),
            DB::raw('"" AS FOLIONO'),
            DB::raw('"" AS FOLIONODOCID'),
            DB::raw('"" AS roomno'),
            DB::raw('"" AS GUESTNAME'),
            'E.vdate',
            'E.vtype',
            'E.vno',
            DB::raw('E.cramt AS NetSale'),
            DB::raw('0 AS TipAmt1'),
            DB::raw('"" AS PAYCODE'),
            DB::raw("'Cash' AS PType"),
            'E.u_name AS UNAME',
            'E.remark AS COMMENT',
            DB::raw('"" AS comp_code'),
            DB::raw('"" AS paycompanyname'),
            DB::raw("'MISC.PAYMENT' AS DEPARTNAME"),
            DB::raw('3 AS AA')
         )
         ->whereBetween('E.vdate', [$fromdate, $todate])
         ->where('E.vtype', 'HTEXP')
         ->where('E.propertyid', $this->propertyid)
         ->where('E.cramt', '>', 0);

      $fourthQuery = DB::table('expsheet as E')
         ->select(
            'E.docid',
            DB::raw('1 AS SNO'),
            DB::raw('2 AS SNO1'),
            DB::raw('"" AS FOLIONO'),
            DB::raw('"" AS FOLIONODOCID'),
            DB::raw('"" AS roomno'),
            DB::raw('"" AS GUESTNAME'),
            'E.vdate',
            'E.vtype',
            'E.vno',
            'E.cramt AS NetSale',
            DB::raw('0 AS TipAmt1'),
            DB::raw('"" AS PAYCODE'),
            DB::raw("'Cash' AS PType"),
            'E.u_name AS UNAME',
            'E.remark AS COMMENT',
            DB::raw('"" AS comp_code'),
            DB::raw('"" AS paycompanyname'),
            DB::raw("'MISC.RECEIPT' AS DEPARTNAME"),
            DB::raw('4 AS AA')
         )
         ->whereBetween('E.VDate', [$fromdate, $todate])
         ->where('E.vtype', 'HTSAL')
         ->where('E.propertyid', $this->propertyid)
         ->where('E.cramt', '>', 0);

      $results = $firstQuery->unionAll($secondQuery)
         ->unionAll($thirdQuery)
         ->unionAll($fourthQuery)
         ->orderBy('AA')
         ->orderBy('foliono')
         ->orderBy('VDATE')
         ->orderBy('DOCID')
         ->orderBy('SNO')
         ->get();

      $billnos = Paycharge::where('propertyid', $this->propertyid)
         ->where('billno', '!=', 0)
         ->whereNull('paytype')
         ->get()
         ->keyBy(function ($item) {
            return $item->folionodocid . '_' . $item->sno1;
         });

      $roomnos = RoomOcc::where('propertyid', $this->propertyid)
         ->whereNotNull('type')
         ->get()
         ->keyBy(function ($item) {
            return $item->docid . '_' . $item->sno1;
         });

      foreach ($results as $row) {
         $key = $row->FOLIONODOCID . '_' . $row->SNO1;
         if (isset($roomnos[$key])) {
            $row->roomno = $roomnos[$key]->roomno;
         }
      }

      foreach ($results as $row) {
         $key = $row->FOLIONODOCID . '_' . $row->SNO1;
         if (isset($billnos[$key])) {
            $row->billno = $billnos[$key]->billno;
         } else {
            $row->billno = 'Not Found';
         }
      }

      $paytype = [];
      $distinctpaytypes = Revmast::where('propertyid', $this->propertyid)->where('field_type', 'P')->get();
      foreach ($distinctpaytypes as $row) {
         $paytype[] = $row->pay_type;
      }

      $data = [
         'cashierdata' => $results,
         'paytype' => $paytype,
      ];

      return json_encode($data);
   }

   public function fetchcashierreportdata2(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $usernames = json_decode($request->input('unames'));
      // Making array of revheading names
      $revheadingArray = [];
      $revheading = Revmast::where('propertyid', $this->propertyid)->where('field_type', 'P')->get();
      foreach ($revheading as $row) {
         $revheadingArray[] = $row->pay_type;
      }

      $propertyId = $this->propertyid;

      $firstQuery = DB::table('paycharge as PC')
         ->leftJoin('revmast as PY', 'PC.PayCode', '=', 'PY.rev_code')
         ->leftJoin('roomocc as RO', 'PC.FOLIONODOCID', '=', 'RO.DOCID')
         ->select(
            'PC.DOCID',
            'PC.SNO',
            'PC.SNO1',
            DB::raw('MAX(PC.FOLIONO) AS FOLIONO'),
            'PC.FOLIONODOCID',
            'RO.roomno',
            'RO.name as GUESTNAME',
            DB::raw('MAX(PC.VDATE) AS VDATE'),
            DB::raw('MAX(PC.VTYPE) AS VTYPE'),
            DB::raw('MAX(PC.VNO) AS VNO'),
            DB::raw('PC.AmtCr - PC.AmtDr AS NetSale'),
            DB::raw('SUM(PC.TipAmt) AS TipAmt1'),
            DB::raw('MAX(PC.PAYCODE) AS PAYCODE'),
            DB::raw('MAX(PC.PayType) AS PType'),
            DB::raw('MAX(PC.U_NAME) AS UNAME'),
            DB::raw('MAX(PC.Comments) AS COMMENT'),
            DB::raw("'PAYMENT RECD.' AS DEPARTNAME"),
            DB::raw('1 AS AA')
         )
         ->where(function ($query) {
            $query->where(function ($query) {
               $query->whereIn('PC.VTYPE', ['ARRES', 'ADRES'])
                  ->where('PC.DbtChkIn', '<>', 'Yes');
            })
               ->orWhere(function ($query) {
                  $query->whereNotIn('PC.VTYPE', ['ARRES', 'ADRES'])
                     ->where(function ($query) {
                        $query->whereNull('PC.refdocid')
                           ->orWhere('PC.refdocid', '=', '');
                     });
               });
         })
         ->where('PC.RESTCODE', 'FOM' . $this->propertyid)
         ->whereIn('PY.field_type', ['P'])
         ->whereIn('PC.u_name', $usernames)
         ->whereNotIn('PC.VTYPE', ['CHK'])
         ->whereBetween('PC.VDate', [$fromdate, $todate])
         ->where('PC.propertyid', $this->propertyid)
         ->whereIn('PC.PAYTYPE', $revheadingArray)
         ->groupBy('PC.DOCID', 'PC.SNO', 'PC.SNO1')
         ->havingRaw('SUM(PC.AmtCr) - SUM(PC.AmtDr) > 0');

      $secondQuery = DB::table('paycharge as PC')
         ->leftJoin('revmast as PY', 'PC.PayCode', '=', 'PY.rev_code')
         ->leftJoin('roomocc as RO', 'PC.FOLIONODOCID', '=', 'RO.DOCID')
         ->select(
            'PC.DOCID',
            'PC.SNO',
            'PC.SNO1',
            DB::raw('MAX(PC.FOLIONO) AS FOLIONO'),
            'PC.FOLIONODOCID',
            'RO.roomno',
            'RO.name as GUESTNAME',
            DB::raw('MAX(PC.VDATE) AS VDATE'),
            DB::raw('MAX(PC.VTYPE) AS VTYPE'),
            DB::raw('MAX(PC.VNO) AS VNO'),
            DB::raw('PC.AmtCr - PC.AmtDr AS NetSale'),
            DB::raw('SUM(PC.TipAmt) AS TipAmt1'),
            DB::raw('MAX(PC.PAYCODE) AS PAYCODE'),
            DB::raw('MAX(PC.PayType) AS PType'),
            DB::raw('MAX(PC.U_NAME) AS UNAME'),
            DB::raw('MAX(PC.Comments) AS COMMENT'),
            DB::raw("'PAYMENT MADE' AS DEPARTNAME"),
            DB::raw('2 AS AA')
         )
         ->where(function ($query) {
            $query->where(function ($query) {
               $query->whereIn('PC.VTYPE', ['ARRES', 'ADRES'])
                  ->where('PC.DbtChkIn', '<>', 'Yes');
            })
               ->orWhere(function ($query) {
                  $query->whereNotIn('PC.VTYPE', ['ARRES', 'ADRES'])
                     ->where(function ($query) {
                        $query->whereNull('PC.refdocid')
                           ->orWhere('PC.refdocid', '=', '');
                     });
               });
         })
         ->where('PC.RESTCODE', 'FOM' . $this->propertyid)
         ->whereIn('PY.field_type', ['P'])
         ->whereIn('PC.u_name', $usernames)
         ->whereNotIn('PC.VTYPE', ['CHK'])
         ->whereBetween('PC.VDate', [$fromdate, $todate])
         ->where('PC.propertyid', $this->propertyid)
         ->whereIn('PC.PAYTYPE', $revheadingArray)
         ->groupBy('PC.DOCID', 'PC.SNO', 'PC.SNO1')
         ->havingRaw('SUM(PC.AmtCr) - SUM(PC.AmtDr) < 0');

      $thirdQuery = DB::table('expsheet as E')
         ->select(
            'E.DOCID',
            DB::raw('1 AS SNO'),
            DB::raw('2 AS SNO1'),
            DB::raw('"" AS FOLIONO'),
            DB::raw('"" AS FOLIONODOCID'),
            DB::raw('"" AS roomno'),
            DB::raw('"" AS GUESTNAME'),
            'E.VDATE',
            'E.VTYPE',
            'E.VNO',
            DB::raw('-E.Amount AS NetSale'),
            DB::raw('0 AS TipAmt1'),
            DB::raw('"" AS PAYCODE'),
            DB::raw("'Cash' AS PType"),
            'E.U_NAME AS UNAME',
            'E.Remarks AS COMMENT',
            DB::raw("'MISC.PAYMENT' AS DEPARTNAME"),
            DB::raw('3 AS AA')
         )
         ->whereBetween('E.VDate', [$fromdate, $todate])
         ->where('E.VTYPE', 'HTEXP')
         ->where('E.Amount', '>', 0);

      $fourthQuery = DB::table('expsheet as E')
         ->select(
            'E.DOCID',
            DB::raw('1 AS SNO'),
            DB::raw('2 AS SNO1'),
            DB::raw('"" AS FOLIONO'),
            DB::raw('"" AS FOLIONODOCID'),
            DB::raw('"" AS roomno'),
            DB::raw('"" AS GUESTNAME'),
            'E.VDATE',
            'E.VTYPE',
            'E.VNO',
            'E.Amount AS NetSale',
            DB::raw('0 AS TipAmt1'),
            DB::raw('"" AS PAYCODE'),
            DB::raw("'Cash' AS PType"),
            'E.U_NAME AS UNAME',
            'E.Remarks AS COMMENT',
            DB::raw("'MISC.RECEIPT' AS DEPARTNAME"),
            DB::raw('4 AS AA')
         )
         ->whereBetween('E.VDate', [$fromdate, $todate])
         ->where('E.VTYPE', 'HTSAL')
         ->where('E.Amount', '>', 0);

      $results = $firstQuery->unionAll($secondQuery)
         ->unionAll($thirdQuery)
         ->unionAll($fourthQuery)
         ->orderBy('AA')
         ->orderBy('DEPARTNAME')
         ->orderBy('VDATE')
         ->orderBy('DOCID')
         ->orderBy('SNO')
         ->get();

      $billnos = Paycharge::where('propertyid', $this->propertyid)
         ->where('billno', '!=', 0)
         ->whereNull('paytype')
         ->get()
         ->keyBy(function ($item) {
            return $item->folionodocid . '_' . $item->sno1;
         });

      foreach ($results as $row) {
         $key = $row->FOLIONODOCID . '_' . $row->SNO1;
         if (isset($billnos[$key])) {
            $row->billno = $billnos[$key]->billno;
         } else {
            $row->billno = 'Not Found';
         }
      }

      $paytype = [];
      $distinctpaytypes = Revmast::where('propertyid', $this->propertyid)->where('field_type', 'P')->get();
      foreach ($distinctpaytypes as $row) {
         $paytype[] = $row->pay_type;
      }
      $data = [
         'cashierdata' => $results,
         'paytype' => $paytype,
      ];

      return json_encode($data);
   }

   public function cancelbills(Request $request)
   {
      $permission = revokeopen(141214);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.cancelbills', [
         'fromdate' => $fromdate,
         'statename' => $statename,
         'company' => $company
      ]);
   }

   public function fetchcancelbilldata(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');

      $data = FomBillDetail::where('propertyid', $this->propertyid)->Where('status', 'Cancel')->whereBetween('billdate', [$fromdate, $todate])->orderBy('billdate')->orderBy('billno')->orderBy('u_name')->get();
      return json_encode($data);
   }

   public function fetchbussource(Request $request)
   {
      $bussdata = BussSource::where('propertyid', $this->propertyid)->get();
      return json_encode($bussdata);
   }

   public function fetchroomresettle(Request $request)
   {
      $billno = $request->input('billno');
      $vprefix = $request->vprefix;

      $chkbilltrue = Paycharge::where('propertyid', $this->propertyid)
         ->where('billno', $billno)
         ->where('vprefix', $vprefix)
         ->first();

      if (!$chkbilltrue) {
         return json_encode('Invalid');
      }


      $resDocid = $chkbilltrue->restcode;

      $records = DB::table('depart_pay')
         ->select(
            'revmast.name',
            'revmast.rev_code',
            'revmast.nature',
            'revmast.field_type',
            'revmast.flag_type',
            'depart_pay.pay_code'
         )
         ->leftJoin('revmast', 'depart_pay.pay_code', '=', 'revmast.rev_code')
         ->where('revmast.field_type', 'P')
         ->where('depart_pay.rest_code', $resDocid)
         ->where('depart_pay.propertyid', $this->propertyid)
         ->get();

      $paychargedata = DB::table('paycharge')
         ->where('propertyid', $this->propertyid)
         ->where('vprefix', $vprefix)
         ->where('billno', $billno)
         ->get();

      foreach ($paychargedata as $data) {
         $docid = $data->folionodocid;
         $sno1 = $data->sno1;
         $sno = $data->sno;
         $msno1 = $data->msno1;
      }

      $rocc = Roomocc::where('propertyid', $this->propertyid)->where('docid', $docid)->where('leaderyn', 'Y')->first();

      $paymodeQuery = Paycharge::select('paycharge.paycode', 'paycharge.vdate', 'paycharge.comp_code', 'revmast.pay_type')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)
         ->where('paycharge.folionodocid', $docid)
         ->where('paycharge.vprefix', $vprefix)
         ->where('paycharge.modeset', 'S')
         ->whereNot('paycharge.vtype', 'REV');

      if ($rocc) {
         $paymodeQuery->where('msno1', $rocc->sno1);
      } else {
         $paymodeQuery->where('sno1', $sno1);
      }

      $paymode = $paymodeQuery->get();

      $paymodedata = [];
      foreach ($paymode as $row) {
         $pay_type = $row->pay_type;
         $paydate = $row->vdate;
         $paydata = null;
         if ($pay_type == 'Company') {
            $paydata = SubGroup::where('propertyid', $this->propertyid)
               ->where('sub_code', $row->comp_code)
               ->first();
         }

         $paymodedata[] = [
            'pay_type' => $pay_type,
            'paydate' => $paydate,
            'paycompname' => ($paydata) ? $paydata->name : null
         ];
      }

      $companydata = DB::table('company')->where('propertyid', $this->propertyid)->first();
      $roomoccdata = RoomOcc::select(
         'roomocc.*',
         'cities.cityname',
         'states.name as statename',
         'room_cat.name as roomcategory',
         'company.name as companyname',
         'company.gstin as companygst',
         'travelagent.name as travelname',
         'travelagent.gstin as travelgst'
      )
         ->leftJoin('guestprof', 'guestprof.docid', '=', 'roomocc.docid')
         ->leftJoin('cities', 'cities.city_code', '=', 'guestprof.city')
         ->leftJoin('states', 'states.state_code', '=', 'guestprof.state_code')
         ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'roomocc.roomcat')
         ->leftJoin('guestfolio', 'guestfolio.docid', '=', 'roomocc.docid')
         ->leftJoin('subgroup as company', 'company.sub_code', '=', 'guestfolio.company')
         ->leftJoin('subgroup as travelagent', 'travelagent.sub_code', '=', 'guestfolio.travelagent')
         ->where('roomocc.propertyid', $this->propertyid)
         ->where('roomocc.docid', $docid)
         ->where('roomocc.sno1', $sno1)
         ->first();

      $qry1s = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $docid);
      $qry2s = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $docid)
         ->whereNull('modeset');
      $qry3s = Paycharge::select('paycharge.*', 'revmast.name as revname')->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)->where('paycharge.folionodocid', $docid)
         ->whereNotNull('paycharge.modeset')->whereNot('paycharge.amtcr', 0)->orderBy('paycharge.sno', 'ASC');
      if ($rocc) {
         $qry1s->where('msno1', $rocc->sno1);
         $qry2s->where('msno1', $rocc->sno1);
         $qry3s->where('paycharge.msno1', $rocc->sno1);
         $payd = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $docid)->where('msno1', $rocc->sno1)
            ->where('modeset', 'S')->first();
      } else {
         $qry1s->where('sno1', $sno1);
         $qry2s->where('sno1', $sno1);
         $qry3s->where('paycharge.sno1', $sno1);
         $payd = Paycharge::where('propertyid', $this->propertyid)->where('folionodocid', $docid)->where('sno1', $sno1)
            ->where('modeset', 'S')->first();
      }
      $qry1 = $qry1s->sum('amtdr');
      $qry2 = $qry2s->sum('amtcr');
      $qry3 = $qry3s->get();
      $totalamt = str_replace(',', '', number_format($qry1 - $qry2, 2));
      $data = [
         'roomoccdata' => $roomoccdata,
         'paymodedata' => $paymodedata,
         'billno' => $billno,
         'companydata' => $companydata,
         'totalamt' => $totalamt,
         'qry3' => $qry3,
         'sno1' => $sno1,
         'payd' => $payd,
         'paytype' => $records,
         'resDocid' => $resDocid

      ];

      return json_encode($data);
   }

   public function fomtaxdetail(Request $request)
   {
      $permission = revokeopen(141511);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;
      $taxnames = Paycharge::select('revmast.name', 'paycharge.paycode', 'paycharge.taxper')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)
         ->where('revmast.field_type', 'T')
         ->whereNotNull('paycharge.taxper')
         // ->whereBetween('paycharge.vdate', [$this->ncurdate, $this->ncurdate])
         ->groupBy('paycharge.paycode')
         ->get();
      $data = DB::table('guestfolio')->where('propertyid', $this->propertyid)->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.fomtaxdetail', [
         'data' => $data,
         'fromdate' => $fromdate,
         'company' => $company,
         'statename' => $statename,
         'taxnames' => $taxnames
      ]);
   }

   public function fetchfomtaxdata(Request $request)
   {
      $propertyid = $this->propertyid;
      $cgstCode = 'CGSS' . $propertyid;
      $sgstCode = 'SGSS' . $propertyid;
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');

      $taxes = $request->input('taxes', []);
      if (is_string($taxes)) {
         $decoded = json_decode($taxes, true);
         $taxes = is_array($decoded) ? $decoded : ($taxes === '' ? [] : [$taxes]);
      }
      $taxes = array_values(array_filter($taxes, fn($v) => $v !== 'All' && $v !== '' && $v !== null));

      $taxDefs = [
         1 => [6,   $cgstCode],
         2 => [9,   $cgstCode],
         3 => [6,   $sgstCode],
         4 => [9,   $sgstCode],
         5 => [2.5, $cgstCode],
         6 => [2.5, $sgstCode],
      ];

      $paychargeAgg = DB::table('paycharge')
         ->select('folionodocid')
         ->selectRaw('MAX(foliono) AS foliono')
         ->selectRaw('MAX(billno) AS billno')
         ->selectRaw('MAX(settledate) AS settledate')
         ->selectRaw('SUM(amtdr - amtcr) AS AmtDr')
         ->selectRaw('SUM(amtdr) AS billamount')
         ->where('roomtype', 'RO')
         ->whereRaw('amtdr - amtcr <> 0')
         ->whereNotIn('vtype', ['ARRES', 'ADRES'])
         ->where('foliono', '<>', 0)
         ->where(function ($q) {
            $q->where('billno', '<>', 0)->orWhereNull('modeset');
         })
         ->whereBetween('settledate', [$fromdate, $todate])
         ->where('propertyid', $propertyid)
         ->when(!empty($taxes), fn($q) => $q->whereIn('paycode', $taxes))
         ->groupBy('folionodocid', 'billno');

      foreach ($taxDefs as $i => [$taxper, $code]) {
         $paychargeAgg->selectRaw(
            "SUM(CASE WHEN taxper = ? AND paycode = ? THEN onamt ELSE 0 END) AS BASE{$i}",
            [$taxper, $code]
         );
         $paychargeAgg->selectRaw(
            "SUM(CASE WHEN taxper = ? AND paycode = ? THEN amtdr - amtcr ELSE 0 END) AS TAX{$i}",
            [$taxper, $code]
         );
      }

      $guestRoom = DB::table('roomocc AS ro')
         ->select('ro.docid AS folionodocid')
         ->selectRaw('MAX(ro.name) AS GuestName')
         ->selectRaw('MAX(s.name) AS company')
         ->selectRaw('MAX(s.gstin) AS gstin')
         ->selectRaw('GROUP_CONCAT(DISTINCT COALESCE(ro_leader.roomno, ro.roomno)) AS RoomNo')
         ->leftJoin('roomocc AS ro_leader', function ($join) {
            $join->on('ro_leader.docid', '=', 'ro.docid')
               ->whereRaw("ro_leader.type = 'O'")
               ->whereRaw("ro_leader.leaderyn = 'Y'");
         })
         ->leftJoin('guestfolio AS gf', 'gf.docid', '=', 'ro.docid')
         ->leftJoin('subgroup AS s', 'gf.company', '=', 's.sub_code')
         ->whereRaw("ro.type = 'O'")
         ->groupBy('ro.docid');

      // fromSub / joinSub let Laravel track bindings itself â€” no toSql()/mergeBindings surgery
      $results = DB::table(DB::raw('1 as dummy')) // placeholder, replaced by fromSub below
         ->fromSub($paychargeAgg, 'P')
         ->joinSub($guestRoom, 'G', 'P.folionodocid', '=', 'G.folionodocid')
         ->select(
            'P.foliono',
            'P.folionodocid',
            'P.settledate',
            'P.billno AS BILL_NO',
            'G.GuestName',
            'G.company AS companyname',
            'G.gstin AS companygstin',
            'G.RoomNo',
            'P.AmtDr',
            'P.BASE1',
            'P.TAX1',
            'P.BASE2',
            'P.TAX2',
            'P.BASE3',
            'P.TAX3',
            'P.BASE4',
            'P.TAX4',
            'P.BASE5',
            'P.TAX5',
            'P.BASE6',
            'P.TAX6',
            DB::raw('(P.BASE1+P.BASE2+P.BASE3+P.BASE4+P.BASE5+P.BASE6) AS EBASEVALUE'),
            DB::raw('(P.TAX1+P.TAX2+P.TAX3+P.TAX4+P.TAX5+P.TAX6) AS ETAXAMT'),
            'P.billamount'
         );

      // Wrap again as a sub-builder for filtering/count/paginate â€” bindings still auto-tracked
      $draw = intval($request->input('draw', 0));
      $start = intval($request->input('start', 0));
      $length = intval($request->input('length', 10));
      $searchValue = trim((string) $request->input('search.value', ''));

      $wrapped = DB::query()->fromSub($results, 'sub');

      $recordsTotal = (clone $wrapped)->count();

      if ($searchValue !== '') {
         $wrapped->where(function ($q) use ($searchValue) {
            $like = "%{$searchValue}%";
            $q->where('sub.GuestName', 'like', $like)
               ->orWhere('sub.BILL_NO', 'like', $like)
               ->orWhere('sub.companyname', 'like', $like)
               ->orWhere('sub.companygstin', 'like', $like)
               ->orWhere('sub.RoomNo', 'like', $like);
         });
      }

      $recordsFiltered = (clone $wrapped)->count();

      // whitelist order column + direction â€” never trust raw request input in ORDER BY
      $orderColIndex = intval($request->input('order.0.column', 0));
      $orderDir = strtolower($request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
      $columnsMap = [
         0 => 'settledate',
         1 => 'GuestName',
         2 => 'foliono',
         3 => 'BILL_NO',
         4 => 'RoomNo',
         5 => 'billamount',
         6 => 'BASE5',
         7 => 'BASE1',
         8 => 'BASE2',
         9 => 'TAX5',
         10 => 'TAX6',
         11 => 'TAX1',
         12 => 'TAX3',
         13 => 'TAX2',
         14 => 'TAX4',
         15 => 'ETAXAMT',
         16 => 'companyname',
         17 => 'companygstin',
      ];

      if (isset($columnsMap[$orderColIndex])) {
         $col = $columnsMap[$orderColIndex];
         if ($col === 'BILL_NO') {
            $wrapped->orderByRaw('CAST(sub.BILL_NO AS DECIMAL) ' . $orderDir);
         } else {
            $wrapped->orderBy('sub.' . $col, $orderDir);
         }
      }

      $limit = $length > 0 ? $length : 10;
      $data = $wrapped->skip($start)->take($limit)->get();

      $dataArr = $data->map(fn($row) => (array) $row)->all();
      $formatted = $this->formattedrow($dataArr);

      return response()->json([
         'draw' => $draw,
         'recordsTotal' => $recordsTotal,
         'recordsFiltered' => $recordsFiltered,
         'data' => $formatted,
         'fromdate_display' => $fromdate,
         'todate_display' => $todate,
      ]);
   }

   private function formattedrow($dataArr)
   {
      $formatted = array_map(function ($r) {
         $fmt = fn($v) => number_format((float)($v ?? 0), 2, '.', '');
         $taxPositive = fn($v) => ((float)($v ?? 0)) != 0.0;

         return [
            'foliono' => $r['foliono'] ?? null,
            'folionodocid' => $r['folionodocid'] ?? null,
            'settledate' => $r['settledate'] ?? null,
            'BILL_NO' => $r['BILL_NO'] ?? null,
            'GuestName' => $r['GuestName'] ?? '',
            'RevenueName' => $r['RevenueName'] ?? '',
            'RoomNo' => $r['RoomNo'] ?? '',
            'AmtDr' => $fmt($r['AmtDr'] ?? 0),

            'BASEVALUE1' => $fmt($r['BASE1'] ?? 0),
            'TAXAMT1' => $fmt($r['TAX1'] ?? 0),
            'TAXPER1' => $taxPositive($r['TAX1'] ?? 0) ? number_format(6, 2, '.', '') : number_format(0, 2, '.', ''),

            'BASEVALUE2' => $fmt($r['BASE2'] ?? 0),
            'TAXAMT2' => $fmt($r['TAX2'] ?? 0),
            'TAXPER2' => $taxPositive($r['TAX2'] ?? 0) ? number_format(9, 2, '.', '') : number_format(0, 2, '.', ''),

            'BASEVALUE3' => $fmt($r['BASE3'] ?? 0),
            'TAXAMT3' => $fmt($r['TAX3'] ?? 0),
            'TAXPER3' => $taxPositive($r['TAX3'] ?? 0) ? number_format(6, 2, '.', '') : number_format(0, 2, '.', ''),

            'BASEVALUE4' => $fmt($r['BASE4'] ?? 0),
            'TAXAMT4' => $fmt($r['TAX4'] ?? 0),
            'TAXPER4' => $taxPositive($r['TAX4'] ?? 0) ? number_format(9, 2, '.', '') : number_format(0, 2, '.', ''),

            'BASEVALUE5' => $fmt($r['BASE5'] ?? 0),
            'TAXAMT5' => $fmt($r['TAX5'] ?? 0),
            'TAXPER5' => $taxPositive($r['TAX5'] ?? 0) ? number_format(2.5, 2, '.', '') : number_format(0, 2, '.', ''),

            'BASEVALUE6' => $fmt($r['BASE6'] ?? 0),
            'TAXAMT6' => $fmt($r['TAX6'] ?? 0),
            'TAXPER6' => $taxPositive($r['TAX6'] ?? 0) ? number_format(2.5, 2, '.', '') : number_format(0, 2, '.', ''),

            'EBASEVALUE' => $fmt($r['EBASEVALUE'] ?? 0),
            'ETAXAMT' => $fmt($r['ETAXAMT'] ?? 0),
            'billamount' => $fmt($r['billamount'] ?? 0),

            'companyname' => $r['companyname'] ?? '',
            'companygstin' => $r['companygstin'] ?? ''
         ];
      }, $dataArr);
      return $formatted;
   }

   public function exportExcel(Request $request)
   {
      $fromdate = $request->query('fromdate');
      $todate = $request->query('todate');
      $taxes = $request->query('taxes', []);

      $filename = 'Fom_Tax_Report_' . date('Ymd_His') . '.xlsx';

      $spreadsheet = new Spreadsheet();
      $sheet = $spreadsheet->getActiveSheet();
   }

   public function exportFomTaxExcel(Request $request)
   {
      try {
         $propertyid = $this->propertyid;
         $cgstCode = 'CGSS' . $propertyid;
         $sgstCode = 'SGSS' . $propertyid;
         $fromdate = $request->query('fromdate');
         $todate = $request->query('todate');

         $taxes = $request->query('taxes', []);
         if (is_string($taxes)) {
            $decoded = json_decode($taxes, true);
            $taxes = is_array($decoded) ? $decoded : ($taxes === '' ? [] : [$taxes]);
         }
         $taxes = array_values(array_filter($taxes, fn($v) => $v !== 'All' && $v !== '' && $v !== null));

         $paychargeAgg = DB::table('paycharge')
            ->select(
               'folionodocid',
               DB::raw('MAX(foliono) AS foliono'),
               DB::raw('MAX(billno) AS billno'),
               DB::raw('MAX(settledate) AS settledate'),
               DB::raw('SUM(amtdr - amtcr) AS AmtDr'),
               DB::raw("SUM(CASE WHEN taxper=6 AND paycode='{$cgstCode}' THEN onamt ELSE 0 END) AS BASE1"),
               DB::raw("SUM(CASE WHEN taxper=6 AND paycode='{$cgstCode}' THEN amtdr-amtcr ELSE 0 END) AS TAX1"),
               DB::raw("SUM(CASE WHEN taxper=9 AND paycode='{$cgstCode}' THEN onamt ELSE 0 END) AS BASE2"),
               DB::raw("SUM(CASE WHEN taxper=9 AND paycode='{$cgstCode}' THEN amtdr-amtcr ELSE 0 END) AS TAX2"),
               DB::raw("SUM(CASE WHEN taxper=6 AND paycode='{$sgstCode}' THEN onamt ELSE 0 END) AS BASE3"),
               DB::raw("SUM(CASE WHEN taxper=6 AND paycode='{$sgstCode}' THEN amtdr-amtcr ELSE 0 END) AS TAX3"),
               DB::raw("SUM(CASE WHEN taxper=9 AND paycode='{$sgstCode}' THEN onamt ELSE 0 END) AS BASE4"),
               DB::raw("SUM(CASE WHEN taxper=9 AND paycode='{$sgstCode}' THEN amtdr-amtcr ELSE 0 END) AS TAX4"),
               DB::raw("SUM(CASE WHEN taxper=2.5 AND paycode='{$cgstCode}' THEN onamt ELSE 0 END) AS BASE5"),
               DB::raw("SUM(CASE WHEN taxper=2.5 AND paycode='{$cgstCode}' THEN amtdr-amtcr ELSE 0 END) AS TAX5"),
               DB::raw("SUM(CASE WHEN taxper=2.5 AND paycode='{$sgstCode}' THEN onamt ELSE 0 END) AS BASE6"),
               DB::raw("SUM(CASE WHEN taxper=2.5 AND paycode='{$sgstCode}' THEN amtdr-amtcr ELSE 0 END) AS TAX6"),
               DB::raw('SUM(amtdr) AS billamount')
            )
            ->where('roomtype', 'RO')
            ->whereRaw('amtdr - amtcr <> 0')
            ->whereNotIn('vtype', ['ARRES', 'ADRES'])
            ->where('foliono', '<>', 0)
            ->where(function ($q) {
               $q->where('billno', '<>', 0)
                  ->orWhereNull('modeset');
            })
            ->whereBetween('settledate', [$fromdate, $todate])
            ->where('propertyid', $propertyid)
            ->when(!empty($taxes), fn($q) => $q->whereIn('paycode', $taxes))
            ->groupBy('folionodocid');

         $guestRoom = DB::table('roomocc AS ro')
            ->select(
               'ro.docid AS folionodocid',
               DB::raw('MAX(ro.name) AS GuestName'),
               DB::raw('MAX(s.name) AS company'),
               DB::raw('MAX(s.gstin) AS gstin'),
               DB::raw('GROUP_CONCAT(DISTINCT COALESCE(ro_leader.roomno, ro.roomno)) AS RoomNo')
            )
            ->leftJoin('roomocc AS ro_leader', function ($join) {
               $join->on('ro_leader.docid', '=', 'ro.docid')
                  ->whereRaw("ro_leader.type='O'")
                  ->whereRaw("ro_leader.leaderyn='Y'");
            })
            ->leftJoin('guestfolio AS gf', 'gf.docid', '=', 'ro.docid')
            ->leftJoin('subgroup AS s', 'gf.company', '=', 's.sub_code')
            ->whereRaw("ro.type='O'")
            ->groupBy('ro.docid');

         $results = DB::table(DB::raw("({$paychargeAgg->toSql()}) AS P"))
            ->mergeBindings($paychargeAgg)
            ->join(DB::raw("({$guestRoom->toSql()}) AS G"), function ($join) use ($guestRoom) {
               $join->on('P.folionodocid', '=', 'G.folionodocid');
            })
            ->select(
               'P.foliono',
               'P.folionodocid',
               'P.settledate',
               'P.billno AS BILL_NO',
               'G.GuestName',
               'G.company AS companyname',
               'G.gstin AS companygstin',
               'G.RoomNo',
               'P.AmtDr',
               'P.BASE1',
               'P.TAX1',
               'P.BASE2',
               'P.TAX2',
               'P.BASE3',
               'P.TAX3',
               'P.BASE4',
               'P.TAX4',
               'P.BASE5',
               'P.TAX5',
               'P.BASE6',
               'P.TAX6',
               DB::raw('(P.BASE1+P.BASE2+P.BASE3+P.BASE4+P.BASE5+P.BASE6) AS EBASEVALUE'),
               DB::raw('(P.TAX1+P.TAX2+P.TAX3+P.TAX4+P.TAX5+P.TAX6) AS ETAXAMT'),
               'P.billamount'
            );

         // Apply sort order from frontend
         $orderCol = intval($request->query('orderCol', 3));
         $orderDir = strtolower($request->query('orderDir', 'desc')) === 'asc' ? 'asc' : 'desc';
         $columnsMap = [
            0 => 'P.settledate',
            1 => 'GuestName',
            2 => 'P.foliono',
            3 => 'P.billno',
            4 => 'RoomNo',
            5 => 'P.billamount',
            6 => 'P.BASE5',
            7 => 'P.BASE1',
            8 => 'P.TAX5',
            9 => 'P.TAX6',
            10 => 'P.TAX1',
            11 => 'P.TAX3',
            12 => 'ETAXAMT',
            13 => 'companyname',
            14 => 'companygstin'
         ];
         $orderColumn = $columnsMap[$orderCol] ?? 'P.billno';
         if ($orderColumn === 'P.billno') {
            $results->orderByRaw("CAST(P.billno AS DECIMAL) {$orderDir}");
         } else {
            $results->orderBy(DB::raw($orderColumn), $orderDir);
         }

         $results = $results->get();

         // Check if we have data
         if ($results->isEmpty()) {
            return response()->json(['error' => 'No data found for the selected date range and filters.'], 404);
         }

         $spreadsheet = new Spreadsheet();
         $sheet = $spreadsheet->getActiveSheet();
         $sheet->setTitle('FOM Tax Report');

         $headers = [
            'Date',
            'Guest Name',
            'Folio No.',
            'Bill No.',
            'Room No.',
            'Bill Amount',
            'Goods 2.5%',
            'Goods 6%',
            'Goods 9%',
            'CGST 2.5%',
            'SGST 2.5%',
            'CGST 6%',
            'SGST 6%',
            'CGST 9%',
            'SGST 9%',
            'Till Tax Amt',
            'Company',
            'GSTIN'
         ];

         $colIndex = 1;
         foreach ($headers as $header) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($colLetter . '1', $header);
            $colIndex++;
         }

         $row = 2;
         foreach ($results as $r) {
            $num = fn($v) => round((float)($v ?? 0), 2);

            // Text columns A-E
            $sheet->setCellValue('A' . $row, $r->settledate);
            $sheet->setCellValue('B' . $row, $r->GuestName);
            $sheet->setCellValue('C' . $row, $r->foliono);
            $sheet->setCellValue('D' . $row, $r->BILL_NO);
            $sheet->setCellValue('E' . $row, $r->RoomNo);

            // Numeric columns F-P - use setCellValueExplicit with NUMERIC type
            $numType = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC;
            $sheet->setCellValueExplicit('F' . $row, $num($r->billamount), $numType);    // Bill Amount
            $sheet->setCellValueExplicit('G' . $row, $num($r->BASE5 ?? 0), $numType);    // Goods 2.5%
            $sheet->setCellValueExplicit('H' . $row, $num($r->BASE1 ?? 0), $numType);    // Goods 6%
            $sheet->setCellValueExplicit('I' . $row, $num($r->BASE2 ?? 0), $numType);    // Goods 9%
            $sheet->setCellValueExplicit('J' . $row, $num($r->TAX5 ?? 0), $numType);     // CGST 2.5%
            $sheet->setCellValueExplicit('K' . $row, $num($r->TAX6 ?? 0), $numType);     // SGST 2.5%
            $sheet->setCellValueExplicit('L' . $row, $num($r->TAX1 ?? 0), $numType);     // CGST 6%
            $sheet->setCellValueExplicit('M' . $row, $num($r->TAX3 ?? 0), $numType);     // SGST 6%
            $sheet->setCellValueExplicit('N' . $row, $num($r->TAX2 ?? 0), $numType);     // CGST 9%
            $sheet->setCellValueExplicit('O' . $row, $num($r->TAX4 ?? 0), $numType);     // SGST 9%
            $sheet->setCellValueExplicit('P' . $row, $num($r->ETAXAMT ?? 0), $numType);  // Till Tax Amt

            // Text columns Q-R
            $sheet->setCellValue('Q' . $row, $r->companyname);
            $sheet->setCellValue('R' . $row, $r->companygstin);

            $row++;
         }

         // Add totals row
         $totalRow = $row;
         $sheet->setCellValue('A' . $totalRow, 'Total');
         $sheet->mergeCells('A' . $totalRow . ':E' . $totalRow);

         $sumCols = ['F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P'];
         foreach ($sumCols as $col) {
            $sheet->setCellValue($col . $totalRow, '=SUM(' . $col . '2:' . $col . ($totalRow - 1) . ')');
         }

         $sheet->getStyle('A' . $totalRow . ':R' . $totalRow)->getFont()->setBold(true);

         // Format all numeric columns (F-P) with 2 decimal places
         $lastDataRow = $totalRow;
         $sheet->getStyle('F2:P' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');

         foreach (range('A', 'R') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
         }

         $filename = 'Fom_Tax_Report_' . date('YmdHis') . '.xlsx';

         $writer = new Xlsx($spreadsheet);

         if (ob_get_length()) {
            ob_end_clean();
         }

         // Set headers for download
         header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
         header('Content-Disposition: attachment; filename="' . $filename . '"');
         header('Cache-Control: max-age=0');
         header('Cache-Control: max-age=1');
         header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
         header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
         header('Cache-Control: cache, must-revalidate');
         header('Pragma: public');

         // Save directly to output
         $writer->save('php://output');
         exit;
      } catch (Exception $e) {
         Log::error('Excel Export Error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
         ]);

         return response()->json([
            'error' => 'Failed to generate Excel file: ' . $e->getMessage()
         ], 500);
      }
   }

   public function fetchtaxesnames(Request $request)
   {
      $fromdate = $request->input('fromdate') ?? $this->ncurdate;
      $todate = $request->input('todate') ?? $this->ncurdate;

      $taxnames = Paycharge::select('revmast.name', 'paycharge.paycode', 'paycharge.taxper')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)
         ->where('revmast.field_type', 'T')
         ->whereBetween('paycharge.vdate', [$fromdate, $todate])
         ->groupBy('paycharge.paycode')
         ->get();

      return json_encode($taxnames);
   }


   // public function fetchfomtaxdata(Request $request)
   // {
   //    $propertyid = $this->propertyid;
   //    $cgstCode = 'CGSS' . $propertyid;
   //    $sgstCode = 'SGSS' . $propertyid;
   //    $fromdate = $request->input('fromdate');
   //    $todate = $request->input('todate');

   //    $guestData = DB::table('paycharge as p')
   //       ->select(
   //          'p.foliono',
   //          'p.folionodocid',
   //          'p.settledate',
   //          'p.billno as BILL_NO',
   //          'ro.name as GuestName',
   //          's.name as companyname',
   //          's.gstin as companygstin',
   //          DB::raw('COALESCE(leader_ro.roomno, ro.roomno) as RoomNo'),
   //          DB::raw("'' as RevenueName")
   //       )
   //       ->join('roomocc as ro', function ($join) {
   //          $join->on('p.folionodocid', '=', 'ro.docid')
   //             ->where('ro.type', '=', 'O');
   //       })
   //       ->join('guestfolio as gf', 'p.folionodocid', '=', 'gf.docid')
   //       ->leftJoin('subgroup as s', 'gf.company', '=', 's.sub_code')
   //       ->leftJoin('roomocc as leader_ro', function ($join) {
   //          $join->on('p.folionodocid', '=', 'leader_ro.docid')
   //             ->where('leader_ro.type', '=', 'O')
   //             ->where('leader_ro.leaderyn', '=', 'Y');
   //       })
   //       ->where('p.propertyid', $propertyid)
   //       ->whereBetween('p.settledate', [$fromdate, $todate])
   //       ->where('p.roomtype', 'RO')
   //       ->where('p.foliono', '>', 0)
   //       ->whereRaw('p.amtdr != p.amtcr')
   //       ->whereNotIn('p.vtype', ['ARRES', 'ADRES'])
   //       ->where(function ($query) {
   //          $query->where('p.billno', '>', 0)
   //             ->orWhere('p.paycode', 'ROFF101');
   //       })
   //       ->groupBy(['p.folionodocid', 'p.billno', 'p.foliono', 'p.settledate', 'ro.name', 's.name', 's.gstin', 'leader_ro.roomno', 'ro.roomno'])
   //       ->get();

   //    $result = [];

   //    foreach ($guestData as $guest) {
   //       $taxData = DB::table('paycharge')
   //          ->select(
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 6 AND paycode = '{$cgstCode}' THEN onamt END), 0) as BASEVALUE1"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 6 AND paycode = '{$cgstCode}' THEN amtdr - amtcr END), 0) as TAXAMT1"),
   //             DB::raw("COALESCE(MAX(CASE WHEN taxper = 6 AND paycode = '{$cgstCode}' THEN 6 END), 0) as TAXPER1"),

   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 9 AND paycode = '{$cgstCode}' THEN onamt END), 0) as BASEVALUE2"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 9 AND paycode = '{$cgstCode}' THEN amtdr - amtcr END), 0) as TAXAMT2"),
   //             DB::raw("COALESCE(MAX(CASE WHEN taxper = 9 AND paycode = '{$cgstCode}' THEN 9 END), 0) as TAXPER2"),

   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 6 AND paycode = '{$sgstCode}' THEN onamt END), 0) as BASEVALUE3"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 6 AND paycode = '{$sgstCode}' THEN amtdr - amtcr END), 0) as TAXAMT3"),
   //             DB::raw("COALESCE(MAX(CASE WHEN taxper = 6 AND paycode = '{$sgstCode}' THEN 6 END), 0) as TAXPER3"),

   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 9 AND paycode = '{$sgstCode}' THEN onamt END), 0) as BASEVALUE4"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 9 AND paycode = '{$sgstCode}' THEN amtdr - amtcr END), 0) as TAXAMT4"),
   //             DB::raw("COALESCE(MAX(CASE WHEN taxper = 9 AND paycode = '{$sgstCode}' THEN 9 END), 0) as TAXPER4"),

   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 2.5 AND paycode = '{$cgstCode}' THEN onamt END), 0) as BASEVALUE5"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 2.5 AND paycode = '{$cgstCode}' THEN amtdr - amtcr END), 0) as TAXAMT5"),
   //             DB::raw("COALESCE(MAX(CASE WHEN taxper = 2.5 AND paycode = '{$cgstCode}' THEN 2.5 END), 0) as TAXPER5"),

   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 2.5 AND paycode = '{$sgstCode}' THEN onamt END), 0) as BASEVALUE6"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper = 2.5 AND paycode = '{$sgstCode}' THEN amtdr - amtcr END), 0) as TAXAMT6"),
   //             DB::raw("COALESCE(MAX(CASE WHEN taxper = 2.5 AND paycode = '{$sgstCode}' THEN 2.5 END), 0) as TAXPER6"),

   //             DB::raw("COALESCE(SUM(CASE WHEN taxper IN (6, 9, 2.5) AND paycode IN ('{$cgstCode}', '{$sgstCode}') THEN onamt END), 0) as EBASEVALUE"),
   //             DB::raw("COALESCE(SUM(CASE WHEN taxper IN (6, 9, 2.5) AND paycode IN ('{$cgstCode}', '{$sgstCode}') THEN amtdr - amtcr END), 0) as ETAXAMT")
   //          )
   //          ->where('folionodocid', $guest->folionodocid)
   //          ->where('billno', $guest->BILL_NO)
   //          ->where('propertyid', $propertyid)
   //          ->first();

   //       $billAmount = DB::table('paycharge')
   //          ->where('folionodocid', $guest->folionodocid)
   //          ->where('billno', $guest->BILL_NO)
   //          ->where('propertyid', $propertyid)
   //          ->sum('amtdr');

   //       $combinedData = (object) array_merge((array) $guest, (array) $taxData);
   //       $combinedData->AmtDr = $billAmount;
   //       $combinedData->billamount = $billAmount;

   //       $result[] = $combinedData;
   //    }

   //    if (empty($result)) {
   //       return response()->json(['taxdetail' => []]);
   //    }

   //    return response()->json([
   //       'taxdetail' => $result,
   //       'fromdate' => $fromdate,
   //       'todate' => $todate
   //    ]);
   // }


   public function occupancyreport(Request $request)
   {
      $permission = revokeopen(141215);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');
      return view('property.occupancyreport', [
         'ncurdate' => $this->ncurdate,
         'comp' => $comp,
         'statename' => $statename
      ]);
   }

   public function fetchoocxhr(Request $request)
   {
      $fordate = $request->input('fordate');
      $todate = $request->input('todate');
      $sortedby = $request->input('sortedby');
      $printcondition = $request->input('printcondition');

      $occdata = DB::table('roomocc')
         ->select([
            'guestprof.mobile_no as mobileno',
            'guestfolio.docid as folionodocid',
            'roomocc.foliono',
            'guestfolio.guestprof',
            'room_mast.room_cat',
            'room_mast.rcode as roomno',
            'guestprof.con_prefix',
            'guestprof.nationality',
            'roomocc.name as guestname',
            'roomocc.propertyid',
            'guestprof.gender',
            'roomocc.adult',
            'roomocc.children',
            DB::raw('(roomocc.adult + roomocc.children) as pax'),
            'countries.name as CName',
            'gueststats.name as GuestStatus',
            'guestprof.age',
            'guestprof.id_proof',
            'guestprof.idproof_no as IdProofNo',
            'guestfolio.arrfrom',
            'guestfolio.destination',
            'guestfolio.travelmode',
            'guestfolio.purvisit',
            'busssource.name as BusiSrc',
            'guestfolio.remark as Remark',
            'subgroup.name as companyname',
            'sg.name as travelname',
            'roomocc.roomrate',
            'guestfolio.rodisc as roomdisc',
            'plan_mast.name as package',
            'plan_mast.tarrif',
            'roomocc.plancode',
            'roomocc.ratecode',
            'roomocc.chngdate',
            'roomocc.type',
            DB::raw('CONCAT(DATE_FORMAT(guestfolio.vdate, "%d-%m-%Y"), " ", DATE_FORMAT(roomocc.chkintime, "%h:%i")) as chkindate'),
            DB::raw('DATE_FORMAT(roomocc.depdate, "%d-%m-%Y") as expdepdate'),
            DB::raw('COALESCE(DATE_FORMAT(roomocc.chkoutdate, "%d-%m-%Y"), "") as depdate'),
            'roomocc.RRTaxInc',
            'guestfolio.add1',
            'guestfolio.add2 as address'
         ])
         ->join('room_mast', function ($query) {
            $query->on('room_mast.rcode', '=', 'roomocc.roomno')
               ->where('room_mast.propertyid', $this->propertyid)
               ->where('room_mast.type', 'RO');
         })
         ->leftJoin('guestfolio', function ($query) {
            $query->on('roomocc.docid', '=', 'guestfolio.docid')
               ->where('guestfolio.propertyid', $this->propertyid);
         })
         ->leftJoin('guestprof', function ($query) {
            $query->on('guestfolio.guestprof', '=', 'guestprof.guestcode')
               ->where('guestprof.propertyid', $this->propertyid);
         })
         ->leftJoin('plan_mast', function ($query) {
            $query->on('roomocc.plancode', '=', 'plan_mast.pcode')
               ->where('plan_mast.propertyid', $this->propertyid);
         })
         ->leftJoin('countries', function ($query) {
            $query->on('guestprof.nationality', '=', 'countries.country_code')
               ->where('countries.propertyid', $this->propertyid);
         })
         ->leftJoin('gueststats', function ($query) {
            $query->on('guestprof.guest_status', '=', 'gueststats.gcode')
               ->where('gueststats.propertyid', $this->propertyid);
         })
         ->leftJoin('subgroup', function ($query) {
            $query->on('guestfolio.company', '=', 'subgroup.sub_code')
               ->where('subgroup.propertyid', $this->propertyid);
         })
         ->leftJoin('subgroup as sg', function ($query) {
            $query->on('guestfolio.travelagent', '=', 'sg.sub_code')
               ->where('sg.propertyid', $this->propertyid);
         })
         ->leftJoin('busssource', function ($query) {
            $query->on('busssource.bcode', '=', 'guestfolio.busssource')
               ->where('busssource.propertyid', $this->propertyid);
         })
         ->where('roomocc.propertyid', $this->propertyid)
         ->where('roomocc.chkindate', '<=', $todate)
         ->where(function ($query) use ($fordate) {
            $query->where('roomocc.chkoutdate', '>=', $fordate)
               ->orWhereNull('roomocc.chkoutdate');
         })
         // ->where(function ($query) use ($fordate) {
         //    $query->where('roomocc.chkoutdate', '<>', $fordate)
         //       ->orWhereNull('roomocc.chkoutdate');
         // })
         // ->where(function ($query) use ($todate) {
         //    $query->where('roomocc.chkoutdate', '<>', $todate)
         //       ->orWhereNull('roomocc.chkoutdate');
         // })
         ->groupBy('roomocc.docid')
         ->groupBy('roomocc.sno1')
         ->orderBy('roomocc.chkindate')
         ->orderBy('roomocc.folioNo')
         ->get();

      $dayuse = RoomOcc::where('propertyid', $this->propertyid)->where('chkindate', $fordate)->where('chkoutdate', $fordate)
         ->whereIn('type', ['O'])
         ->count();

      $data = [
         'occdata' => $occdata,
         'dayuse' => $dayuse
      ];
      return response()->json($data);
   }

   public function enviroform(Request $request)
   {
      $data = EnviroFom::where('propertyid', $this->propertyid)->first();
      return json_encode($data);
   }


   public function itemwisesale(Request $request)
   {
      $permission = revokeopen(171713);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;
      $taxnames = Paycharge::select('revmast.name', 'paycharge.paycode', 'paycharge.taxper')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)
         ->where('revmast.field_type', 'T')
         ->whereNotNull('paycharge.taxper')
         ->groupBy('paycharge.paycode')
         ->get();
      $data = DB::table('guestfolio')->where('propertyid', $this->propertyid)->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      $departs = Depart::where('propertyid', $this->propertyid)->whereIn('nature', ['Room Service', 'Outlet'])->groupBy('dcode')->orderBy('name', 'ASC')->get();
      $items = ItemMast::where('Property_ID', $this->propertyid)->groupBy('Code')->orderBy('Name', 'ASC')->get();
      return view('property.pos_itemwisesale', [
         'data' => $data,
         'fromdate' => $fromdate,
         'company' => $company,
         'statename' => $statename,
         'taxnames' => $taxnames,
         'departs' => $departs,
         'items' => $items,
      ]);
   }

   public function itemwiserepfetch(Request $request)
   {
      try {
         $fromDate = $request->input('fromdate');
         $toDate = $request->input('todate');
         $allOutlets = explode(',', $request->input('alloutlets'));
         $allitemgroups = explode(',', $request->input('allitemgroups'));
         $allitems = explode(',', $request->input('allitems'));
         $propertyId = $this->propertyid;
         $nckot = $request->nckot;

         $depart = Depart::where('propertyid', $propertyId)
            ->whereIn('dcode', $allOutlets)
            ->get();

         $shortname1 = [];
         $shortname2 = [];
         foreach ($depart as $row) {
            $shortname1[] = 'B' . $row->short_name;
            if ($nckot != 'N') {
               $shortname2[] = 'N' . $row->short_name;
            }
         }

         if ($nckot == 'N') {
            $saleQuery = DB::table('sale1 as S')
               ->select([
                  DB::raw('MAX(sk.docid) as DOC'),
                  'I.DispCode',
                  DB::raw('MAX(I.HSNCode) as HSNCODE'),
                  'I.Name as ITEMNAME',
                  DB::raw('MAX(U.name) as UNIT'),
                  DB::raw('0 as NCQTY'),
                  DB::raw('SUM(sk.qtyiss) as QTY'),
                  DB::raw('SUM(sk.amount) as VALUE1'),
                  DB::raw('SUM(sk.discamt) as DISC'),
                  DB::raw('SUM(sk.amount - sk.discapp) as VALUE2'),
                  DB::raw('MAX(IG.name) as ITEMGROUP'),
                  DB::raw('MAX(sk.restcode) as RestCode'),
                  DB::raw('MAX(D.name) as DepartCode'),
               ])
               ->join('stock as sk', 'S.docid', '=', 'sk.docid')
               ->join('itemmast as I', function ($join) {
                  $join->on('sk.item', '=', 'I.Code')
                     ->on('sk.itemrestcode', '=', 'I.RestCode');
               })
               ->join('unitmast as U', function ($join) use ($propertyId) {
                  $join->on('U.ucode', '=', 'I.Unit')
                     ->where('U.propertyid', $propertyId);
               })
               ->join('itemgrp as IG', 'I.ItemGroup', '=', 'IG.code')
               ->join('depart as D', 'S.restcode', '=', 'D.dcode')
               ->whereIn('sk.vtype', $shortname1)
               ->where('S.delflag', 'N')
               ->whereIn('I.ItemGroup', $allitemgroups)
               ->whereIn('I.Code', $allitems)
               ->whereBetween('S.vdate', [$fromDate, $toDate])
               ->whereIn('S.restcode', $allOutlets)
               ->groupBy('ITEMNAME', 'sk.restcode')
               ->orderBy('ITEMNAME')
               ->get();
         } else {
            $saleQuery = DB::table('stock as sk')
               ->select([
                  DB::raw('MAX(sk.docid) as DOC'),
                  'I.DispCode',
                  DB::raw('MAX(I.HSNCode) as HSNCODE'),
                  'I.Name as ITEMNAME',
                  DB::raw('MAX(U.name) as UNIT'),
                  DB::raw('SUM(CASE WHEN sk.vtype IN (' . implode(',', array_map(fn($val) => "'$val'", $shortname1)) . ') THEN sk.qtyiss ELSE 0 END) as QTY'),
                  DB::raw('SUM(CASE WHEN sk.vtype IN (' . implode(',', array_map(fn($val) => "'$val'", $shortname2)) . ') THEN sk.qtyiss ELSE 0 END) as NCQTY'),
                  DB::raw('SUM(sk.amount) as VALUE1'),
                  DB::raw('SUM(sk.discamt) as DISC'),
                  DB::raw('SUM(sk.amount - sk.discapp) as VALUE2'),
                  DB::raw('MAX(IG.name) as ITEMGROUP'),
                  'sk.restcode',
                  DB::raw('MAX(D.name) as DepartCode'),
               ])
               ->join('itemmast as I', function ($join) {
                  $join->on('sk.item', '=', 'I.Code')
                     ->on('sk.itemrestcode', '=', 'I.RestCode');
               })
               ->join('unitmast as U', function ($join) use ($propertyId) {
                  $join->on('U.ucode', '=', 'I.Unit')
                     ->where('U.propertyid', $propertyId);
               })
               ->join('itemgrp as IG', 'I.ItemGroup', '=', 'IG.code')
               ->join('depart as D', 'sk.restcode', '=', 'D.dcode')
               ->whereIn('sk.vtype', array_merge($shortname1, $shortname2))
               ->where('sk.delflag', 'N')
               ->whereIn('I.ItemGroup', $allitemgroups)
               ->whereIn('I.Code', $allitems)
               ->whereBetween('sk.vdate', [$fromDate, $toDate])
               ->whereIn('sk.restcode', $allOutlets)
               ->groupBy('ITEMNAME', 'sk.restcode')
               ->orderBy('ITEMNAME')
               ->get();
         }

         return response()->json([
            'items' => $saleQuery,
            'shortname1' => $shortname1,
            'shortname2' => $shortname2,
            'status' => 'success'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'An error occurred while fetching the report: ' . $e->getMessage()
         ], 500);
      }
   }



   public function printItemWiseSale(Request $request)
   {
      try {
         $company = Companyreg::where('propertyid', $this->propertyid)->first();
         $fromDate = $request->input('fromdate');
         $toDate = $request->input('todate');
         $allOutlets = explode(',', $request->input('alloutlets'));
         $allitemgroups = explode(',', $request->input('allitemgroups'));
         $allitems = explode(',', $request->input('allitems'));
         $nckot = $request->input('nckot', 'N');
         $groupby = $request->input('groupby', 'ITEMGROUP');
         $propertyId = $this->propertyid;

         $depart = Depart::where('propertyid', $propertyId)
            ->whereIn('dcode', $allOutlets)
            ->get();

         $shortname1 = [];
         $shortname2 = [];
         foreach ($depart as $row) {
            $shortname1[] = 'B' . $row->short_name;
            if ($nckot != 'N') {
               $shortname2[] = 'N' . $row->short_name;
            }
         }

         if ($nckot == 'N') {
            $saleQuery = DB::table('sale1 as S')
               ->select([
                  DB::raw('MAX(sk.docid) as DOC'),
                  'I.DispCode',
                  DB::raw('MAX(I.HSNCode) as HSNCODE'),
                  'I.Name as ITEMNAME',
                  DB::raw('MAX(U.name) as UNIT'),
                  DB::raw('0 as NCQTY'),
                  DB::raw('SUM(sk.qtyiss) as QTY'),
                  DB::raw('SUM(sk.amount) as VALUE1'),
                  DB::raw('SUM(sk.discamt) as DISC'),
                  DB::raw('SUM(sk.amount - sk.discapp) as VALUE2'),
                  DB::raw('MAX(IG.name) as ITEMGROUP'),
                  DB::raw('MAX(sk.restcode) as RestCode'),
                  DB::raw('MAX(D.name) as DepartCode'),
               ])
               ->join('stock as sk', 'S.docid', '=', 'sk.docid')
               ->join('itemmast as I', function ($join) {
                  $join->on('sk.item', '=', 'I.Code')
                     ->on('sk.itemrestcode', '=', 'I.RestCode');
               })
               ->join('unitmast as U', function ($join) use ($propertyId) {
                  $join->on('U.ucode', '=', 'I.Unit')
                     ->where('U.propertyid', $propertyId);
               })
               ->join('itemgrp as IG', 'I.ItemGroup', '=', 'IG.code')
               ->join('depart as D', 'S.restcode', '=', 'D.dcode')
               ->whereIn('sk.vtype', $shortname1)
               ->where('S.delflag', 'N')
               ->whereIn('I.ItemGroup', $allitemgroups)
               ->whereIn('I.Code', $allitems)
               ->whereBetween('S.vdate', [$fromDate, $toDate])
               ->whereIn('S.restcode', $allOutlets)
               ->groupBy('ITEMNAME', 'sk.restcode')
               ->orderBy('ITEMNAME')
               ->get();
         } else {
            $saleQuery = DB::table('stock as sk')
               ->select([
                  DB::raw('MAX(sk.docid) as DOC'),
                  'I.DispCode',
                  DB::raw('MAX(I.HSNCode) as HSNCODE'),
                  'I.Name as ITEMNAME',
                  DB::raw('MAX(U.name) as UNIT'),
                  DB::raw('SUM(CASE WHEN sk.vtype IN (' . implode(',', array_map(fn($val) => "'$val'", $shortname1)) . ') THEN sk.qtyiss ELSE 0 END) as QTY'),
                  DB::raw('SUM(CASE WHEN sk.vtype IN (' . implode(',', array_map(fn($val) => "'$val'", $shortname2)) . ') THEN sk.qtyiss ELSE 0 END) as NCQTY'),
                  DB::raw('SUM(sk.amount) as VALUE1'),
                  DB::raw('SUM(sk.discamt) as DISC'),
                  DB::raw('SUM(sk.amount - sk.discapp) as VALUE2'),
                  DB::raw('MAX(IG.name) as ITEMGROUP'),
                  'sk.restcode',
                  DB::raw('MAX(D.name) as DepartCode'),
               ])
               ->join('itemmast as I', function ($join) {
                  $join->on('sk.item', '=', 'I.Code')
                     ->on('sk.itemrestcode', '=', 'I.RestCode');
               })
               ->join('unitmast as U', function ($join) use ($propertyId) {
                  $join->on('U.ucode', '=', 'I.Unit')
                     ->where('U.propertyid', $propertyId);
               })
               ->join('itemgrp as IG', 'I.ItemGroup', '=', 'IG.code')
               ->join('depart as D', 'sk.restcode', '=', 'D.dcode')
               ->whereIn('sk.vtype', array_merge($shortname1, $shortname2))
               ->where('sk.delflag', 'N')
               ->whereIn('I.ItemGroup', $allitemgroups)
               ->whereIn('I.Code', $allitems)
               ->whereBetween('sk.vdate', [$fromDate, $toDate])
               ->whereIn('sk.restcode', $allOutlets)
               ->groupBy('ITEMNAME', 'sk.restcode')
               ->orderBy('ITEMNAME')
               ->get();
         }

         // Group the data
         $grouped = [];
         foreach ($saleQuery as $row) {
            $key = $row->{$groupby} ?? 'Others';
            if (!isset($grouped[$key])) {
               $grouped[$key] = [];
            }
            $grouped[$key][] = $row;
         }

         $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'property.print.pos_itemwisesale_print',
            [
               'company'  => $company,
               'fromDate' => $fromDate,
               'toDate'   => $toDate,
               'grouped'  => $grouped,
               'nckot'    => $nckot,
               'groupby'  => $groupby,
            ]
         )->setPaper('a4', 'landscape');

         return $pdf->stream('itemwise-sale-report.pdf');
      } catch (\Exception $e) {
         return response()->json([
            'status' => 'error',
            'message' => 'Print error: ' . $e->getMessage()
         ], 500);
      }
   }


   public function deletedunsettledbill(Request $request)
   {
      $permission = revokeopen(171714);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;
      $taxnames = Paycharge::select('revmast.name', 'paycharge.paycode', 'paycharge.taxper')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)
         ->where('revmast.field_type', 'T')
         ->whereNotNull('paycharge.taxper')
         ->groupBy('paycharge.paycode')
         ->get();
      $data = DB::table('guestfolio')->where('propertyid', $this->propertyid)->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      $departs = Depart::where('propertyid', $this->propertyid)->whereIn('nature', ['Room Service', 'Outlet'])->groupBy('dcode')->orderBy('name', 'ASC')->get();
      $items = ItemMast::where('Property_ID', $this->propertyid)->groupBy('Code')->orderBy('Name', 'ASC')->get();
      return view('property.pos_saledeletereport', [
         'data' => $data,
         'fromdate' => $fromdate,
         'company' => $company,
         'statename' => $statename,
         'taxnames' => $taxnames,
         'departs' => $departs,
         'items' => $items,
      ]);
   }

   public function saledelxhr(Request $request)
   {

      $alloutlets = $request->input('alloutlets');
      $delorunsettle = $request->input('delorunsettle');
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');

      if ($delorunsettle == 'delete') {
         $query = DB::table('sale1 as S')
            ->leftJoin('depart as D', 'S.restcode', '=', 'D.dcode')
            ->leftJoin('server_mast as W', 'S.waiter', '=', 'W.scode')
            ->select(
               'S.vno',
               'S.vdate',
               'S.roomno',
               'S.netamt',
               'S.guaratt',
               'W.name as Steward',
               'D.name as OutletName',
               'D.dcode as OutletCode',
               'S.delremark',
               'S.u_name',
               'S.delflag'
            )
            ->where('S.propertyid', '=', $this->propertyid)
            ->whereBetween('S.vdate', [$fromdate, $todate])
            ->whereIn('S.restcode', explode(',', $alloutlets))
            ->where('S.delflag', '=', 'Y')
            ->get();
      } else if ($delorunsettle == 'unsettle') {
         $query = Sale1::select(
            'sale1.vno',
            'sale1.vdate',
            'sale1.roomno',
            'sale1.netamt',
            'sale1.guaratt',
            'server_mast.name AS Steward',
            'depart.name AS OutletName',
            'depart.dcode AS OutletCode',
            'sale1.delremark',
            'sale1.u_name',
            'sale1.delflag'
         )
            ->leftJoin('depart', 'sale1.restcode', '=', 'depart.dcode')
            ->leftJoin('server_mast', 'sale1.waiter', '=', 'server_mast.scode')
            ->where('sale1.propertyid', '=', $this->propertyid)
            ->whereBetween('sale1.vdate', [$fromdate, $todate])
            ->whereIn('sale1.restcode', explode(',', $alloutlets))
            ->where('sale1.delflag', 'N')
            ->whereNotIn('sale1.docid', function ($query) {
               $query->select('docid')->distinct()->from('paycharge');
            })
            ->get();
      } else if ($delorunsettle == 'combine') {
         $query = DB::table('sale1 as S')
            ->leftJoin('depart as D', 'S.restcode', '=', 'D.dcode')
            ->leftJoin('server_mast as W', 'S.waiter', '=', 'W.scode')
            ->select(
               'S.vno',
               'S.vdate',
               'S.roomno',
               'S.netamt',
               'S.guaratt',
               'W.name as Steward',
               'D.name as OutletName',
               'D.dcode as OutletCode',
               'S.delremark',
               'S.u_name',
               'S.delflag'
            )
            ->where('S.propertyid',  '=', $this->propertyid)
            ->whereBetween('S.vdate', [$fromdate, $todate])
            ->whereIn('S.restcode', explode(',', $alloutlets))
            ->where(function ($query) {
               $query->where('S.delflag', 'Y') // Delete condition
                  ->orWhere(function ($query) { // Unsettle condition
                     $query->where('S.delflag', 'N')
                        ->whereNotIn('S.docid', function ($subquery) {
                           $subquery->select('docid')
                              ->distinct()
                              ->from('paycharge');
                        });
                  });
            })
            ->get();
      }

      $data = [
         'items' => $query,
      ];

      return json_encode($data);
   }

   public function getindex(Request $request)
   {
      if ($this->revokeopen(141114)->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $startdate = $request->input('startdate');
      $enddate = $request->input('enddate');

      $gueststayduration = RoomOcc::select('chkindate', DB::raw('COUNT(DISTINCT docid) as guest_count'))
         ->where('propertyid', $this->propertyid)
         ->whereBetween('chkindate', [$startdate, $enddate])
         ->groupBy('chkindate')
         ->orderBy('chkindate')
         ->get();

      $depart = Depart::where('propertyid', $this->propertyid)
         ->where('nature', 'Outlet')
         ->where('kot_yn', 'Y')
         ->get();

      // Batch the per-outlet voucher-type + per-voucher sums (was 1 VoucherType
      // + 1 Paycharge query per voucher, twice â€” today and yesterday).
      $outletCodes = $depart->pluck('dcode')->all();
      $outletVouchers = VoucherType::where('propertyid', $this->propertyid)
         ->whereIn('restcode', $outletCodes)
         ->get()
         ->filter(function ($vt) use ($depart) {
            $d = $depart->firstWhere('dcode', $vt->restcode);
            return $d && $vt->description === $d->short_name . ' Memo Entry';
         });

      $memoVtypes = $outletVouchers->pluck('v_type')->all();
      $memoSumsToday = collect();
      if ($memoVtypes) {
         $memoSumsToday = Paycharge::where('propertyid', $this->propertyid)
            ->whereIn('vtype', $memoVtypes)
            ->where('vdate', $this->ncurdate)
            ->groupBy('vtype')
            ->selectRaw('vtype, SUM(amtcr) as total')
            ->pluck('total', 'vtype');
      }

      $totalamount = (float) $memoSumsToday->sum();

      $totalamount1 = Paycharge::where('propertyid', $this->propertyid)
         ->where('vtype', 'REC')
         ->where('vdate', $this->ncurdate)
         ->sum('amtcr');

      $combinedTotal = $totalamount + $totalamount1;

      $yesterday = date('Y-m-d', strtotime($this->ncurdate . ' -2 day'));
      $yesterdaytime = date('Y-m-d H:i:s', strtotime($this->ncurdate . ' -2 day'));
      $last7days = date('Y-m-d', strtotime($this->ncurdate . ' -7 day'));
      $yesterdayroomchargamount = Paycharge::where('propertyid', $this->propertyid)
         ->where('vdate', $yesterday)
         ->sum('amtcr');

      $totalkotlast24 = Kot::where('propertyid', $this->propertyid)
         ->where('u_entdt', '>=', $yesterdaytime)
         ->distinct('docid')
         ->count('docid');

      $totalreservation7days = Bookings::where('Property_ID', $this->propertyid)
         ->where('vdate', '>=', $last7days)
         ->distinct('DocId')
         ->count('DocId');

      $totalamountoutletyesterday = 0.00;

      if ($memoVtypes) {
         $memoSumsYesterday = Paycharge::where('propertyid', $this->propertyid)
            ->whereIn('vtype', $memoVtypes)
            ->where('vdate', $yesterday)
            ->groupBy('vtype')
            ->selectRaw('vtype, SUM(amtcr) as total')
            ->pluck('total', 'vtype');

         $totalamountoutletyesterday = (float) $memoSumsYesterday->sum();
      }

      $yesterdaycombinedTotal = $yesterdayroomchargamount + $totalamountoutletyesterday;

      $percentageChange = $yesterdaycombinedTotal > 0
         ? (($combinedTotal - $yesterdaycombinedTotal) / $yesterdaycombinedTotal) * 100
         : 0;


      $totalRooms = RoomMast::where('propertyid', $this->propertyid)
         ->where('type', 'RO')
         ->count();

      $occupiedRoomsCount = DB::table('roomocc')
         ->leftJoin('paycharge', function ($join) {
            $join->on('paycharge.roomno', '=', 'roomocc.roomno')
               ->on('paycharge.sno1', '=', 'roomocc.sno1');
         })
         ->where('roomocc.propertyid', $this->propertyid)
         ->whereNull('roomocc.type')
         ->where(function ($query) {
            $query->where('paycharge.vtype', 'BRS')
               ->orWhereNull('paycharge.vtype');
         })
         ->groupBy('roomocc.roomno', 'roomocc.sno1')
         ->get()
         ->count();

      $occupancyPercentage = 100;
      if ($totalRooms > 0) {
         $occupancyPercentage = ($occupiedRoomsCount / $totalRooms) * 100;
      }

      $data = [
         'occupancyPercentage' => $occupancyPercentage,
         'totalreservation7days' => $totalreservation7days,
         'totalkotlast24' => $totalkotlast24,
         'yesterdaycombinedTotal' => number_format($yesterdaycombinedTotal, 2),
         'yesterday' => $yesterday,
         'combinedTotal' => number_format($combinedTotal, 2),
         'gueststayduration' => $gueststayduration,
         'enddate' => $enddate,
         'percentageChange' => str_replace(',', '', number_format($percentageChange, 2)),
      ];

      return response()->json(['success' => 'Data Found', 'data' => $data]);
   }


   public function salesummary(Request $request)
   {
      $permission = revokeopen(171811);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $fromdate = $this->ncurdate;
      $taxnames = Paycharge::select('revmast.name', 'paycharge.paycode', 'paycharge.taxper')
         ->leftJoin('revmast', 'revmast.rev_code', '=', 'paycharge.paycode')
         ->where('paycharge.propertyid', $this->propertyid)
         ->where('revmast.field_type', 'T')
         ->whereNotNull('paycharge.taxper')
         ->groupBy('paycharge.paycode')
         ->get();
      $data = DB::table('guestfolio')->where('propertyid', $this->propertyid)->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      $departs = Depart::where('propertyid', $this->propertyid)->whereIn('nature', ['Room Service', 'Outlet'])->groupBy('dcode')->orderBy('name', 'ASC')->get();
      $items = ItemMast::where('Property_ID', $this->propertyid)->groupBy('Code')->orderBy('Name', 'ASC')->get();
      return view('property.pos_salesummary', [
         'data' => $data,
         'fromdate' => $fromdate,
         'company' => $company,
         'statename' => $statename,
         'taxnames' => $taxnames,
         'departs' => $departs,
         'items' => $items,
      ]);
   }


   public function salesummaryrpt(Request $request)
   {
      $alloutlets = $request->input('alloutlets');
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');

      $cgst = 'CGSS' . $this->propertyid;
      $sgst = 'SGSS' . $this->propertyid;
      $igst = 'IGSS' . $this->propertyid;

      $taxRates = DB::table('sale2')
         ->select('taxper')
         ->distinct()
         ->where('delflag', '<>', 'Y')
         ->whereBetween('vdate', [$fromdate, $todate])
         ->whereIn('restcode', explode(',', $alloutlets))
         ->where('propertyid', $this->propertyid)
         ->pluck('taxper')
         ->toArray();

      $dynamicColumns = [];
      foreach ($taxRates as $rate) {
         $r = str_replace('.', '_', $rate);

         $dynamicColumns[] = "COALESCE(SUM(CASE WHEN T.taxcode = '$cgst' AND T.taxper = $rate THEN T.basevalue END), 0.00) AS CGST_BASE_$r";
         $dynamicColumns[] = "COALESCE(SUM(CASE WHEN T.taxcode = '$cgst' AND T.taxper = $rate THEN T.taxamt END), 0.00) AS CGST_TAXAMT_$r";
         $dynamicColumns[] = "COALESCE(SUM(CASE WHEN T.taxcode = '$sgst' AND T.taxper = $rate THEN T.basevalue END), 0.00) AS SGST_BASE_$r";
         $dynamicColumns[] = "COALESCE(SUM(CASE WHEN T.taxcode = '$sgst' AND T.taxper = $rate THEN T.taxamt END), 0.00) AS SGST_TAXAMT_$r";
         $dynamicColumns[] = "COALESCE(SUM(CASE WHEN T.taxcode = '$igst' AND T.taxper = $rate THEN T.basevalue END), 0.00) AS IGST_BASE_$r";
         $dynamicColumns[] = "COALESCE(SUM(CASE WHEN T.taxcode = '$igst' AND T.taxper = $rate THEN T.taxamt END), 0.00) AS IGST_TAXAMT_$r";
      }

      $dynamicColumnsSql = implode(",\n            ", $dynamicColumns);

      $outletsArray = explode(',', $alloutlets);
      $placeholders = implode(',', array_fill(0, count($outletsArray), '?'));

      $sql = "
        WITH S1 AS (
            SELECT
                restcode,
                vdate,
                MIN(vno) AS MinBillNo,
                MAX(vno) AS MaxBillNo,
                SUM(netamt) AS NetAmt,
                SUM(taxable) AS Taxable,
                SUM(nontaxable) AS NonTaxable,
                SUM(roundoff) AS RoundOff,
                SUM(dedamt) AS DedAmt,
                SUM(addamt) AS AddAmt,
                SUM(servicecharge) AS ServiceCharge,
                SUM(discamt) AS DiscAmt,
                SUM(total) AS GoodsAmt
            FROM sale1
            WHERE delflag <> 'Y'
              AND vdate BETWEEN ? AND ?
              AND restcode IN ($placeholders)
            GROUP BY restcode, vdate
        ),
        T AS (
            SELECT
                docid,
                vdate,
                SUM(basevalue) AS basevalue,
                SUM(taxamt) AS taxamt,
                taxcode,
                taxper
            FROM sale2
            WHERE delflag <> 'Y'
              AND vdate BETWEEN ? AND ?
              AND restcode IN ($placeholders)
            GROUP BY docid, vdate, taxcode, taxper
        )
        SELECT
            d.name AS DepartName,
            S1.vdate,
            S1.MinBillNo,
            S1.MaxBillNo,
            S1.NetAmt,
            S1.Taxable,
            S1.NonTaxable,
            S1.RoundOff,
            S1.DedAmt,
            S1.AddAmt,
            S1.ServiceCharge,
            S1.DiscAmt,
            S1.GoodsAmt,
            $dynamicColumnsSql
        FROM S1
        LEFT JOIN sale1 S ON S1.restcode = S.restcode AND S1.vdate = S.vdate
        LEFT JOIN T ON T.docid = S.docid AND T.vdate = S.vdate
        LEFT JOIN depart d ON S1.restcode = d.dcode
        GROUP BY
            d.name,
            S1.vdate,
            S1.MinBillNo,
            S1.MaxBillNo,
            S1.NetAmt,
            S1.Taxable,
            S1.NonTaxable,
            S1.RoundOff,
            S1.DedAmt,
            S1.AddAmt,
            S1.ServiceCharge,
            S1.DiscAmt,
            S1.GoodsAmt
        ORDER BY d.name, S1.vdate
    ";

      $bindings = [
         $fromdate,
         $todate,
         ...$outletsArray,
         $fromdate,
         $todate,
         ...$outletsArray
      ];

      $results = DB::select($sql, $bindings);

      return response()->json(['items' => $results]);
   }

   public function arrivallist(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');
      $busssource = DB::table('booking')
         ->select('busssource.name', 'booking.BussSource')
         ->leftJoin('busssource', function ($join) {
            $join->on('busssource.bcode', '=', 'booking.BussSource')
               ->on('busssource.propertyid', '=', 'booking.Property_ID');
         })
         ->where('booking.Property_ID', $this->propertyid)
         ->where('booking.BussSource', '!=', '')
         ->whereNotNull('booking.BussSource')
         ->groupBy('booking.BussSource')
         ->get();

      $bookingsource = Bookings::select('MarketSeg')
         ->where('booking.Property_ID', $this->propertyid)
         ->where(function ($query) {
            $query->where('booking.MarketSeg', '!=', '')
               ->orWhereNotNull('booking.MarketSeg');
         })
         ->groupBy('booking.MarketSeg')
         ->get();

      return view('property.arrivallist', [
         'comp' => $comp,
         'statename' => $statename,
         'fromdate' => $this->ncurdate,
         'busssource' => $busssource,
         'bookingsource' => $bookingsource,
      ]);
   }

   public function arrivallistfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pendingyn = $request->input('pendingyn');
      $busssource = $request->input('busssource');
      $bookingsource = $request->input('bookingsource');

      $report = GrpBookinDetail::leftJoin('booking', 'booking.DocId', '=', 'grpbookingdetails.BookingDocid')
         ->leftJoin('guestprof', 'guestprof.docid', '=', 'grpbookingdetails.BookingDocid')
         ->leftJoin('plan_mast', 'grpbookingdetails.Plan_Code', '=', 'plan_mast.pcode')
         ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
         ->leftJoin('subgroup AS S', 'booking.Company', '=', 'S.sub_code')
         ->leftJoin('subgroup AS T', 'booking.TravelAgency', '=', 'T.sub_code')
         ->leftJoin('bookingplandetails', function ($join) {
            $join->on('bookingplandetails.docid', '=', 'grpbookingdetails.BookingDocid')
               ->on('bookingplandetails.sno1', '=', 'grpbookingdetails.Sno');
         })
         ->leftJoin('paycharge', function ($join) {
            $join->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
               ->where('paycharge.sno', '1');
         })
         ->select([
            DB::raw('(SUM(paycharge.amtcr) - SUM(paycharge.amtdr)) / booking.NoofRooms AS advance'),
            DB::raw("CASE WHEN bookingplandetails.docid != '' THEN bookingplandetails.netplanamt ELSE grpbookingdetails.Tarrif END AS tarrifamount"),
            'T.name as travelname',
            'booking.Vtype',
            'booking.DocId',
            'grpbookingdetails.Sno',
            'grpbookingdetails.NoDays',
            'booking.BookNo AS ResNo',
            'grpbookingdetails.ContraDocId',
            'grpbookingdetails.GuestName AS GuestName',
            'booking.MobNo',
            'S.name AS Company',
            'grpbookingdetails.RoomDet AS RoomDet',
            'grpbookingdetails.ArrDate',
            'grpbookingdetails.ArrTime',
            'grpbookingdetails.Adults AS Pax',
            'grpbookingdetails.Childs AS Child',
            'grpbookingdetails.DepDate',
            'grpbookingdetails.DepTime',
            'plan_mast.name AS PlanName',
            'grpbookingdetails.RoomNo',
            'room_cat.name AS RoomType',
            'booking.ArrFrom AS ArrDetail',
            'booking.BookedBy',
            'booking.ResStatus',
            'booking.Remarks',
            'booking.U_Name'
         ])
         ->where('grpbookingdetails.Cancel', 'N')
         ->where('grpbookingdetails.Property_ID', $this->propertyid)
         ->whereBetween('grpbookingdetails.ArrDate', [$fromdate, $todate])
         ->groupBy('grpbookingdetails.BookingDocid', 'grpbookingdetails.Sno');

      if ($pendingyn == 'pending') {
         $report->where('grpbookingdetails.ContraDocId', '');
      }

      if ($busssource != 'all') {
         $report->where('booking.BussSource', $busssource);
      }
      if ($bookingsource != 'all') {
         $report->where('booking.MarketSeg', $bookingsource);
      }

      $report->orderByDesc('booking.BookNo')
         ->orderBy('grpbookingdetails.Sno');

      $main = $report->get();

      $docids = $main->pluck('DocId')->toArray();

      $roomInclusive = DB::table('room_inclusive')
         ->whereIn('docid', $docids)
         ->orderBy('sno')
         ->get()
         ->groupBy('docid');

      foreach ($main as $item) {
         $item->room_inclusive = $roomInclusive[$item->DocId] ?? [];
      }

      return response()->json(['data' => $main]);
   }


   public function dailyreport(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $ncurdate = $this->ncurdate;
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      $company = SubGroup::where('propertyid', $this->propertyid)->whereIn('comp_type', ['Corporate', 'Travel Agency'])
         ->orderBy('name')->groupBy('sub_code')->get();
      $departs = Depart::where('propertyid', $this->propertyid)->whereIn('nature', ['Room Service', 'Outlet'])->groupBy('dcode')->orderBy('name', 'ASC')->get();
      $items = Itemmast::where('Property_ID', $this->propertyid)->groupBy('Code')->orderBy('Name', 'ASC')->get();
      $taxes = [
         1 => 'CGSS' . $this->propertyid . '-CGST (SALES)',
         2 => 'SGSS' . $this->propertyid . '-SGST (SALES)',
         3 => 'NT' . $this->propertyid . '-NO TAX',
      ];

      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');

      return view('property.dailyreport', [
         'fordate' => $ncurdate,
         'comp' => $comp,
         'company' => $company,
         'departs' => $departs,
         'items' => $items,
         'taxes' => $taxes,
         'todate' => $ncurdate,
         'statename' => $statename
      ]);
   }

   public function lookuprromtype(Request $request)
   {
      $permission = revokeopen(131212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $resstatus = Bookings::where('Property_ID', $this->propertyid)->groupBy('ResStatus')->get();
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');
      return view('property.lookuprromtype', [
         'comp' => $comp,
         'statename' => $statename,
         'fromdate' => $this->ncurdate,
         'resstatus' => $resstatus
      ]);
   }

   public function lookuproomtypefetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $resstatus = $request->input('resstatus');
      $todate = date('Y-m-d', strtotime($fromdate . ' +21 days'));

      $roomcategories = RoomCat::select('cat_code', 'name', 'norooms')
         ->where('propertyid', $this->propertyid)
         ->where('inclcount', 'y')
         ->orderBy('name')
         ->get();

      $totalrooms = RoomCat::where('propertyid', $this->propertyid)
         ->where('inclcount', 'y')
         ->sum('norooms');

      // Batch the per-day busy-room lookups (was 2 queries per category x day).
      // Fetch every booking/occupancy row that could intersect the window once,
      // then compute the per-day busy count in memory.
      $grpRows = GrpBookinDetail::where('Property_ID', $this->propertyid)
         ->where('ContraDocId', '')
         ->where('Cancel', 'N')
         ->whereDate('ArrDate', '<=', $todate)
         ->whereDate('DepDate', '>', $fromdate)
         ->select('RoomCat', 'ArrDate', 'DepDate', 'RoomDet')
         ->get();

      $occRows = RoomOcc::where('propertyid', $this->propertyid)
         ->where('roomtype', 'ro')
         ->whereNull('type')
         ->whereDate('chkindate', '<=', $todate)
         ->whereDate('depdate', '>', $fromdate)
         ->select('roomcat', 'chkindate', 'depdate')
         ->get();

      $results = [];

      foreach ($roomcategories as $category) {
         $dailyBusyCounts = [];
         $currentDate = $fromdate;

         // In-memory busy counts for this category (date-window overlap).
         $grpForCat = $grpRows->where('RoomCat', $category->cat_code);
         $occForCat = $occRows->where('roomcat', $category->cat_code);

         while (strtotime($currentDate) <= strtotime($todate)) {
            $norooms = $category->norooms;

            $busyrooms_grp = $grpForCat
               ->filter(function ($g) use ($currentDate) {
                  return substr($g->ArrDate, 0, 10) <= $currentDate
                     && substr($g->DepDate, 0, 10) > $currentDate;
               })
               ->sum('RoomDet');

            $busyrooms_occ = $occForCat
               ->filter(function ($o) use ($currentDate) {
                  return substr($o->chkindate, 0, 10) <= $currentDate
                     && substr($o->depdate, 0, 10) > $currentDate;
               })
               ->count();

            $dailyBusyCounts[$currentDate] = $norooms - ($busyrooms_grp + $busyrooms_occ);
            $currentDate = date('Y-m-d', strtotime($currentDate . ' +1 day'));
         }

         $results[] = [
            'category' => $category->name,
            'cat_code' => $category->cat_code,
            'daily_busy_counts' => $dailyBusyCounts,
            'busyrooms_grp' => $busyrooms_grp,
            'busyrooms_occ' => $busyrooms_occ
         ];
      }

      $data = [
         'roomcategories' => $results,
         'totalrooms' => $totalrooms,
      ];

      return response()->json($data);
   }

   public function nckotreport(Request $request)
   {
      $permission = revokeopen(171715);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      // Fetching the valid outlets for the given property ID 
      $Outlets = DB::table('depart')
         ->select('dcode', 'Name')
         ->where('propertyid', $this->propertyid)
         ->whereIn('Rest_Type', ['Outlet', 'Room Service'])
         ->where('pos', 'Y')
         ->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.nckotreport', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'outlets' => $Outlets,
         'statename' => $statename
      ]);
   }
   public function nckotreportfetch(Request $request)
   {
      $fromdate = $request->fromdate;

      $todate = $request->todate;
      $outlet = $request->input('outlet'); // $request->outlet;

      $kotdata = DB::table('kot AS K')
         ->leftJoin('sale1 AS S', 'K.contradocid', '=', 'S.docid')
         ->leftJoin('depart AS D', 'K.restcode', '=', 'D.dcode')
         ->leftJoin('itemmast AS I', function ($join) {
            $join->on('K.item', '=', 'I.Code')
               ->on('K.itemrestcode', '=', 'I.RestCode');
         })
         ->leftJoin('nctype_mast AS nc', 'K.nctype', '=', 'nc.ncode')
         ->select([
            'K.vno AS KOTNO',
            'K.vdate',
            'K.vtime',
            'nc.ncode',
            'K.restcode',
            'K.roomno',
            'K.qty',
            'K.rate',
            'K.amount',
            'D.name as DEPARTNAME',
            'I.Name as ITEMNAME',
            'K.ncreason AS Reason',
            'K.voidyn AS VoidYN',
            'K.u_name AS UserName',
         ])
         ->where('K.propertyid', $this->propertyid)
         ->where('K.voidyn', 'N')
         ->where('K.nckot', 'Y')
         ->whereBetween('K.VDate', [$fromdate, $todate])
         ->whereIn('K.restcode', $outlet)
         ->orderBy('K.restcode')
         ->orderBy('K.vdate')
         ->orderBy('K.vno')
         ->orderBy('nc.ncode')
         ->get();

      return json_encode($kotdata);
   }

   public function advresreport(Request $request)
   {
      $permission = revokeopen(131213);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.advresreport', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }

   public function advresreportfetch(Request $request)
   {
      // Validate inputs
      $request->validate([
         'fromdate' => 'required|date',
         'todate' => 'required|date'
      ]);

      $fromdate = $request->fromdate;
      $todate = $request->todate;


      $bookingData = DB::table('booking AS B')
         ->leftJoin('guestprof AS GF', 'B.guestprof', '=', 'GF.guestcode')
         ->leftJoin('grpbookingdetails AS G', 'B.DocId', '=', 'G.BookingDocid')
         ->leftJoin('paycharge AS PC', function ($join) {
            $join->on('B.DocId', '=', 'PC.refdocid')
               ->whereIn('PC.vtype', ['ARRES', 'ADRES'])
               ->where('PC.propertyid', $this->propertyid);
         })
         ->leftJoin('revmast AS RM', 'PC.paycode', '=', 'RM.rev_code')
         ->leftJoin('subgroup AS SU', 'B.Company', '=', 'SU.sub_code')
         ->leftJoin('subgroup AS ST', 'B.TravelAgency', '=', 'ST.sub_code')
         ->select([
            'B.DocId',
            'B.BookNo AS ResNo',
            'PC.vno AS Reciptno',
            'B.ResStatus AS Status',
            DB::raw("CASE 
                        WHEN PC.vtype = 'ADRES' THEN 'Advance' 
                        WHEN PC.vtype = 'ARRES' THEN 'Refund' 
                        ELSE 'Other' 
                    END AS PaymentType"),
            'B.vdate as ResDate',
            'GF.name as GuestName',
            'G.arrDate as ArrivalDate',
            'G.DepDate as Depdate',
            'PC.amtcr as Amount',
            'RM.name as PMode',
            'SU.name as Company',
            'ST.Name as TravelAgent',
            'PC.u_name',
         ])
         ->whereExists(function ($query) use ($fromdate, $todate) {
            $query->select(DB::raw(1))
               ->from('paycharge AS PC')
               ->whereColumn('PC.refdocid', 'B.DocId')
               ->whereBetween('PC.vdate', [$fromdate, $todate])
               ->where('PC.propertyid', $this->propertyid)
               ->whereIn('PC.vtype', ['ARRES', 'ADRES']);
         })
         ->groupBy('PC.vno')
         ->orderBy('PC.vdate', 'ASC')
         ->orderBy('PC.vno', 'ASC')
         ->get();

      return response()->json($bookingData);
   }

   /**
    * Advance / Folio Reconciliation Report (read-only diagnostic, mission Â§10).
    *
    * Traces each reservation's advance: received at reservation (ADRES/ARRES via
    * refdocid) -> transferred at check-in (folio paycharge rows) -> deletion
    * history (paychargelog). A non-zero Recon on a checked-in folio flags an
    * ADVANCE MISMATCH for investigation/restore. No data is modified.
    */
   public function advreconreport(Request $request)
   {
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.advreconreport', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }

   public function advreconreportfetch(Request $request)
   {
      $request->validate([
         'fromdate' => 'required|date',
         'todate' => 'required|date'
      ]);

      $fromdate = $request->fromdate;
      $todate = $request->todate;
      $pid = (int) $this->propertyid;

      $rows = DB::table('booking AS B')
         ->leftJoin('guestfolio AS GF', function ($j) use ($pid) {
            $j->on('GF.bookingdocid', '=', 'B.DocId')
               ->where('GF.propertyid', '=', $pid);
         })
         ->leftJoin('roomocc AS RO', function ($j) use ($pid) {
            $j->on('RO.docid', '=', 'GF.docid')
               ->where('RO.propertyid', '=', $pid);
         })
         ->select([
            'B.DocId',
            'B.BookNo AS ResNo',
            'B.vdate AS ResDate',
            'B.ResStatus',
            'B.Cancel',
            'B.GuestName',
            'B.NoofRooms',
            'GF.folio_no AS FolioNo',
            'GF.docid AS FolioDocid',
            'GF.vdate AS CheckInDate',
            'RO.roomno AS RoomNo',
            'RO.depdate AS CheckOutDate',
            DB::raw("(SELECT COALESCE(SUM(PC.amtcr - PC.amtdr), 0) FROM paycharge PC WHERE PC.refdocid = B.DocId AND PC.propertyid = {$pid} AND PC.vtype IN ('ADRES','ARRES')) AS ResAdvance"),
            DB::raw("(SELECT COALESCE(SUM(PC.amtcr - PC.amtdr), 0) FROM paycharge PC WHERE PC.refdocid = B.DocId AND PC.propertyid = {$pid} AND PC.folionodocid IS NOT NULL AND PC.folionodocid <> '' AND PC.vtype <> 'REV') AS FolioAdvance"),
            DB::raw("(SELECT COUNT(*) FROM paychargelog PL WHERE PL.propertyid = {$pid} AND (PL.refdocid = B.DocId OR PL.docid IN (SELECT PC2.docid FROM paycharge PC2 WHERE PC2.refdocid = B.DocId AND PC2.propertyid = {$pid} AND PC2.vtype IN ('ADRES','ARRES')))) AS DelCount"),
            DB::raw("(SELECT COALESCE(SUM(COALESCE(PL.amtcr,0) - COALESCE(PL.amtdr,0)), 0) FROM paychargelog PL WHERE PL.propertyid = {$pid} AND (PL.refdocid = B.DocId OR PL.docid IN (SELECT PC2.docid FROM paycharge PC2 WHERE PC2.refdocid = B.DocId AND PC2.propertyid = {$pid} AND PC2.vtype IN ('ADRES','ARRES')))) AS DelAmount"),
            DB::raw("(SELECT RM.name FROM paycharge PC LEFT JOIN revmast RM ON PC.paycode = RM.rev_code AND RM.propertyid = {$pid} WHERE PC.refdocid = B.DocId AND PC.propertyid = {$pid} AND PC.vtype IN ('ADRES','ARRES') ORDER BY PC.vdate, PC.sno LIMIT 1) AS PayMode"),
         ])
         ->where('B.Property_ID', $pid)
         ->whereBetween('B.vdate', [$fromdate, $todate])
         ->orderBy('B.vdate')
         ->orderBy('B.BookNo')
         ->get();

      foreach ($rows as $row) {
         $row->ResAdvance = (float) $row->ResAdvance;
         $row->FolioAdvance = (float) $row->FolioAdvance;
         $row->DelAmount = (float) $row->DelAmount;
         $row->DelCount = (int) $row->DelCount;
         $row->Recon = round($row->ResAdvance - $row->FolioAdvance - $row->DelAmount, 2);
         $checkedIn = !empty($row->FolioDocid);
         $cancelled = strtoupper((string) $row->Cancel) === 'Y';
         if ($cancelled) {
            $row->Flag = (abs($row->Recon) > 0.01) ? 'CANCELLED-CHECK' : 'CANCELLED';
            $row->FlagType = (abs($row->Recon) > 0.01) ? 'danger' : 'secondary';
         } elseif ($checkedIn) {
            if ($row->Recon > 0.01) {
               $row->Flag = 'MISMATCH';
               $row->FlagType = 'danger';
            } elseif ($row->Recon < -0.01) {
               $row->Flag = 'OVER-CREDIT';
               $row->FlagType = 'danger';
            } else {
               $row->Flag = 'OK';
               $row->FlagType = 'success';
            }
         } else {
            if ($row->ResAdvance > 0.01) {
               $row->Flag = 'PENDING-TRANSFER';
               $row->FlagType = 'warning';
            } else {
               $row->Flag = 'OK';
               $row->FlagType = 'success';
            }
         }
      }

      return response()->json($rows);
   }

   public function advreconreportdetail(Request $request)
   {
      $docid = $request->docid;
      $pid = (int) $this->propertyid;
      if (empty($docid)) {
         return response()->json(['status' => false, 'message' => 'Missing reservation DocId']);
      }

      $booking = DB::table('booking')->where('Property_ID', $pid)->where('DocId', $docid)->first();
      if (!$booking) {
         return response()->json(['status' => false, 'message' => 'Reservation not found']);
      }

      $resAdvanceRows = DB::table('paycharge AS PC')
         ->leftJoin('revmast AS RM', function ($j) use ($pid) {
            $j->on('PC.paycode', '=', 'RM.rev_code')->where('RM.propertyid', '=', $pid);
         })
         ->select('PC.*', 'RM.name AS PayModeName')
         ->where('PC.propertyid', $pid)
         ->where('PC.refdocid', $docid)
         ->whereIn('PC.vtype', ['ADRES', 'ARRES'])
         ->orderBy('PC.vdate')->orderBy('PC.sno')
         ->get();

      $folioAdvanceRows = DB::table('paycharge AS PC')
         ->leftJoin('revmast AS RM', function ($j) use ($pid) {
            $j->on('PC.paycode', '=', 'RM.rev_code')->where('RM.propertyid', '=', $pid);
         })
         ->select('PC.*', 'RM.name AS PayModeName')
         ->where('PC.propertyid', $pid)
         ->where('PC.refdocid', $docid)
         ->whereNotNull('PC.folionodocid')
         ->where('PC.folionodocid', '<>', '')
         ->where('PC.vtype', '<>', 'REV')
         ->orderBy('PC.vdate')->orderBy('PC.sno')
         ->get();

      $logRows = DB::table('paychargelog')
         ->where('propertyid', $pid)
         ->where(function ($q) use ($docid, $pid) {
            $q->where('refdocid', $docid)
               ->orWhereIn('docid', DB::table('paycharge')->where('propertyid', $pid)->where('refdocid', $docid)->whereIn('vtype', ['ADRES', 'ARRES'])->pluck('docid'));
         })
         ->orderByDesc('u_entdt')
         ->get();

      $folios = DB::table('guestfolio')->where('propertyid', $pid)->where('bookingdocid', $docid)->get();
      $rooms = DB::table('roomocc')->where('propertyid', $pid)->whereIn('docid', $folios->pluck('docid'))->get();

      return response()->json([
         'status' => true,
         'booking' => $booking,
         'res_advance' => $resAdvanceRows,
         'folio_advance' => $folioAdvanceRows,
         'log' => $logRows,
         'folios' => $folios,
         'rooms' => $rooms,
      ]);
   }

   /**
    * Safe Restore / Re-post of a missing folio advance (mission Â§10).
    *
    * Reposts ONLY the missing difference (ResAdvance - FolioAdvance - Deleted)
    * onto the existing folio, so a payment is NEVER duplicated. Guards:
    *  - booking must exist, not cancelled, and be checked-in (folio present)
    *  - folio must NOT be settled (roomocc type='O' or settled paycharge)
    *  - missing amount must be > 0 (after a restore it becomes ~0, blocking re-run)
    *  - a reservation advance row must exist to copy payment mode
    *  - CHK voucher prefix must exist for the current date
    * Writes a paychargelog audit row inside the same transaction.
    */
   public function advreconrestore(Request $request)
   {
      $docid = $request->docid;
      $pid = (int) $this->propertyid;
      if (empty($docid)) {
         return response()->json(['status' => false, 'message' => 'Missing reservation DocId']);
      }

      $booking = DB::table('booking')->where('Property_ID', $pid)->where('DocId', $docid)->first();
      if (!$booking) {
         return response()->json(['status' => false, 'message' => 'Reservation not found']);
      }
      if (strtoupper((string) $booking->Cancel) === 'Y') {
         return response()->json(['status' => false, 'message' => 'Cannot restore advance on a cancelled reservation']);
      }

      $folio = DB::table('guestfolio')->where('propertyid', $pid)->where('bookingdocid', $docid)->first();
      if (!$folio) {
         return response()->json(['status' => false, 'message' => 'Reservation is not checked-in â€” no folio to restore to']);
      }
      $folioDocid = $folio->docid;
      $folioNo = $folio->folio_no;

      // Refuse if the folio is already settled / guest checked out
      $checkedOut = DB::table('roomocc')->where('propertyid', $pid)->where('docid', $folioDocid)->where('type', 'O')->exists();
      $settled = DB::table('paycharge')->where('propertyid', $pid)->where('folionodocid', $folioDocid)->whereNotNull('settledate')->exists();
      if ($checkedOut || $settled) {
         return response()->json(['status' => false, 'message' => 'Folio is settled / guest checked-out â€” restore not allowed']);
      }

      // Amounts (same semantics as advreconreportfetch)
      $resAdvance = (float) DB::table('paycharge')
         ->where('propertyid', $pid)->where('refdocid', $docid)->whereIn('vtype', ['ADRES', 'ARRES'])
         ->selectRaw('COALESCE(SUM(amtcr - amtdr), 0) AS total')->value('total');
      $folioAdvance = (float) DB::table('paycharge')
         ->where('propertyid', $pid)->where('refdocid', $docid)
         ->whereNotNull('folionodocid')->where('folionodocid', '<>', '')->where('vtype', '<>', 'REV')
         ->selectRaw('COALESCE(SUM(amtcr - amtdr), 0) AS total')->value('total');
      $delAmount = (float) DB::table('paychargelog')
         ->where('propertyid', $pid)
         ->where(function ($q) use ($docid, $pid) {
            $q->where('refdocid', $docid)
               ->orWhereIn('docid', DB::table('paycharge')->where('propertyid', $pid)->where('refdocid', $docid)->whereIn('vtype', ['ADRES', 'ARRES'])->pluck('docid'));
         })
         ->selectRaw('COALESCE(SUM(COALESCE(amtcr,0) - COALESCE(amtdr,0)), 0) AS total')->value('total');

      $missing = round($resAdvance - $folioAdvance - $delAmount, 2);
      if ($missing <= 0.01) {
         return response()->json(['status' => false, 'message' => 'No missing advance to restore (already balanced) â€” nothing to re-post']);
      }

      $advRow = DB::table('paycharge')
         ->where('propertyid', $pid)->where('refdocid', $docid)->where('vtype', 'ADRES')
         ->orderBy('vdate')->orderBy('sno')->first();
      if (!$advRow) {
         return response()->json(['status' => false, 'message' => 'No reservation advance row found to copy payment mode']);
      }

      // Next CHK voucher number
      $voucherPrefix = DB::table('voucher_prefix')
         ->where('propertyid', $pid)->where('v_type', 'CHK')
         ->whereDate('date_from', '<=', $this->ncurdate)->whereDate('date_to', '>=', $this->ncurdate)
         ->first();
      if (!$voucherPrefix) {
         return response()->json(['status' => false, 'message' => 'CHK voucher prefix not found for current date']);
      }
      $vno = (int) $voucherPrefix->start_srl_no + 1;
      $vprefix = $voucherPrefix->prefix;

      // Rebuild a voucher docid with the SAME format/separators as the folio docid
      $newDocid = preg_replace('/\d+$/', (string) $vno, $folioDocid);
      if ($newDocid === $folioDocid) {
         $newDocid = $pid . 'CHK' . ' ' . $vprefix . ' ' . $vno;
      }

      $room = DB::table('roomocc')->where('propertyid', $pid)->where('docid', $folioDocid)->first();
      $maxSno = (int) DB::table('paycharge')->where('propertyid', $pid)->where('folionodocid', $folioDocid)->max('sno');

      DB::beginTransaction();
      try {
         // Duplicate guard inside the transaction: another restore may have slipped in
         $dup = DB::table('paycharge')->where('propertyid', $pid)->where('docid', $newDocid)->exists();
         if ($dup) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => 'Voucher docid collision â€” restore aborted (no duplicate created)']);
         }

         DB::table('paycharge')->insert([
            'propertyid' => $pid,
            'docid' => $newDocid,
            'sno' => $maxSno + 1,
            'sno1' => $advRow->sno1 ?? 1,
            'vtype' => 'CHK',
            'vno' => $vno,
            'vprefix' => $vprefix,
            'vdate' => $this->ncurdate,
            'vtime' => date('H:i:s'),
            'paycode' => $advRow->paycode,
            'paytype' => $advRow->paytype,
            'comments' => trim(($advRow->comments ?? '') . ' [ADVANCE RESTORED via reconciliation ' . date('d-m-Y H:i') . ']'),
            'guestprof' => $folio->guestprof,
            'comp_code' => '',
            'travel_agent' => '',
            'roomno' => $room->roomno ?? '',
            'amtcr' => $missing,
            'amtdr' => 0.00,
            'roomcat' => $room->roomcat ?? '',
            'roomtype' => 'RO',
            'restcode' => 'FOM' . $pid,
            'billamount' => $missing,
            'taxper' => 0,
            'onamt' => 0,
            'taxstru' => '',
            'taxcondamt' => 0,
            'foliono' => $folioNo,
            'folionodocid' => $folioDocid,
            'refdocid' => $docid,
            'u_entdt' => $this->currenttime,
            'u_name' => Auth::user()->u_name ?? Auth::user()->name,
            'u_ae' => 'a',
         ]);

         DB::table('voucher_prefix')
            ->where('propertyid', $pid)->where('v_type', 'CHK')->where('prefix', $vprefix)
            ->increment('start_srl_no');

         // Audit trail: this restore is itself recorded in paychargelog
         PayChargeLogService::store([
            'propertyid' => $pid,
            'docid' => $newDocid,
            'sno' => $maxSno + 1,
            'vtype' => 'CHK',
            'vno' => $vno,
            'vprefix' => $vprefix,
            'vdate' => $this->ncurdate,
            'vtime' => date('H:i:s'),
            'paycode' => $advRow->paycode,
            'paytype' => $advRow->paytype,
            'comments' => 'Advance restored to folio ' . $folioNo . ' (missing Rs ' . number_format($missing, 2) . ')',
            'guestprof' => $folio->guestprof,
            'roomno' => $room->roomno ?? '',
            'amtcr' => $missing,
            'amtdr' => 0.00,
            'foliono' => $folioNo,
            'folionodocid' => $folioDocid,
            'refdocid' => $docid,
            'restcode' => 'FOM' . $pid,
            'remarks' => 'ADVANCE RESTORED via reconciliation by ' . (Auth::user()->u_name ?? Auth::user()->name) . ' â€” original reservation ' . ($booking->BookNo ?? $docid),
            'u_entdt' => $this->currenttime,
            'u_name' => Auth::user()->u_name ?? Auth::user()->name,
            'u_ae' => 'a',
         ]);

         DB::commit();
      } catch (Exception $e) {
         DB::rollBack();
         Log::error('advreconrestore failed: ' . $e->getMessage());
         return response()->json(['status' => false, 'message' => 'Restore failed: ' . $e->getMessage()]);
      }

      return response()->json([
         'status' => true,
         'message' => 'Advance of Rs ' . number_format($missing, 2) . ' restored to folio ' . $folioNo . ' (audit written)',
      ]);
   }


   /**
    * Front Office mismatch diagnostics (read-only). One page, tabbed queries:
    * noshow | orphanrooms | folionoroom | cancelledfolio | resvfolio | settlement.
    * No data is modified.
    */
   public function fodiagnostics(Request $request)
   {
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.fodiagnostics', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }

   public function fodiagnosticsfetch(Request $request)
   {
      $tab = $request->tab ?? 'noshow';
      $pid = (int) $this->propertyid;

      switch ($tab) {
         case 'noshow':
            $rows = DB::table('booking AS B')
               ->leftJoin('grpbookingdetails AS G', 'B.DocId', '=', 'G.BookingDocid')
               ->select([
                  'B.BookNo AS ResNo', 'B.DocId', 'B.GuestName', 'B.ResStatus', 'B.vdate AS ResDate',
                  'G.ArrDate', 'G.DepDate', 'G.RoomNo AS BookedRoom', 'G.RoomCat AS BookedCat', 'G.Tarrif AS BookedRate',
                  DB::raw("(SELECT COALESCE(SUM(PC.amtcr - PC.amtdr),0) FROM paycharge PC WHERE PC.refdocid = B.DocId AND PC.propertyid = {$pid} AND PC.vtype IN ('ADRES','ARRES')) AS Advance"),
               ])
               ->where('B.Property_ID', $pid)
               ->where('B.Cancel', '<>', 'Y')
               ->whereDate('G.ArrDate', '<', $this->ncurdate)
               ->whereNotExists(function ($q) {
                  $q->select(DB::raw(1))->from('guestfolio')->whereColumn('bookingdocid', 'B.DocId');
               })
               ->orderBy('G.ArrDate')
               ->limit(500)
               ->get();
            break;

         case 'orphanrooms':
            $rows = DB::table('roomocc AS RO')
               ->leftJoin('guestfolio AS GF', 'RO.docid', '=', 'GF.docid')
               ->select('RO.docid', 'RO.roomno', 'RO.roomcat', 'RO.guestprof', 'RO.chkindate', 'RO.depdate', 'RO.type', 'RO.u_name', 'RO.u_entdt')
               ->where('RO.propertyid', $pid)
               ->whereNull('GF.docid')
               ->orderByDesc('RO.u_entdt')
               ->limit(500)
               ->get();
            break;

         case 'folionoroom':
            $rows = DB::table('guestfolio AS GF')
               ->leftJoin('roomocc AS RO', 'RO.docid', '=', 'GF.docid')
               ->select('GF.docid', 'GF.folio_no', 'GF.name', 'GF.vdate', 'GF.bookingdocid', 'GF.guestprof', 'GF.Company')
               ->where('GF.propertyid', $pid)
               ->whereNull('RO.docid')
               ->orderByDesc('GF.vdate')
               ->limit(500)
               ->get();
            break;

         case 'cancelledfolio':
            $rows = DB::table('guestfolio AS GF')
               ->join('booking AS B', 'GF.bookingdocid', '=', 'B.DocId')
               ->select('B.BookNo AS ResNo', 'B.DocId', 'B.ResStatus', 'B.CancelDate', 'B.CancelUName', 'GF.folio_no', 'GF.docid AS FolioDocid', 'GF.name', 'GF.vdate AS FolioDate')
               ->where('GF.propertyid', $pid)
               ->where('B.Cancel', 'Y')
               ->orderByDesc('GF.vdate')
               ->limit(500)
               ->get();
            break;

         case 'resvfolio':
            $rows = DB::table('roomocc AS RO')
               ->join('grpbookingdetails AS G', 'RO.docid', '=', 'G.ContraDocId')
               ->join('guestfolio AS GF', 'GF.docid', '=', 'RO.docid')
               ->join('booking AS B', 'B.DocId', '=', 'GF.bookingdocid')
               ->select([
                  'B.BookNo AS ResNo', 'B.DocId', 'B.GuestName', 'GF.folio_no AS FolioNo',
                  'RO.roomno AS OccRoom', 'G.RoomNo AS BookedRoom', 'RO.newroomno AS NewRoom',
                  'G.RoomCat AS BookedCat', 'RO.roomcat AS OccCat',
                  'G.Tarrif AS BookedRate', 'RO.roomrate AS OccRate',
                  'G.Plan_Code AS BookedPlan', 'RO.plancode AS OccPlan',
                  'G.ArrDate', 'G.DepDate', 'RO.chkindate AS OccInDate', 'RO.depdate AS OccOutDate',
                  'B.Company', 'B.TravelAgency', 'B.BussSource', 'GF.Company AS FolCompany', 'GF.travelagent AS FolAgent',
               ])
               ->where('RO.propertyid', $pid)
               ->where(function ($q) {
                  $q->where(function ($q2) {
                     $q2->where('G.RoomNo', '<>', '')->whereColumn('RO.roomno', '<>', 'G.RoomNo')->whereNull('RO.newroomno');
                  })->orWhere(function ($q2) {
                     $q2->where('G.RoomCat', '<>', '')->whereColumn('RO.roomcat', '<>', 'G.RoomCat');
                  })->orWhere(function ($q2) {
                     $q2->where('G.Tarrif', '>', 0)->where('RO.roomrate', '>', 0)->whereColumn('RO.roomrate', '<>', 'G.Tarrif');
                  })->orWhere(function ($q2) {
                     $q2->where('G.Plan_Code', '<>', '')->where('RO.plancode', '<>', '')->whereColumn('RO.plancode', '<>', 'G.Plan_Code');
                  })->orWhere(function ($q2) {
                     $q2->whereNotNull('G.DepDate')->whereNotNull('RO.depdate')->whereRaw('ABS(DATEDIFF(RO.depdate, G.DepDate)) > 2');
                  });
               })
               ->orderBy('RO.u_entdt', 'desc')
               ->limit(500)
               ->get();

            foreach ($rows as $row) {
               $row->RoomMismatch = (!empty($row->BookedRoom) && $row->OccRoom !== $row->BookedRoom && empty($row->NewRoom)) ? 'Y' : '';
               $row->CatMismatch = (!empty($row->BookedCat) && $row->OccCat !== $row->BookedCat) ? 'Y' : '';
               $row->RateMismatch = ((float) $row->BookedRate > 0 && (float) $row->OccRate > 0 && (float) $row->OccRate !== (float) $row->BookedRate) ? 'Y' : '';
               $row->PlanMismatch = (!empty($row->BookedPlan) && !empty($row->OccPlan) && $row->OccPlan !== $row->BookedPlan) ? 'Y' : '';
               $row->DepMismatch = (!empty($row->DepDate) && !empty($row->OccOutDate) && abs(strtotime($row->OccOutDate) - strtotime($row->DepDate)) > 2 * 86400) ? 'Y' : '';
               $row->CarryMismatch = ((!empty($row->Company) && empty($row->FolCompany)) || (!empty($row->TravelAgency) && empty($row->FolAgent))) ? 'Y' : '';
            }
            break;

         case 'settlement':
            $rows = DB::table('guestfolio AS GF')
               ->join('roomocc AS RO', 'RO.docid', '=', 'GF.docid')
               ->select([
                  'GF.docid', 'GF.folio_no AS FolioNo', 'GF.name', 'RO.roomno', 'RO.chkoutdate',
                  DB::raw("(SELECT COALESCE(SUM(PC.amtdr - PC.amtcr),0) FROM paycharge PC WHERE PC.folionodocid = GF.docid AND PC.settledate IS NULL) AS OpenBalance"),
               ])
               ->where('GF.propertyid', $pid)
               ->where('RO.type', 'O')
               ->havingRaw('ABS(OpenBalance) > 0.01')
               ->orderByDesc('RO.chkoutdate')
               ->limit(500)
               ->get();
            break;

         default:
            return response()->json(['status' => false, 'message' => 'Unknown tab']);
      }

      return response()->json(['status' => true, 'tab' => $tab, 'data' => $rows]);
   }

   public function roomrecon(Request $request)
   {
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.roomrecon', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }

   /**
    * Room Management reconciliation â€” read-only diagnostics.
    * Compares RoomOcc / GuestFolio / PayCharge / room_mast / roomblockout
    * against the invariants the legacy HMS maintained. No data mutation.
    */
   public function roomreconfetch(Request $request)
   {
      $tab = $request->tab ?? 'orphanocc';
      $pid = (int) $this->propertyid;

      switch ($tab) {
         // 1. Active RoomOcc whose folio docid has no GuestFolio row (orphan occupancy)
         case 'orphanocc':
            $rows = DB::table('roomocc AS RO')
               ->leftJoin('guestfolio AS GF', 'RO.docid', '=', 'GF.docid')
               ->select('RO.docid', 'RO.roomno', 'RO.roomcat', 'RO.guestprof', 'RO.chkindate', 'RO.depdate', 'RO.type', 'RO.u_name', 'RO.u_entdt')
               ->where('RO.propertyid', $pid)
               ->whereNull('GF.docid')
               ->orderByDesc('RO.u_entdt')
               ->limit(500)
               ->get();
            break;

         // 2. RoomOcc rows pointing at a room missing from room_mast (deleted room / wrong code)
         case 'noroominmast':
            $rows = DB::table('roomocc AS RO')
               ->leftJoin('room_mast AS RM', function ($j) {
                  $j->on('RM.rcode', '=', 'RO.roomno')->on('RM.propertyid', '=', 'RO.propertyid');
               })
               ->select('RO.docid', 'RO.roomno', 'RO.roomcat', 'RO.type', 'RO.chkindate', 'RO.depdate', 'RO.propertyid AS roprop', 'RO.u_name', 'RO.u_entdt')
               ->where('RO.propertyid', $pid)
               ->where(function ($q) {
                  $q->whereNull('RM.rcode')->orWhere('RO.roomno', '');
               })
               ->orderByDesc('RO.u_entdt')
               ->limit(500)
               ->get();
            break;

         // 3. GuestFolio rows with NO RoomOcc row (folio without a room)
         case 'folionoroom':
            $rows = DB::table('guestfolio AS GF')
               ->leftJoin('roomocc AS RO', 'RO.docid', '=', 'GF.docid')
               ->select('GF.docid', 'GF.folio_no', 'GF.name', 'GF.vdate', 'GF.bookingdocid', 'GF.guestprof', 'GF.Company')
               ->where('GF.propertyid', $pid)
               ->whereNull('RO.docid')
               ->orderByDesc('GF.vdate')
               ->limit(500)
               ->get();
            break;

         // 4. Active RoomOcc folios with NO paycharge at all (checked-in, no charges posted)
         case 'nopaycharge':
            $rows = DB::table('roomocc AS RO')
               ->leftJoin('paycharge AS PC', 'PC.folionodocid', '=', 'RO.docid')
               ->select('RO.docid', 'RO.roomno', 'RO.guestprof', 'RO.chkindate', 'RO.depdate')
               ->where('RO.propertyid', $pid)
               ->where(function ($q) {
                  $q->whereNull('RO.type')->orWhere('RO.type', '');
               })
               ->whereNull('PC.sn')
               ->where('RO.chkindate', '>=', '2026-01-01')
               ->orderBy('RO.chkindate', 'desc')
               ->limit(500)
               ->get();
            break;

         // 5. Occupied rooms vs room_mast.room_stat (occupied room must not be Clean 'C')
         case 'occstat':
            $rows = DB::table('room_mast AS RM')
               ->join('roomocc AS RO', function ($j) {
                  $j->on('RO.roomno', '=', 'RM.rcode')->on('RO.propertyid', '=', 'RM.propertyid');
               })
               ->select('RM.rcode AS RoomNo', 'RM.room_stat AS MastStat', 'RM.name AS RoomName', 'RO.docid', 'RO.chkindate', 'RO.depdate')
               ->where('RM.propertyid', $pid)
               ->where('RM.type', 'RO')
               ->where(function ($q) {
                  $q->whereNull('RO.type')->orWhere('RO.type', '');
               })
               ->where('RM.room_stat', '<>', 'D')
               ->orderBy('RM.rcode')
               ->limit(500)
               ->get();
            break;

         // 6. Blocked (OOO/Maint) rooms that are simultaneously occupied â€” must never happen
         case 'blockedoccupied':
            $rows = DB::table('roomblockout AS RB')
               ->join('roomocc AS RO', function ($j) {
                  $j->on('RO.roomno', '=', 'RB.roomcode')->on('RO.propertyid', '=', 'RB.propertyid');
               })
               ->select('RB.roomcode AS RoomNo', 'RB.block', 'RB.reasons', 'RB.fromdate', 'RB.todate', 'RB.cleardate', 'RO.docid', 'RO.chkindate', 'RO.depdate')
               ->where('RB.propertyid', $pid)
               ->whereIn('RB.type', ['O', 'M'])
               ->whereNull('RB.cleardate')
               ->where(function ($q) {
                  $q->whereNull('RO.type')->orWhere('RO.type', '');
               })
               ->orderBy('RB.roomcode')
               ->limit(500)
               ->get();
            break;

         // 7. roomblockout rows still open past their todate (stale blocks)
         case 'staleblock':
            $rows = DB::table('roomblockout AS RB')
               ->select('RB.roomcode AS RoomNo', 'RB.block', 'RB.reasons', 'RB.fromdate', 'RB.todate', 'RB.type', 'RB.cleardate', 'RB.u_name', 'RB.u_entdt')
               ->where('RB.propertyid', $pid)
               ->whereNull('RB.cleardate')
               ->where('RB.todate', '<', $this->ncurdate)
               ->orderBy('RB.todate')
               ->limit(500)
               ->get();
            break;

         // 8. Extra bed rooms (rate/stock reconciliation: extrabed on RoomOcc)
         case 'extrabed':
            $rows = DB::table('roomocc AS RO')
               ->select('RO.docid', 'RO.roomno', 'RO.extrabed', 'RO.adult', 'RO.children', 'RO.chkindate', 'RO.depdate', 'RO.u_entdt')
               ->where('RO.propertyid', $pid)
               ->where('RO.extrabed', '>', 0)
               ->orderByDesc('RO.u_entdt')
               ->limit(500)
               ->get();
            break;

         default:
            return response()->json(['status' => false, 'message' => 'Unknown tab']);
      }

      return response()->json(['status' => true, 'tab' => $tab, 'data' => $rows]);
   }

   public function expectedcheckout(Request $request)
   {
      $permission = revokeopen(141216);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.expectedcheckout', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }
   public function expectedcheckoutfetch(Request $request)
   {
      $fromdate = $request->fromdate;

      $todate = $request->todate;
      $checkoutData =  DB::table('guestfolio')
         ->select([
            'guestfolio.docid',
            'guestfolio.folio_no as FolioNo',
            'roomocc.roomno',
            'guestfolio.name',
            'roomocc.adult as PAX',
            'roomocc.deptime as ExpTime',
            'guestfolio.vdate as ChechINDate',
            DB::raw("CONCAT(roomocc.depdate, ' ', roomocc.deptime) AS Depdate"),
            'subgroup.name as CompanyName'
         ])
         ->join('roomocc', 'guestfolio.docid', '=', 'roomocc.docid')
         ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'guestfolio.Company')
         ->where('guestfolio.propertyid', $this->propertyid)
         ->whereNull('roomocc.chkoutdate')
         ->whereBetween('roomocc.depdate', [$fromdate, $todate])
         ->orderBy('roomocc.roomno')
         ->get();


      return json_encode($checkoutData);
   }

   public function focc_report(Request $request)
   {
      $permission = revokeopen(191211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.focc_report', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }

   public function foccamount(Request $request)
   {
      $date = $request->date;
      $focc = Focc::where('propertyid', $this->propertyid)->where('vdate', $date)->first();

      return response()->json([$focc]);
   }

   public function foccreportprint(Request $request)
   {
      $permission = revokeopen(191211);
      if (is_null($permission) || $permission->print == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');

      return view('property.foccreportprint', [
         'comp' => $comp,
         'statename' => $statename
      ]);
   }


   public function focc_reportfetch(Request $request)
   {
      $fordate = $request->input('fordate');
      $interestamount = $request->input('interestamount');

      $invenv = EnviroGeneral::where('propertyid', $this->propertyid)->first();
      if (!$invenv) {
         return response()->json([
            'status' => 'error',
            'message' => 'Please Define Enviro Inventory First'
         ]);
      }

      if ($interestamount != '0') {
         $chkfocc = Focc::where('propertyid', $this->propertyid)->where('vdate', $fordate)->first();

         if ($chkfocc) {
            $data = [
               'interestamount' => $interestamount,
               'u_updatedt' => $this->currenttime
            ];

            Focc::where('propertyid', $this->propertyid)->where('vdate', $fordate)->update($data);
         } else {
            $data = [
               'vdate' => $fordate,
               'propertyid' => $this->propertyid,
               'interestamount' => $interestamount,
               'u_entdt' => $this->currenttime
            ];

            Focc::insert($data);
         }
      }

      $frontofcsum = 0.00;
      $outletsum = 0.00;
      $banquetsum = 0.00;
      $miscolsum = 0.00;
      $misexpsum = 0.00;
      $compsum = 0.00;
      $othersum = 0.00;

      $fomoffice = DB::table('paycharge AS P')
         ->select([
            'P.docid',
            'G.name AS GuestName',
            'P.vtype',
            'P.vno AS RectNo',
            'FO.billno AS Billno',
            'P.foliono AS FolioNo',
            'P.folionodocid',
            'P.roomno AS Roomno',
            'P.paytype',
            DB::raw('CASE WHEN P.amtcr = 0.00 THEN -P.amtdr ELSE P.amtcr END AS Amount')
         ])
         ->leftJoin('revmast AS PY', function ($join) {
            $join->on('P.paycode', '=', 'PY.rev_code')
               ->where('PY.field_type', '=', 'P')
               ->where('PY.propertyid', $this->propertyid);
         })
         ->leftJoin('guestprof AS G', function ($join) {
            $join->on('P.guestprof', '=', 'G.guestcode')
               ->where('G.propertyid', $this->propertyid);;
         })
         ->leftJoin('fombilldetails AS FO', function ($join) {
            $join->on('P.folionodocid', '=', 'FO.folionodocid')
               ->where('FO.propertyid', $this->propertyid);
         })
         ->whereDate('P.vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('P.restcode', 'FOM' . $this->propertyid)
         ->whereIn('P.paytype', ['Cash', 'Cash In Hand'])
         ->whereIn('P.vtype', ['ADRES', 'REC', 'REV'])
         ->groupBy([
            'P.vno',
            'P.vtype',
         ])
         ->orderBy('P.folionodocid')
         ->orderBy('P.foliono')
         ->orderBy('P.vtype')
         ->orderBy('P.vno')
         ->get();

      $reportdata = [];

      foreach ($fomoffice as $row) {
         $frontofcsum += $row->Amount;
         $reportdata[] = [
            'frontoffice' => 'Y',
            'guestname' => $row->GuestName,
            'rectno' => $row->RectNo,
            'foliono' => $row->FolioNo,
            'billno' => $row->Billno,
            'roomno' => $row->Roomno,
            'amount' => $row->Amount
         ];
      }

      $depart = Depart::where('propertyid', $this->propertyid)
         ->whereIn('rest_type', ['Room Service', 'Outlet'])
         ->orderBy('name', 'ASC')
         ->get();

      $departCodes = $depart->pluck('dcode')->toArray();

      $pos = DB::table('sale1 as S')
         ->selectRaw('
        MAX(S.docid) AS saledocid,
        SUM(S.netamt) AS TotalNetAmount,
        MAX(PC.paycode) AS MaxPayCode,
        MAX(PC.paytype) AS MaxPayType,
        MAX(D.name) AS DepartName,
        SUM(PC.amtcr) - SUM(PC.amtdr) AS Amount
    ')
         ->leftJoin('paycharge as PC', function ($join) use ($fordate, $departCodes) {
            $join->on('S.docid', '=', 'PC.docid')
               ->where('PC.paytype', '=', 'Cash')
               ->whereNotIn('PC.paycode', ['TOUT' . $this->propertyid])
               ->whereIn('PC.restcode', $departCodes)
               ->where('PC.vdate', '=', $fordate);
         })
         ->leftJoin('depart as D', 'S.restcode', '=', 'D.dcode')
         ->where('S.propertyid', '=', $this->propertyid)
         ->groupBy('S.restcode')
         ->get();

      foreach ($pos as $row) {
         $outletsum += $row->Amount;
         $reportdata[] = [
            'pos' => 'Y',
            'outlet' => $row->DepartName,
            'amount' => $row->Amount,
            'docid' => $row->saledocid
         ];
      }

      $banquet = DB::table('hallsale1 as S')
         ->selectRaw('
         MAX(S.docid) AS banqdocid,
         S.vno as billno,
         S.party,
         SUM(S.netamt) AS TotalNetAmount,
         MAX(PC.paycode) AS MaxPayCode,
         MAX(PC.paytype) AS MaxPayType,
         MAX(D.name) AS DepartName,
         SUM(PC.amtcr) - SUM(PC.amtdr) AS Amount
      ')
         ->leftJoin('paychargeh as PC', function ($join) use ($fordate, $departCodes) {
            $join->on('S.docid', '=', 'PC.docid')
               ->where('PC.paytype', '=', 'Cash')
               ->whereNotIn('PC.paycode', ['TOUT' . $this->propertyid])
               ->where('PC.restcode', "BANQ$this->propertyid")
               ->where('PC.vdate', '=', $fordate);
         })
         ->leftJoin('depart as D', 'S.restcode', '=', 'D.dcode')
         ->where('S.propertyid', '=', $this->propertyid)
         ->groupBy('S.docid')
         ->get();

      foreach ($banquet as $row) {
         $banquetsum += $row->Amount;
         $reportdata[] = [
            'banquet' => 'Y',
            'outlet' => $row->party . ' (Settlement)',
            'amount' => $row->Amount,
            'docid' => $row->banqdocid,
            'billno' => $row->billno
         ];
      }

      $banquet2 = DB::table('hallbook as S')
         ->selectRaw('
         MAX(S.docid) AS banqdocid,
         S.vno as billno,
         S.partyname,
         MAX(PC.paycode) AS MaxPayCode,
         MAX(PC.paytype) AS MaxPayType,
         SUM(PC.amtcr) - SUM(PC.amtdr) AS Amount
      ')
         ->leftJoin('paychargeh as PC', function ($join) use ($fordate, $departCodes) {
            $join->on('S.docid', '=', 'PC.contradocid')
               ->where('PC.paytype', '=', 'Cash')
               ->where('PC.vtype', 'AD')
               ->whereNotIn('PC.paycode', ['TOUT' . $this->propertyid])
               ->where('PC.restcode', "BANQ$this->propertyid")
               ->where('PC.vdate', '=', $fordate);
         })
         ->leftJoin('depart as D', 'S.restcode', '=', 'D.dcode')
         ->where('S.propertyid', '=', $this->propertyid)
         ->groupBy('PC.paycode')
         ->groupBy('PC.amtcr')
         ->get();

      foreach ($banquet2 as $row) {
         $banquetsum += $row->Amount;
         $reportdata[] = [
            'banquet' => 'Y',
            'advance' => 'Y',
            'outlet' => $row->partyname . ' (Advance)',
            'amount' => $row->Amount,
            'docid' => $row->banqdocid,
            'billno' => $row->billno
         ];
      }

      $misccol = DB::table('expsheet')
         ->select([
            'expsheet.vno as Vouncherno',
            's2.name as ACName',
            'expsheet.cramt as Amount',
            'expsheet.remark'
         ])
         ->leftJoin('subgroup', 'expsheet.drac', '=', 'subgroup.sub_code')
         ->leftJoin('subgroup as s2', 'expsheet.crac', '=', 's2.sub_code')
         ->where('expsheet.vtype', 'HTSAL')
         ->where('expsheet.VDate', $fordate)
         ->where('subgroup.nature', 'Cash')
         ->whereNot('expsheet.delflag', 'Y')
         ->where('expsheet.propertyid', $this->propertyid)
         ->orderBy('ACName')
         ->get();

      foreach ($misccol as $row) {
         $miscolsum += $row->Amount;
         $reportdata[] = [
            'miscy' => 'Y',
            'acname' => $row->ACName,
            'voucherno' => $row->Vouncherno,
            'amount' => $row->Amount
         ];
      }

      if ($invenv->cashpurcheffect == 'Y') {
         $expsheetData = DB::table('expsheet as e')
            ->leftJoin('subgroup as s', 'e.crac', '=', 's.sub_code')
            ->leftJoin('subgroup as s2', 'e.drac', '=', 's2.sub_code')
            ->selectRaw('
               e.vno as Vouncherno,
               s2.name as ACName,
               e.dramt as Amount,
               e.remark
            ')
            ->where('e.Vtype', 'HTEXP')
            ->where('s.nature', 'Cash')
            ->where('e.delflag', '!=', 'Y')
            ->where('e.vdate', $fordate)
            ->where('e.propertyid', $this->propertyid);

         $purchData = DB::table('purch1 as p')
            ->selectRaw("
               NULL as Vouncherno,
               'Cash Purchase' as ACName,
               SUM(p.NetAmt) as Amount,
               NULL as remark
            ")
            ->where('p.vdate', $fordate)
            ->where('p.propertyid', $this->propertyid)
            ->where('p.vtype', 'PBPC');

         $miscexpense = DB::query()
            ->fromSub($expsheetData->unionAll($purchData), 't')
            ->orderBy('ACName')
            ->get();

         Log::info('miscexpense data: ' . json_encode($miscexpense));
      } else {
         $miscexpense = DB::table('expsheet as e')
            ->leftJoin('subgroup as s', 'e.crac', '=', 's.sub_code')
            ->leftJoin('subgroup as s2', 'e.drac', '=', 's2.sub_code')
            ->select(
               'e.vno as Vouncherno',
               'e.dramt as Amount',
               'e.remark',
               's2.name as ACName'
            )
            ->where('e.Vtype', 'HTEXP')
            ->where('e.VDate', $fordate)
            ->where('s.nature', 'Cash')
            ->whereNot('e.delflag', 'Y')
            ->where('e.propertyid', $this->propertyid)
            ->orderBy('s.name')
            ->get();
      }

      foreach ($miscexpense as $row) {
         $misexpsum += (float) $row->Amount;
         $reportdata[] = [
            'miscx' => 'Y',
            'acname' => $row->ACName,
            'voucherno' => $row->Vouncherno,
            'amount' => $row->Amount,
            'remark' => $row->remark
         ];
      }

      $companyrec = DB::table('paycharge as P')
         ->leftJoin('revmast as PY', 'P.PayCode', '=', 'PY.rev_code')
         ->leftJoin('subgroup as S', 'P.comp_code', '=', 'S.Sub_Code')
         ->leftJoin('fombilldetails as FO', function ($join) {
            $join->on('P.folionodocid', '=', 'FO.folionodocid')
               ->where('FO.status', 'settle');
         })
         ->select([
            'P.vno as vno',
            'P.foliono as foliono',
            'FO.billno as billno',
            'S.name as compname',
            'P.amtcr as amount'
         ])
         ->where(function ($query) {
            $query->where(function ($q) {
               $q->whereIn('P.VTYPE', ['ARRES', 'ADRES', 'AWRES'])
                  ->where('P.propertyid', $this->propertyid);
            })
               ->orWhere(function ($q) {
                  $q->whereNotIn('P.VTYPE', ['ARRES', 'ADRES', 'AWRES'])
                     ->where(function ($sub) {
                        $sub->whereNull('P.contraid')
                           ->orWhere('P.contraid', '');
                     });
               });
         })
         ->where('P.Vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('P.modeset', 'S')
         ->where('P.RESTCODE', 'FOM' . $this->propertyid)
         ->where('PY.Field_Type', 'P')
         ->where('P.PayType', 'Company')
         ->where('P.Vtype', '<>', 'CHK')
         ->groupBy('FO.billno')
         ->orderBy('S.Name')
         ->orderBy('P.FOLIONODOCID')
         ->orderBy('P.FOLIONO')
         ->orderBy('P.VTYPE')
         ->orderBy('P.VNO')
         ->get();

      foreach ($companyrec as $row) {
         $compsum += $row->amount;
         $reportdata[] = [
            'comp' => 'Y',
            'compname' => $row->compname,
            'vno' => $row->vno,
            'billno' => $row->billno,
            'foliono' => $row->foliono,
            'amount' => $row->amount
         ];
      }

      $companyrecpos = DB::table('paycharge as P')
         ->leftJoin('revmast as PY', function ($join) {
            $join->on('P.PayCode', '=', 'PY.rev_code')
               ->where('PY.Field_Type', 'P');
         })
         ->leftJoin('subgroup as S', 'P.comp_code', '=', 'S.Sub_Code')
         ->join('sale1 as S1', 'P.docid', '=', 'S1.docid')
         ->select([
            'P.vno as vno',
            'P.foliono as foliono',
            'S1.vno as billno',
            'S.name as compname',
            'P.amtcr as amount'
         ])
         ->where('P.vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('P.guestprof', '')
         ->where('P.RESTCODE', '!=', 'FOM' . $this->propertyid)
         ->where('P.PayType', 'Company')
         ->where('P.Vtype', '<>', 'CHK')
         ->orderBy('P.vno')
         ->get();


      foreach ($companyrecpos as $row) {
         $compsum += $row->amount;
         $reportdata[] = [
            'comp' => 'Y',
            'compname' => $row->compname,
            'vno' => $row->vno,
            'billno' => $row->billno,
            'foliono' => $row->foliono,
            'amount' => $row->amount
         ];
      }

      $companyrecbanq = DB::table('paychargeh as P')
         ->leftJoin('revmast as PY', function ($join) {
            $join->on('P.PayCode', '=', 'PY.rev_code')
               ->where('PY.Field_Type', 'P');
         })
         ->leftJoin('subgroup as S', 'P.comp_code', '=', 'S.Sub_Code')
         ->join('hallsale1 as S1', 'P.docid', '=', 'S1.docId')
         ->select([
            'P.vno as vno',
            DB::raw("'' as foliono"),
            'S1.vno as billno',
            'S.name as compname',
            'P.amtcr as amount'
         ])
         ->where('P.vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('P.RESTCODE', '=', 'BANQ' . $this->propertyid)
         ->where('P.PayType', 'Company')
         ->where('P.Vtype', '<>', 'CHK')
         ->orderBy('P.vno')
         ->get();

      foreach ($companyrecbanq as $row) {
         $compsum += $row->amount;
         $reportdata[] = [
            'comp' => 'Y',
            'compname' => $row->compname,
            'vno' => $row->vno,
            'billno' => $row->billno,
            'foliono' => $row->foliono,
            'amount' => $row->amount
         ];
      }

      $otherpay = DB::table('paycharge as P')
         ->select(
            'P.vno as vno',
            DB::raw('MAX(P.foliono) as foliono'),
            'booking.BookNo as resno',
            'FO.billno as billno',
            'G.name as guestname',
            'P.paytype as paymode',
            'P.restcode',
            'P.vtype',
            'P.vno',
            DB::raw('SUM(P.amtcr) as amount')
         )
         ->leftJoin('revmast as PY', 'P.PayCode', '=', 'PY.rev_code')
         ->leftJoin('booking', 'booking.DocId', '=', 'P.refdocid')
         ->leftJoin('guestprof AS G', function ($join) {
            $join->on('P.guestprof', '=', 'G.guestcode');
         })
         // ->leftJoin('fombilldetails as FO', function ($join) {
         //    $join->on('P.folionodocid', '=', 'FO.folionodocid')
         //       ->on('P.sno1', '=', 'FO.sno1');
         // })
         ->leftJoin('fombilldetails as FO', function ($join) {
            $join->on('P.folionodocid', '=', 'FO.folionodocid')
               ->whereRaw("FO.sno1 = CASE WHEN P.msno1 = 0 THEN P.sno1 ELSE P.msno1 END")
               ->where('FO.status', 'settle');
         })
         ->where(function ($query) {
            $query->where(function ($q) {
               $q->whereIn('P.VTYPE', ['ARRES', 'ADRES', 'AWRES'])
                  ->where('P.propertyid', $this->propertyid);
            })
               ->orWhere(function ($q) {
                  $q->whereNotIn('P.VTYPE', ['ARRES', 'ADRES', 'AWRES'])
                     ->where(function ($sub) {
                        $sub->whereNull('P.contraid')
                           ->orWhere('P.contraid', '');
                     });
               });
         })
         ->whereDate('P.vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('PY.Field_Type', 'P')
         ->whereIn('P.PayType', [
            'UPI',
            'Credit Card',
            'Cheque',
            'Hold',
            'Complementary',
            'Staff',
            'Other'
         ])
         ->where('P.Vtype', '<>', 'CHK')
         // ->groupBy('P.folionodocid')
         ->groupBy('P.docid')
         ->groupBy('P.sno')
         // ->groupBy('FO.sno1')
         // ->orderBy('P.paytype')
         ->orderBy('FO.billno')
         ->get();

      // One batched fetch of depart names for every non-FOM restcode on the
      // page (the per-row Depart lookup was an N+1 â€” 1 query per payment row).
      $nonFomCodes = $otherpay
         ->pluck('restcode')
         ->reject(fn($code) => $code == 'FOM' . $this->propertyid)
         ->filter()
         ->unique()
         ->values();

      $departNames = Depart::where('propertyid', $this->propertyid)
         ->whereIn('dcode', $nonFomCodes)
         ->pluck('name', 'dcode');

      foreach ($otherpay as $row) {

         if ($row->restcode != 'FOM' . $this->propertyid) {
            $row->billno = $row->vtype . ' / ' . $row->vno;
            $depart = $departNames->get($row->restcode);
            $row->guestname = $depart ? $depart : $row->restcode;
         }

         $othersum += $row->amount;
         $reportdata[] = [
            'otherpay' => 'Y',
            'guestname' => $row->guestname,
            'vno' => $row->vno,
            'billno' => $row->billno,
            'foliono' => $row->foliono . ' / ' . $row->resno,
            'amount' => $row->amount,
            'paymode' => $row->paymode
         ];
      }

      $misccolotherpay = DB::table('expsheet')
         ->select([
            'expsheet.vno as Vouncherno',
            'subgroup.nature as paymode',
            'subgroup.name as ACName',
            'expsheet.cramt as Amount',
            'expsheet.remark'
         ])
         ->leftJoin('subgroup', 'expsheet.drac', '=', 'subgroup.sub_code')
         ->where('expsheet.vtype', 'HTSAL')
         ->where('expsheet.VDate', $fordate)
         ->whereNot('subgroup.nature', 'Cash')
         ->where('expsheet.propertyid', $this->propertyid)
         ->orderBy('ACName')
         ->get();

      foreach ($misccolotherpay as $row) {
         $othersum += $row->Amount;
         $reportdata[] = [
            'otherpay' => 'Y',
            'paymode' => $row->paymode,
            'vno' => $row->Vouncherno,
            'amount' => $row->Amount,
            'guestname' => $row->ACName,
            'billno' => '',
            'foliono' => ''
         ];
      }

      $otherpaybanq = DB::table('paychargeh as P')
         ->select(
            'P.vno as vno',
            'hallbook.partyname as partyname',
            'hallbook.vno as billno',
            'P.paytype as paymode',
            'P.restcode',
            'P.vtype',
            'P.vno',
            DB::raw('SUM(P.amtcr) as amount')
         )
         ->leftJoin('revmast as PY', 'P.PayCode', '=', 'PY.rev_code')
         ->Join('hallbook', 'hallbook.docid', '=', 'P.contradocid')
         ->whereDate('P.vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('PY.Field_Type', 'P')
         ->whereIn('P.PayType', [
            'UPI',
            'Credit Card',
            'Cheque',
            'Hold',
            'Complementary',
            'Staff',
            'Other',
         ])
         ->where('P.vtype', 'AD')
         ->groupBy('P.paytype')
         ->groupBy('P.amtcr')
         ->get();

      foreach ($otherpaybanq as $row) {

         $othersum += $row->amount;
         $reportdata[] = [
            'otherpay' => 'Y',
            'banq' => 'Y',
            'guestname' => $row->partyname . ' (Advance)',
            'vno' => $row->vno,
            'billno' => '',
            'foliono' => '',
            'amount' => $row->amount,
            'paymode' => $row->paymode
         ];
      }

      $otherpaybanqsale = DB::table('paychargeh as P')
         ->select(
            'P.vno as vno',
            'hallsale1.party as partyname',
            'hallsale1.vno as billno',
            'P.paytype as paymode',
            'P.restcode',
            'P.vtype',
            'P.vno',
            DB::raw('SUM(P.amtcr) as amount')
         )
         ->leftJoin('revmast as PY', 'P.PayCode', '=', 'PY.rev_code')
         ->Join('hallsale1', 'hallsale1.docid', '=', 'P.docid')
         ->whereDate('P.vdate', $fordate)
         ->where('P.propertyid', $this->propertyid)
         ->where('PY.Field_Type', 'P')
         ->whereIn('P.PayType', [
            'UPI',
            'Credit Card',
            'Cheque',
            'Hold',
            'Complementary',
            'Staff',
            'Other',
         ])
         ->groupBy('P.paytype')
         ->groupBy('P.amtcr')
         ->get();

      foreach ($otherpaybanqsale as $row) {

         $othersum += $row->amount;
         $reportdata[] = [
            'otherpay' => 'Y',
            'banq' => 'Y',
            'guestname' => $row->partyname . ' (Settlement)',
            'vno' => $row->vno,
            'billno' => $row->billno,
            'foliono' => '',
            'amount' => $row->amount,
            'paymode' => $row->paymode
         ];
      }

      $totalamount = 0.00;

      foreach ($reportdata as $row) {
         $totalamount += (float) $row['amount'];
      }

      $data =  [
         'reportdata' => $reportdata,
         'departCodes' => $departCodes,
         'totalamount' => $totalamount,
         'frontofcsum' => $frontofcsum,
         'outletsum' => $outletsum,
         'banquetsum' => $banquetsum,
         'miscolsum' => $miscolsum,
         'misexpsum' => $misexpsum,
         'compsum' => $compsum,
         'othersum' => $othersum
      ];

      return json_encode($data);
   }

   public function pendingkotreport(Request $request)
   {
      $permission = revokeopen(171717);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $propertyId = $request->input('propertyid', $this->propertyid);

      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.pendingkotreport', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename,

         'propertyid' => $propertyId

      ]);
   }

   public function pendingkotreportfetch(Request $request)
   {
      $fromdate = $request->fromdate;

      $todate = $request->todate;

      $propertyId = $request->input('propertyid', $this->propertyid);

      $validOutlets = DB::table('depart')
         ->select('dcode')
         ->where('propertyid', $propertyId)
         ->whereIn('Rest_Type', ['Outlet', 'Room Service'])
         ->where('pos', 'Y')
         ->pluck('dcode');

      // Fetching the merged report based on the valid outlets
      $mergedReport = DB::table('kot AS K')
         ->join('server_mast AS W', 'K.waiter', '=', 'W.scode')
         ->join('itemmast AS I', function ($join) {
            $join->on('K.item', '=', 'I.Code')
               ->on('K.restcode', '=', 'I.RestCode');
         })
         ->join('depart AS D', function ($join) use ($propertyId) {
            $join->on('K.restcode', '=', 'D.dcode')
               ->where('D.propertyid', $propertyId)
               ->whereIn('D.Rest_Type', ['Outlet', 'Room Service'])
               ->where('D.pos', 'Y');
         })
         ->join('voucher_type AS V', 'K.vtype', '=', 'V.v_type')
         ->select(
            'D.name AS Outlet',
            'D.dcode',
            'K.vdate AS Date',
            'K.vtime AS Time',
            'K.vno AS KOTno',
            'W.name AS WAITER',
            'K.roomno AS RoomTableNo',
            'K.u_name AS UserName',
            'I.Name AS ItemName',
            'K.qty AS Qty'
         )
         ->where('K.nckot', '<>', 'Y')
         ->whereBetween('K.vdate', [$fromdate, $todate])
         ->whereIn('V.ncat', ['RSKOT'])
         ->where('K.pending', 'Y')
         ->whereRaw("IFNULL(K.delflag, '') <> 'Y'")
         ->where('K.voidyn', '<>', 'Y')
         ->whereIn('K.restcode', $validOutlets)
         ->where('K.propertyid', $propertyId)
         ->groupBy('K.vno')
         ->groupBy('K.item')
         ->orderBy('K.docid')
         ->orderBy('K.restcode')
         ->orderBy('I.Name')
         ->get();

      return response()->json($mergedReport);
   }

   public function kotwisedetail(Request $request)
   {
      $permission = revokeopen(171716);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      // Fetching the valid outlets for the given property ID 
      $Outlets = DB::table('depart')
         ->select('dcode', 'Name')
         ->where('propertyid', $this->propertyid)
         ->whereIn('Rest_Type', ['Outlet', 'Room Service'])
         ->where('pos', 'Y')
         ->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.kotwisedetail', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename,
         'outlets' => $Outlets,
         'propertyid' => $this->propertyid
      ]);
   }
   public function kotwisedetailfetch(Request $request)
   {

      //   echo "<pre>";
      //     die();

      $fromdate = $request->fromdate;

      $todate = $request->todate;

      $outlet = $request->input('outlet'); // $request->outlet;


      $propertyId = $request->input('propertyid', $this->propertyid);


      // Fetching the valid outlets for the given property ID 
      $Outlets = DB::table('depart')
         ->select('dcode')
         ->where('propertyid', $propertyId)
         ->whereIn('Rest_Type', ['Outlet', 'Room Service'])
         ->where('pos', 'Y')
         ->pluck('dcode');

      // Fetching the details based on the selected outlets and date range
      $details = DB::table('kot AS K')
         ->select(
            'D.name AS OutLet',
            'K.vdate AS Date',
            'K.roomno AS TableRoomNo',
            'K.vno AS KOTNO',
            'K.vtime AS KotTime',
            'S.vno AS BILLNO',
            'K.qty AS QTY',
            'K.rate AS Rate',
            'K.amount as kotamount',
            'I.Name AS ITEMNAME',
            DB::raw("CASE K.voidyn WHEN 'N' THEN 'No' WHEN 'Y' THEN 'Yes' ELSE '' END AS VoidYN"),
            'KL.VTime AS EditTime',
            'W.Name AS WAITER',
            'K.u_name AS UserName',
            'K.remarks AS Remarks',
            'K.reasons AS Reason'
         )
         ->leftJoin('sale1 AS S', 'K.contradocid', '=', 'S.DocId')
         ->leftJoin('itemmast AS I', function ($join) {
            $join->on('K.item', '=', 'I.Code')
               ->on('K.itemrestcode', '=', 'I.RestCode');
         })
         ->leftJoin('depart AS D', function ($join) use ($propertyId) {
            $join->on('K.restcode', '=', 'D.dcode')
               ->where('D.propertyid', $propertyId)
               ->whereIn('D.Rest_Type', ['Outlet', 'Room Service'])
               ->where('D.pos', 'Y');
         })
         ->leftJoin(DB::raw('(SELECT DocId, MAX(VTime) AS VTime FROM kotlog GROUP BY DocId) AS KL'), 'KL.DocId', '=', 'K.DocId')
         ->leftJoin('server_mast AS W', 'K.waiter', '=', 'W.scode')
         ->where('K.propertyid', $propertyId)
         ->whereNotIn(DB::raw('IFNULL(S.DELFLAG, "")'), ['D', 'Y'])
         ->where('K.nckot', 'N')
         ->whereBetween('K.vdate', [$fromdate, $todate])
         ->whereIn('K.restcode', $outlet)
         ->orderBy('K.restcode')
         ->orderBy('K.vdate')
         ->orderBy('K.vno')
         ->get();

      return response()->json($details);
   }






   public function roominventory(Request $request)
   {

      $permission = revokeopen(141311);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.roominventory', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename,


      ]);
   }

   public function roominventoryfetch(Request $request)
   {
      $status = $request->status;

      $allrooms = DB::table('room_mast as RM')
         ->join('room_cat as RC', function ($join) {
            $join->on('RM.room_cat', '=', 'RC.cat_code')
               ->on('RM.type', '=', 'RC.type');
         })
         ->select('RM.rcode as ROOMNO', 'RC.Name as RoomCatName')
         ->where([
            ['RM.Type', 'RO'],
            ['RM.InclCount', 'Y'],
            ['RM.propertyid', $this->propertyid]
         ])
         ->orderBy('RM.rcode')
         ->get();

      $occupiedRooms = DB::table('room_mast as RM')
         ->select([
            'RM.rcode as ROOMNO',
            'RC.name as RoomCatName',
            'RO.foliono',
            'RO.sno1',
            'RO.docid',
            'RO.chkindate',
            'RO.type',
            'RO.depdate',
            'RO.adult',
            'RO.children as Child',
            'PL.name as PlanName',
            'RO.roomrate',
            'RO.planamt',
            'GF.name AS GuestName',
            'B.bookedby',
            'BS.name AS MarketSeg',
            'S.name AS CompanyName',
            DB::raw('MAX(RL.rate2) as RackRate')
         ])
         ->join('room_cat as RC', function ($join) {
            $join->on('RM.room_cat', '=', 'RC.cat_code')
               ->on('RM.type', '=', 'RC.type')
               ->where('RC.propertyid', $this->propertyid);
         })
         ->join('roomocc as RO', function ($join) {
            $join->on('RM.rcode', '=', 'RO.roomno')
               ->where('RO.propertyid', $this->propertyid)
               ->whereNull('RO.chkoutdate');
         })
         ->leftJoin('guestfolio as GF', function ($join) {
            $join->on('RO.foliono', '=', 'GF.folio_no')
               ->where('GF.propertyid', $this->propertyid);
         })
         ->leftJoin('booking as B', 'B.DocId', '=', 'GF.bookingdocid')
         ->leftJoin('busssource as BS', function ($join) {
            $join->on('GF.busssource', '=', 'BS.bcode')
               ->where('BS.propertyid', $this->propertyid);
         })
         ->leftJoin('subgroup as S', function ($join) {
            $join->on('GF.company', '=', 'S.sub_code')
               ->where('S.propertyid', $this->propertyid);
         })
         ->leftJoin('plan_mast as PL', function ($join) {
            $join->on('RO.plancode', '=', 'PL.pcode')
               ->where('PL.propertyid', $this->propertyid);
         })
         ->leftJoin('rate_list as RL', function ($join) {
            $join->on('RM.rcode', '=', 'RL.roomno')
               ->where('RL.propertyid', $this->propertyid)
               ->orOn('RM.room_cat', '=', 'RL.room_cat');
         })
         ->where([
            ['RM.type', 'RO'],
            ['RM.inclcount', 'Y'],
            ['RM.propertyid', $this->propertyid]
         ])
         ->groupBy([
            'RM.rcode',
            'RC.name',
            'RO.foliono',
            'RO.chkindate',
            'RO.type',
            'RO.depdate',
            'RO.adult',
            'RO.children',
            'PL.name',
            'RO.roomrate',
            'RO.planamt',
            'GF.name',
            'B.bookedby',
            'BS.name',
            'S.name'
         ])
         ->orderBy('RO.roomno')
         ->orderBy('RM.rcode')
         ->get();

      // Batch the per-room balance/advance lookups (was 2 queries per room).
      // Aggregate once per (folionodocid, sno1) pair, then attach in memory.
      // NOTE: the original had NO propertyid filter on these lookups â€” preserved.
      $folioPairs = collect($occupiedRooms)
         ->filter(function ($row) {
            return $row->docid !== null && $row->sno1 !== null;
         })
         ->map(fn($row) => $row->docid . '_' . $row->sno1)
         ->unique();

      $folioLookup = collect();
      if ($folioPairs->isNotEmpty()) {
         $folioLookup = Paycharge::select(
            'folionodocid',
            'sno1',
            DB::raw('SUM(amtdr) - SUM(amtcr) as balanceamt'),
            DB::raw('SUM(amtcr) as Advance')
         )
            ->whereIn('folionodocid', $occupiedRooms->pluck('docid')->unique()->filter()->values())
            ->whereIn('sno1', $occupiedRooms->pluck('sno1')->unique()->filter()->values())
            ->groupBy('folionodocid', 'sno1')
            ->get()
            ->keyBy(fn($r) => $r->folionodocid . '_' . $r->sno1);
      }

      foreach ($occupiedRooms as $row) {
         $pair = $folioLookup->get($row->docid . '_' . $row->sno1);

         $row->Advance = $pair->Advance ?? 0;
         $row->balanceamt = $pair->balanceamt ?? 0;
      }


      return response()->json([
         'allrooms' => $allrooms,
         'roomdetails' => $occupiedRooms,
         'status' => $status
      ]);
   }

   public function voidbills(Request $request)
   {
      $permission = revokeopen(171812);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      return view('property.voidbills', [
         'ncurdate' => $this->ncurdate,
         'company' => $company,
         'statename' => $statename
      ]);
   }
   public function voidbillsfetch(Request $request)
   {
      $fromdate = $request->fromdate;
      $todate = $request->todate;

      $Outlets = DB::table('depart')
         ->where('propertyid', $this->propertyid)
         ->whereIn('nature', ['Outlet', 'Room Service'])
         ->where('pos', 'Y')
         ->pluck('dcode')
         ->toArray();

      $sales = DB::table('sale1 as S')
         ->leftJoin('depart as D', 'S.restcode', '=', 'D.dcode')
         ->leftJoin('server_mast as W', 'S.waiter', '=', 'W.scode')
         ->leftJoin('room_mast as RM', function ($join) {
            $join->on('S.restcode', '=', 'RM.rest_code');
         })
         ->select([
            'D.name as DEPARTNAME',
            DB::raw("CONCAT_WS('/', S.vtype, S.VNO) AS BillNo"),
            'S.vdate as Date',
            'S.vtime as Time',
            'W.name as WAITER',
            'S.roomno as TableRoomno',
            'S.netamt as NetSale',
            'S.delremark as Remark',
            'S.u_name as UserName'
         ])
         ->where('S.propertyid', $this->propertyid)
         ->whereBetween('S.vdate', [$fromdate, $todate])
         ->where('S.delflag', 'Y')
         ->whereIn('S.restcode', $Outlets)
         // ->whereRaw("COALESCE(RM.type, '') IN ('TB')")
         ->groupBy('S.docid')
         ->orderBy('D.name')
         ->orderBy('S.vno')
         ->get();

      return response()->json($sales);
   }


   public function fomsalesummary(Request $request)
   {
      $permission = revokeopen(141312);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $fromdate = $this->ncurdate;
      $bsource = DB::table('busssource')
         ->where('propertyid', $this->propertyid)
         ->orderBy('name', 'ASC')->get();
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');
      $outlets = Depart::where('propertyid', $this->propertyid)->whereIn('nature', ['Room Service', 'Outlet'])->orderBy('name')->get();

      $roundoff = 'ROFF' . $this->propertyid;
      $disc = 'DISC' . $this->propertyid;
      $cgst = 'CGSS' . $this->propertyid;
      $sgst = 'SGSS' . $this->propertyid;
      $tout = 'TOUT' . $this->propertyid;

      $revmast = Revmast::where('revmast.propertyid', $this->propertyid)
         ->where('field_type', 'C')
         ->where('Desk_code', '=', 'FOM' . $this->propertyid)
         ->whereNotIn('revmast.rev_code', [$roundoff, $disc, $cgst, $sgst, $tout])
         ->whereNot('seq_no', '0')
         ->distinct()
         ->orderBy('seq_no', 'ASC')
         ->get();

      return view('property.fomsalesummary', [
         'fromdate' => $fromdate,
         'statename' => $statename,
         'company' => $company,
         'outlets' => $outlets,
         'revmast' => $revmast
      ]);
   }

   public function fetchfomsalesummary(Request $request)
   {
      $fromdate = $request->fromdate;
      $todate = $request->todate;
      $propertyId = $this->propertyid;
      $from = $fromdate;
      $to = $todate;

      $totalRooms = DB::table('room_mast')
         ->where('propertyid', $propertyId)
         ->where('type', 'RO')
         ->where('inclcount', 'Y')
         ->count();

      $roundoff = 'ROFF' . $propertyId;
      $disc = 'DISC' . $propertyId;
      $cgst = 'CGSS' . $propertyId;
      $sgst = 'SGSS' . $propertyId;
      $tout = 'TOUT' . $propertyId;
      $rmch = 'RMCH' . $propertyId;

      $revmast = Revmast::where('revmast.propertyid', $propertyId)
         ->where('field_type', 'C')
         ->where('Desk_code', '=', 'FOM' . $propertyId)
         ->whereNotIn('revmast.rev_code', [$roundoff, $disc, $cgst, $sgst, $tout])
         ->whereNot('seq_no', '0')
         ->distinct()
         ->orderBy('seq_no', 'ASC')
         ->get();

      $selectFields = [
         'vdate',
         DB::raw("SUM(CASE WHEN paycode = '{$rmch}' THEN 1 ELSE 0 END) as chargableroom"),
         DB::raw("SUM(CASE WHEN paycode = '{$cgst}' THEN amtdr ELSE 0 END) as cgst"),
         DB::raw("SUM(CASE WHEN paycode = '{$sgst}' THEN amtdr ELSE 0 END) as sgst"),
      ];

      foreach ($revmast as $row) {
         $code = $row->rev_code;
         $alias = "sum_" . strtolower($code);
         $selectFields[] = DB::raw("SUM(CASE WHEN paycode = '{$code}' THEN amtdr ELSE 0 END) as {$alias}");
      }

      $paySummary = DB::table('paycharge')
         ->select($selectFields)
         ->where('propertyid', $propertyId)
         ->whereBetween('vdate', [$from, $to])
         ->groupBy('vdate');

      $data = DB::table('paycharge as p')
         ->joinSub($paySummary, 'summary', function ($join) {
            $join->on('p.vdate', '=', 'summary.vdate');
         })
         ->leftJoin('roomocc', 'roomocc.chkindate', '=', 'p.vdate')
         ->select(array_merge([
            'p.vdate',
            'p.folionodocid',
            DB::raw("CONCAT(MIN(p.billno), ' to ', MAX(p.billno)) AS billno_range"),
            DB::raw("$totalRooms as totalrooms"),
            DB::raw("summary.chargableroom"),
            DB::raw("($totalRooms - summary.chargableroom) as balance_room"),
            DB::raw("ROUND((summary.chargableroom / NULLIF($totalRooms, 0)) * 100, 2) as roomoccupancy"),
            DB::raw("summary.cgst"),
            DB::raw("summary.sgst"),
         ], $revmast->map(function ($rev) {
            $alias = "sum_" . strtolower($rev->rev_code);
            return DB::raw("summary.{$alias} as {$alias}");
         })->all()))
         ->where('p.propertyid', $propertyId)
         ->whereBetween('p.vdate', [$from, $to])
         ->groupBy('p.vdate')
         ->orderBy('p.vdate')
         ->get();

      // return $data;

      foreach ($data as $row) {
         foreach ($revmast as $rev) {
            $alias = "sum_" . strtolower($rev->rev_code);
            $row->$alias = $row->$alias ?? 0;
         }
      }

      $outlets = Depart::where('propertyid', $propertyId)
         ->whereIn('nature', ['Room Service', 'Outlet'])
         ->orderBy('name')
         ->get();
      foreach ($data as $row) {
         $vdate = $row->vdate;

         foreach ($outlets as $outlet) {
            $shortName = strtolower($outlet->short_name);
            $restcode = $outlet->dcode;

            $result = DB::table('sale1')
               ->selectRaw('SUM(total) AS total_sum, SUM(discamt) AS discamt_sum')
               ->where('propertyid', $this->propertyid)
               ->where('restcode', $restcode)
               ->where('vdate', $vdate)
               ->where('delflag', 'N')
               ->first();

            $totalSum = $result->total_sum;
            $discamtSum = $result->discamt_sum;
            $row->$shortName = $totalSum - $discamtSum;
         }

         $paycode = 'RMCH' . $this->propertyid;

         // $groupColumn = Paycharge::where('propertyid', $this->propertyid)
         //    ->where('vdate', $vdate)
         //    ->where('paycode', $paycode)
         //    ->groupBy('relatedfolionodocid')
         //    ->exists() ? 'relatedfolionodocid' : 'folionodocid';

         $groupColumn = 'folionodocid';

         $folioDocIds = DB::table('paycharge')
            ->where('propertyid', $this->propertyid)
            ->where('vdate', $vdate)
            ->where('paycode', $paycode)
            ->groupBy($groupColumn)
            ->pluck($groupColumn)
            ->toArray();

         $roomOccQuery = DB::table('roomocc')
            ->whereIn('docid', $folioDocIds)
            ->where(function ($query) {
               $query->where('type', '!=', 'C');
            });

         $row->adult = $roomOccQuery->sum('adult') ?? 0;
         $row->children = $roomOccQuery->sum('children') ?? 0;
      }

      return response()->json([
         'report' => $data,
         'revmast' => $revmast,
      ]);
   }

   public function contributionreport(Request $request)
   {
      $permission = revokeopen(141313);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $ncurdate = $this->ncurdate;
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');
      $years = DateHelper::Uniqueyears($this->propertyid);
      return view('property.contributionreport', [
         'ncurdate' => $ncurdate,
         'statename' => $statename,
         'comp' => $comp,
         'years' => $years
      ]);
   }

   public function fetchcontribuition(Request $request)
   {
      $year = $request->vprefix;
      $month = $request->formonth;
      $type = $request->type;

      $month = str_pad($month, 2, '0', STR_PAD_LEFT);
      $startdate = "$year-$month-01";
      $enddate = date("Y-m-t", strtotime($startdate));

      $data = DB::table('subgroup as sg')
         ->selectRaw("
        sg.sub_code AS compcode,
        sg.name AS company_name,
        SUM(CASE WHEN DAY(p.vdate) = 1 THEN 1 ELSE 0 END) AS `01`,
        SUM(CASE WHEN DAY(p.vdate) = 2 THEN 1 ELSE 0 END) AS `02`,
        SUM(CASE WHEN DAY(p.vdate) = 3 THEN 1 ELSE 0 END) AS `03`,
        SUM(CASE WHEN DAY(p.vdate) = 4 THEN 1 ELSE 0 END) AS `04`,
        SUM(CASE WHEN DAY(p.vdate) = 5 THEN 1 ELSE 0 END) AS `05`,
        SUM(CASE WHEN DAY(p.vdate) = 6 THEN 1 ELSE 0 END) AS `06`,
        SUM(CASE WHEN DAY(p.vdate) = 7 THEN 1 ELSE 0 END) AS `07`,
        SUM(CASE WHEN DAY(p.vdate) = 8 THEN 1 ELSE 0 END) AS `08`,
        SUM(CASE WHEN DAY(p.vdate) = 9 THEN 1 ELSE 0 END) AS `09`,
        SUM(CASE WHEN DAY(p.vdate) = 10 THEN 1 ELSE 0 END) AS `10`,
        SUM(CASE WHEN DAY(p.vdate) = 11 THEN 1 ELSE 0 END) AS `11`,
        SUM(CASE WHEN DAY(p.vdate) = 12 THEN 1 ELSE 0 END) AS `12`,
        SUM(CASE WHEN DAY(p.vdate) = 13 THEN 1 ELSE 0 END) AS `13`,
        SUM(CASE WHEN DAY(p.vdate) = 14 THEN 1 ELSE 0 END) AS `14`,
        SUM(CASE WHEN DAY(p.vdate) = 15 THEN 1 ELSE 0 END) AS `15`,
        SUM(CASE WHEN DAY(p.vdate) = 16 THEN 1 ELSE 0 END) AS `16`,
        SUM(CASE WHEN DAY(p.vdate) = 17 THEN 1 ELSE 0 END) AS `17`,
        SUM(CASE WHEN DAY(p.vdate) = 18 THEN 1 ELSE 0 END) AS `18`,
        SUM(CASE WHEN DAY(p.vdate) = 19 THEN 1 ELSE 0 END) AS `19`,
        SUM(CASE WHEN DAY(p.vdate) = 20 THEN 1 ELSE 0 END) AS `20`,
        SUM(CASE WHEN DAY(p.vdate) = 21 THEN 1 ELSE 0 END) AS `21`,
        SUM(CASE WHEN DAY(p.vdate) = 22 THEN 1 ELSE 0 END) AS `22`,
        SUM(CASE WHEN DAY(p.vdate) = 23 THEN 1 ELSE 0 END) AS `23`,
        SUM(CASE WHEN DAY(p.vdate) = 24 THEN 1 ELSE 0 END) AS `24`,
        SUM(CASE WHEN DAY(p.vdate) = 25 THEN 1 ELSE 0 END) AS `25`,
        SUM(CASE WHEN DAY(p.vdate) = 26 THEN 1 ELSE 0 END) AS `26`,
        SUM(CASE WHEN DAY(p.vdate) = 27 THEN 1 ELSE 0 END) AS `27`,
        SUM(CASE WHEN DAY(p.vdate) = 28 THEN 1 ELSE 0 END) AS `28`,
        SUM(CASE WHEN DAY(p.vdate) = 29 THEN 1 ELSE 0 END) AS `29`,
        SUM(CASE WHEN DAY(p.vdate) = 30 THEN 1 ELSE 0 END) AS `30`,
        SUM(CASE WHEN DAY(p.vdate) = 31 THEN 1 ELSE 0 END) AS `31`,
        COUNT(p.vdate) AS total_nights,
        IFNULL(SUM(p.amtdr), 0) AS revenue
    ")
         ->leftJoin('paycharge as p', function ($join) use ($startdate, $enddate) {
            $join->on('p.comp_code', '=', 'sg.sub_code')
               ->whereBetween('p.vdate', [$startdate, $enddate])
               ->where('p.propertyid', $this->propertyid)
               ->where('p.sno', '1')
               ->where('p.paycode', 'RMCH' . $this->propertyid)
               ->where('p.vtype', 'RC');
         })
         ->where('sg.propertyid', $this->propertyid)
         ->where('sg.comp_type', $type)
         ->groupBy('sg.name', 'sg.sub_code')
         ->orderBy('sg.name')
         ->get();

      foreach ($data as $row) {
         $amountsum = Paycharge::where('propertyid', $this->propertyid)->where('comp_code', $row->compcode)
            ->whereBetween('vdate', [$startdate, $enddate])
            ->sum('amtdr');
         $row->revenue = $amountsum;
      }

      return json_encode($data);
   }

   // Menu Item Rate Report - Load Page
   public function menuitemratereport(Request $request)
   {
      $permission = revokeopen(141215);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      // Fetch outlets (depart) where nature is 'outlet' or 'room services'
      $outlets = DB::table('depart')
         ->where('propertyid', $this->propertyid)
         ->whereIn('nature', ['outlet', 'room services'])
         ->select('dcode', 'Name')
         ->orderBy('Name')
         ->get();
      $ncur = $this->ncurdate;
      return view('property.reports.menuitemratereport', [
         'outlets' => $outlets,
         'ncur' => $ncur
      ]);
   }

   // Fetch Item Groups by Selected Outlet (AJAX)
   public function fetchitemgroupsbyoutlet(Request $request)
   {
      $departCode = $request->input('depart_code');

      if (!$departCode) {
         return json_encode([]);
      }

      // Get RestCode from depart code
      $restCode = DB::table('depart')
         ->where('propertyid', $this->propertyid)
         ->where('dcode', $departCode)
         ->value('dcode');

      if (!$restCode) {
         return json_encode([]);
      }

      // Fetch item groups for this outlet/restcode
      $itemGroups = DB::table('itemgrp')
         ->where('property_id', $this->propertyid)
         ->where('restcode', '=', $restCode)
         ->select('code', 'name')
         ->orderBy('name')
         ->get();

      return json_encode($itemGroups);
   }

   // Fetch Menu Item Rate Report Data (AJAX)
   public function fetchmenuitemratereport(Request $request)
   {
      $departCode = $request->input('depart_code');
      $itemGroupCode = $request->input('item_group');

      if (!$departCode) {
         return json_encode([]);
      }

      // Get RestCode from depart code
      $restCode = DB::table('depart')
         ->where('propertyid', $this->propertyid)
         ->where('dcode', $departCode)
         ->value('dcode');

      if (!$restCode) {
         return json_encode([]);
      }

      $query = DB::table('itemmast as I')
         ->leftJoin('itemrate as R', function ($join) {
            $join->on('I.Code', '=', 'R.ItemCode');
         })
         ->leftJoin('depart as D', function ($join) {
            $join->on('D.DCode', '=', 'I.Kitchen');
         })
         ->where('I.RestCode', $restCode)
         ->where('I.Property_ID', $this->propertyid)
         ->where('R.RestCode', $restCode)
         ->where('I.ActiveYN', 'Y')
         // Get Letest app date 
         ->whereRaw('R.AppDate = (SELECT MAX(AppDate) FROM itemrate WHERE ItemCode = I.Code)')
         ->select(
            'I.Name as ItemName',
            'I.ItemGroup',
            'R.Rate',
            'R.AppDate',
            'I.wtqty',
            'I.Pitemcode',
            'D.Name as DepartName',
            'I.Kitchen as kcode',
            'I.Code as ItemCode'
         );

      // If item group is selected, filter by it; otherwise get all
      if ($itemGroupCode && $itemGroupCode != '') {
         $query->where('I.ItemGroup', $itemGroupCode);
      }

      $data = $query->orderBy('I.Code')
         ->get();

      // Depart name 
      $departname = DB::table('depart')->select('name', 'dcode')->where(['propertyid' => $this->propertyid, 'nature' => 'Kitchen'])->get();
      // Purchase Item Code
      $purchaseitemcode = DB::table('itemmast')->select('Code as code', 'Name as name')->where(['Property_ID' => $this->propertyid, 'RestCode' => 'PURC' . $this->propertyid])->get();

      return response()->json([
         'items' => $data,
         'departments' => $departname,
         'purchase_items' => $purchaseitemcode
      ]);
   }

   public function updatemenuitems(Request $request)
   {
      $permission = revokeopen(141215);
      if (is_null($permission) || $permission->view == 0) {
         return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
      }
      try {
         $items = $request->input('items');
         $outlet = $request->input('outlet');

         if (empty($items)) {
            return response()->json([
               'success' => false,
               'message' => 'No items to update'
            ], 400);
         }

         $updatedCount = 0;

         foreach ($items as $item) {
            // Update itemmast table
            $updated = DB::table('itemmast')
               ->where('Property_ID', $this->propertyid)
               ->where('Code', $item['itemcode'])
               ->update([
                  'Kitchen' => $item['departcode'],
                  'wtqty' => $item['wtqty'],
                  'Pitemcode' => $item['pitemcode']
               ]);

            if ($updated) {
               $updatedCount++;
            }
         }

         return response()->json([
            'success' => true,
            'message' => $updatedCount . ' item(s) updated successfully'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error updating items: ' . $e->getMessage()
         ], 500);
      }
   }

   public function updateitemrates(Request $request)
   {
      $permission = revokeopen(141215);
      if (is_null($permission) || $permission->view == 0) {
         return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
      }
      try {
         $items = $request->input('items');
         $outlet = $request->input('outlet');

         if (empty($items)) {
            return response()->json([
               'success' => false,
               'message' => 'No items to update'
            ], 400);
         }

         // Get RestCode from outlet/depart code
         $restCode = DB::table('depart')
            ->where('propertyid', $this->propertyid)
            ->where('dcode', $outlet)
            ->value('dcode');

         if (!$restCode) {
            return response()->json([
               'success' => false,
               'message' => 'Invalid outlet selected'
            ], 400);
         }

         $updatedCount = 0;
         $insertedCount = 0;

         foreach ($items as $item) {
            if (empty($item['itemcode']) || !isset($item['rate']) || !isset($item['app_date'])) {
               continue;
            }
            $existingRate = DB::table('itemrate')
               ->where('RestCode', $restCode)
               ->where('ItemCode', $item['itemcode'])
               ->where('Property_ID', $this->propertyid)
               ->first();

            if ($existingRate) {
               $updated = DB::table('itemrate')
                  ->where('RestCode', $restCode)
                  ->where('ItemCode', $item['itemcode'])
                  ->where('Property_ID', $this->propertyid)
                  ->update([
                     'Rate' => $item['rate'],
                     'AppDate' => $item['app_date']
                  ]);
               if ($updated) {
                  $updatedCount++;
               }
            } else {
               $inserted = DB::table('itemrate')->insert([
                  'RestCode' => $restCode,
                  'ItemCode' => $item['itemcode'],
                  'Rate' => $item['rate'],
                  'AppDate' => $item['app_date'],
                  'Property_ID' => $this->propertyid
               ]);
               if ($inserted) {
                  $insertedCount++;
               }
            }
         }

         $message = '';
         if ($updatedCount > 0) {
            $message .= $updatedCount . ' rate(s) updated';
         }
         if ($insertedCount > 0) {
            $message .= ($message ? ' and ' : '') . $insertedCount . ' rate(s) inserted';
         }

         return response()->json([
            'success' => true,
            'message' => $message ? $message : 'No changes made'
         ]);
      } catch (\Exception $e) {
         return response()->json([
            'success' => false,
            'message' => 'Error updating rates: ' . $e->getMessage()
         ], 500);
      }
   }

   // â”€â”€â”€ Reward Point Report â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€

   public function rewardpointreport(Request $request)
   {
      $company   = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)
                         ->where('state_code', $company->state_code)
                         ->value('name');

      return view('property.rewardpointreport', [
         'company'   => $company,
         'statename' => $statename,
         'fromdate'  => $this->ncurdate,
      ]);
   }

   public function fetchrewardpointreport(Request $request)
   {
      try {
         $fromdate   = $request->input('fromdate', $this->ncurdate);
         $todate     = $request->input('todate',   $this->ncurdate);
         $propertyId = $this->propertyid;
         $mobileno   = $request->input('mobileno'); // null = all, value = party wise

         $query = DB::table('guestreward')
            ->where('propertyid', $propertyId);

         if ($mobileno) {
            // Party wise â€” filter by mobile, no date filter, order by date DESC
            $query->where('mobileno', $mobileno)
                  ->select(
                     'vdate as Date', 'vtime as Time', 'departname as Outlet',
                     'mobileno', 'billno as BillNo', 'total as GoodAmt',
                     'rewardpoint', 'rewardvalue', 'redeempoint', 'reedemvalue', 'u_name'
                  )
                  ->orderBy('vdate', 'DESC');
         } else {
            // Date range report
            $query->whereBetween('vdate', [$fromdate, $todate])
                  ->select(
                     'vdate as Date', 'vtime as Time', 'departname as Outlet',
                     'mobileno', 'billno as BillNo', 'total as GoodAmt',
                     'rewardpoint', 'rewardvalue', 'redeempoint', 'reedemvalue', 'u_name'
                  )
                  ->orderBy('vdate')
                  ->orderBy('departname')
                  ->orderBy('billno');
         }

         $rows = $query->get();

         return response()->json(['success' => true, 'data' => $rows]);
      } catch (\Exception $e) {
         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }

   public function fetchrewardmobilenumbers(Request $request)
   {
      try {
         $rows = DB::table('guestreward')
            ->where('propertyid', $this->propertyid)
            ->whereNotNull('mobileno')
            ->where('mobileno', '!=', '')
            ->select('mobileno')
            ->groupBy('mobileno')
            ->orderBy('mobileno')
            ->get();

         return response()->json(['success' => true, 'data' => $rows]);
      } catch (\Exception $e) {
         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
      }
   }

   // â”€â”€â”€ Occupancy Forecast Report â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   public function occupancyforecast(Request $request)
   {
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = States::where('propertyid', $this->propertyid)
         ->where('state_code', $company->state_code)
         ->value('name');
      return view('property.occupancyforecast', [
         'ncurdate' => $this->ncurdate,
         'company'  => $company,
         'statename' => $statename,
      ]);
   }

   public function fetchoccupancyforecast(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate   = $request->input('todate');
      $pid      = $this->propertyid;

      // Total rooms (fixed for the property)
      $totalRooms = DB::table('room_mast')
         ->where('propertyid', $pid)
         ->where('type', 'RO')
         ->where('inclcount', 'Y')
         ->count();

      // Build date range day by day
      $start  = Carbon::parse($fromdate);
      $end    = Carbon::parse($todate);
      $rows   = [];

      $grandTotalRooms    = 0;
      $grandArrival       = 0;
      $grandDeparture     = 0;
      $grandStayOver      = 0;
      $grandOccupied      = 0;
      $grandPax           = 0;
      $grandRevenue       = 0.0;
      $grandAvailable     = 0;
      $days               = 0;

      for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
         $dateStr = $date->toDateString();   // Y-m-d

         // Expected Arrival: bookings arriving on this date (not cancelled, not contra)
         $expectedArrival = DB::table('grpbookingdetails')
            ->where('Property_ID', $pid)
            ->where('ArrDate', $dateStr)
            ->where('Cancel', 'N')
            ->where('ContraDocId', '')
            ->count();

         // Expected Departure: in-house guests whose departure date is this date & not yet checked out
         $expectedDeparture = DB::table('roomocc')
            ->where('propertyid', $pid)
            ->where('DepDate', $dateStr)
            ->whereNull('chkoutdate')
            ->whereNull('type')
            ->count();

         // Stay Over: guests who checked in on or before this date and depart after this date
         $stayOver = DB::table('roomocc')
            ->where('propertyid', $pid)
            ->where('DepDate', '>', $dateStr)
            ->where('chkindate', '<=', $dateStr)
            ->whereNull('type')
            ->count();

         // Occupied Rooms: all current in-house rooms (no checkout)
         $occupiedRooms = DB::table('roomocc')
            ->where('propertyid', $pid)
            ->whereNull('type')
            ->whereNull('chkoutdate')
            ->count();

         // Total Pax: sum of adults for stay-overs on this date
         $totalPax = (int) DB::table('roomocc')
            ->where('propertyid', $pid)
            ->where('DepDate', '>', $dateStr)
            ->where('chkindate', '<=', $dateStr)
            ->whereNull('type')
            ->sum('Adult');

         // Total Revenue: sum of charges posted on this date
         $totalRevenue = (float) DB::table('paycharge')
            ->where('propertyid', $pid)
            ->where('vdate', $dateStr)
            ->sum('amtdr');

         // Derived metrics
         $availableRooms = $totalRooms - $occupiedRooms;
         $arr            = $occupiedRooms > 0 ? round($totalRevenue / $occupiedRooms, 2) : 0;
         $revpar         = $totalRooms > 0     ? round($totalRevenue / $totalRooms, 2)    : 0;

         $rows[] = [
            'date'              => $date->format('l, F j, Y'),   // e.g. "Thursday, November 21, 2024"
            'date_raw'          => $dateStr,
            'total_rooms'       => $totalRooms,
            'expected_arrival'  => $expectedArrival,
            'expected_departure'=> $expectedDeparture,
            'stay_over'         => $stayOver,
            'occupied_rooms'    => $occupiedRooms,
            'total_pax'         => $totalPax,
            'available_rooms'   => $availableRooms < 0 ? 0 : $availableRooms,
            'total_revenue'     => $totalRevenue,
            'arr'               => $arr,
            'revpar'            => $revpar,
         ];

         // Grand totals
         $grandTotalRooms  += $totalRooms;
         $grandArrival     += $expectedArrival;
         $grandDeparture   += $expectedDeparture;
         $grandStayOver    += $stayOver;
         $grandOccupied    += $occupiedRooms;
         $grandPax         += $totalPax;
         $grandRevenue     += $totalRevenue;
         $grandAvailable   += ($availableRooms < 0 ? 0 : $availableRooms);
         $days++;
      }

      $grandArr    = $grandOccupied > 0 ? round($grandRevenue / $grandOccupied, 3) : 0;
      $grandRevpar = $grandTotalRooms > 0  ? round($grandRevenue / $grandTotalRooms, 3) : 0;

      return response()->json([
         'rows'   => $rows,
         'totals' => [
            'total_rooms'        => $grandTotalRooms,
            'expected_arrival'   => $grandArrival,
            'expected_departure' => $grandDeparture,
            'stay_over'          => $grandStayOver,
            'occupied_rooms'     => number_format($grandOccupied, 3),
            'total_pax'          => number_format($grandPax, 3),
            'available_rooms'    => $grandAvailable,
            'total_revenue'      => number_format($grandRevenue, 3),
            'arr'                => number_format($grandArr, 3),
            'revpar'             => number_format($grandRevpar, 3),
         ],
      ]);
   }

   // â”€â”€â”€ Occupancy Forecast Print (DomPDF) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   public function printoccupancyforecast(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate   = $request->input('todate');

      $dateError = $this->validateOccupancyDates($fromdate, $todate);
      if ($dateError) {
         return redirect()->back()->with('error', $dateError);
      }

      $company = Companyreg::where('propertyid', $this->propertyid)->first();

      $export = new \App\Exports\OccupancyForecastExport(
         $this->propertyid,
         $company->comp_name ?? '',
         $fromdate,
         $todate
      );
      $data = $export->getData();

      $pdf = Pdf::loadView('property.print.occupancyforecastprint', [
         'company'  => $company,
         'rows'     => $data['rows'],
         'totals'   => $data['totals'],
         'fromDate' => $fromdate,
         'toDate'   => $todate,
      ])->setPaper('a4', 'landscape');

      return $pdf->stream('OccupancyForecast_' . $fromdate . '_' . $todate . '.pdf');
   }

   // â”€â”€â”€ Occupancy Forecast Excel Export â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
   public function exportoccupancyforecast(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate   = $request->input('todate');

      $dateError = $this->validateOccupancyDates($fromdate, $todate);
      if ($dateError) {
         return redirect()->back()->with('error', $dateError);
      }

      $companyName = Companyreg::where('propertyid', $this->propertyid)->value('comp_name');

      $export = new \App\Exports\OccupancyForecastExport(
         $this->propertyid,
         $companyName ?? '',
         $fromdate,
         $todate
      );
      $export->download();
   }

   /**
    * Validate from/to dates for the occupancy forecast print & export routes.
    * Returns an error message string, or null when the dates are valid.
    */
   private function validateOccupancyDates($fromdate, $todate)
   {
      if (!$fromdate || !$todate) {
         return 'Please provide both From and To dates.';
      }
      if (!strtotime($fromdate) || !strtotime($todate)) {
         return 'Please provide valid From and To dates.';
      }
      if (strtotime($fromdate) > strtotime($todate)) {
         return 'From Date cannot be greater than To Date.';
      }
      return null;
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // GST Consolidated Register â€” unified outward-supply tax view
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function gstconsolidatedregister(Request $request)
   {
      $permission = revokeopen(141511);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $fromdate = $this->ncurdate;
      $company  = Companyreg::where('propertyid', $this->propertyid)->first();

      return view('property.gstconsolidatedregister', [
         'fromdate'  => $fromdate,
         'company'   => $company,
      ]);
   }

   public function gstconsolidatedregisterfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fromdate   = $request->input('fromdate');
      $todate     = $request->input('todate');
      $source     = $request->input('source', 'all'); // all|rooms|pos|banquet

      if (!$fromdate || !$todate) {
         return response()->json(['message' => 'From and To dates required.'], 422);
      }

      $cgstCode = 'CGSS' . $propertyid;
      $sgstCode = 'SGSS' . $propertyid;
      $igstCode = 'IGSS' . $propertyid;

      $results = [];

      // â”€â”€ 1. ROOM REVENUE (paycharge + revmast â†’ sundrymast) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      if (in_array($source, ['all', 'rooms'])) {
         $roomRows = DB::table('paycharge AS P')
            ->leftJoin('revmast AS R', 'P.paycode', '=', 'R.rev_code')
            ->leftJoin('sundrymast AS SM', 'R.sundry', '=', 'SM.sundry_code')
            ->leftJoin('guestfolio AS GF', 'P.folionodocid', '=', 'GF.DocId')
            ->leftJoin('subgroup AS SG', 'GF.Company', '=', 'SG.sub_code')
            ->select(
               DB::raw("'Room Revenue' AS Source"),
               'P.foliono AS BillNo',
               'P.settledate AS VDate',
               DB::raw("TRIM(IFNULL(SG.GSTIN,'')) AS GSTIN"),
               DB::raw("TRIM(IFNULL(SG.Name,'')) AS PartyName"),
               DB::raw('P.foliono AS VNo'),
               'P.onamt AS BaseValue',
               'P.taxper AS TaxPer',
               DB::raw(
                  "SUM(CASE WHEN SM.nature='CGST' THEN P.amtdr-P.amtcr ELSE 0 END) AS CGSTAmt"
               ),
               DB::raw(
                  "SUM(CASE WHEN SM.nature='SGST' THEN P.amtdr-P.amtcr ELSE 0 END) AS SGSTAmt"
               ),
               DB::raw(
                  "SUM(CASE WHEN SM.nature='IGST' THEN P.amtdr-P.amtcr ELSE 0 END) AS IGSTAmt"
               ),
               DB::raw('SUM(P.amtdr-P.amtcr) AS NetAmt')
            )
            ->where('P.propertyid', $propertyid)
            ->where('P.roomtype', 'RO')
            ->whereNotIn('P.vtype', ['ARRES', 'ADRES'])
            ->whereBetween('P.settledate', [$fromdate, $todate])
            ->whereRaw('(P.amtdr - P.amtcr) <> 0')
            ->where('P.foliono', '<>', 0)
            ->where(function ($q) {
               $q->where('P.billno', '<>', 0)->orWhereNull('P.modeset');
            })
            ->where(function ($q) use ($cgstCode, $sgstCode, $igstCode) {
               $q->whereIn('P.paycode', [
                  DB::raw("'{$cgstCode}'"),
                  DB::raw("'{$sgstCode}'"),
                  DB::raw("'{$igstCode}'")
               ]);
            })
            ->groupBy('P.foliono', 'P.settledate', 'P.folionodocid', 'P.onamt', 'P.taxper')
            ->havingRaw('ABS(CGSTAmt + SGSTAmt + IGSTAmt) > 0')
            ->get();

         foreach ($roomRows as $r) {
            $results[] = [
               'Source'    => 'Room',
               'BillNo'    => $r->BillNo,
               'VDate'     => $r->VDate,
               'GSTIN'     => $r->GSTIN,
               'PartyName' => $r->PartyName,
               'BaseValue' => (float) $r->BaseValue,
               'TaxPer'    => (float) $r->TaxPer,
               'CGSTAmt'   => (float) $r->CGSTAmt,
               'SGSTAmt'   => (float) $r->SGSTAmt,
               'IGSTAmt'   => (float) $r->IGSTAmt,
               'TotalTax'  => (float) ($r->CGSTAmt + $r->SGSTAmt + $r->IGSTAmt),
               'NetAmt'    => (float) $r->NetAmt,
            ];
         }
      }

      // â”€â”€ 2. POS â€” SUNTRAN (tax lines per docid) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      if (in_array($source, ['all', 'pos'])) {
         $posTax = DB::table('suntran AS ST')
            ->join('revmast AS R', 'ST.revcode', '=', 'R.rev_code')
            ->join('sundrymast AS SM', 'R.sundry', '=', 'SM.sundry_code')
            ->join('sale1 AS S1', 'ST.docid', '=', 'S1.DocId')
            ->leftJoin('subgroup AS SG', 'S1.party', '=', 'SG.sub_code')
            ->select(
               DB::raw("'POS' AS Source"),
               'S1.VNo AS BillNo',
               'ST.vdate AS VDate',
               DB::raw("TRIM(IFNULL(SG.GSTIN,'')) AS GSTIN"),
               DB::raw("TRIM(IFNULL(SG.Name,'')) AS PartyName"),
               'ST.baseamount AS BaseValue',
               'ST.svalue AS TaxPer',
               DB::raw(
                  "SUM(CASE WHEN SM.nature='CGST' THEN ST.amount ELSE 0 END) AS CGSTAmt"
               ),
               DB::raw(
                  "SUM(CASE WHEN SM.nature='SGST' THEN ST.amount ELSE 0 END) AS SGSTAmt"
               ),
               DB::raw(
                  "SUM(CASE WHEN SM.nature='IGST' THEN ST.amount ELSE 0 END) AS IGSTAmt"
               ),
               'S1.NetAmt AS NetAmt'
            )
            ->where('ST.propertyid', $propertyid)
            ->where('ST.delflag', 'N')
            ->whereBetween('ST.vdate', [$fromdate, $todate])
            ->where('R.field_type', 'T')
            ->where(function ($q) use ($cgstCode, $sgstCode, $igstCode) {
               $q->whereIn('R.rev_code', [
                  DB::raw("'{$cgstCode}'"),
                  DB::raw("'{$sgstCode}'"),
                  DB::raw("'{$igstCode}'")
               ]);
            })
            ->groupBy('ST.docid', 'ST.baseamount', 'ST.svalue', 'S1.VNo', 'S1.VDate', 'S1.NetAmt', 'SG.GSTIN', 'SG.Name')
            ->havingRaw('ABS(CGSTAmt + SGSTAmt + IGSTAmt) > 0')
            ->get();

         foreach ($posTax as $r) {
            $results[] = [
               'Source'    => 'POS',
               'BillNo'    => $r->BillNo,
               'VDate'     => $r->VDate,
               'GSTIN'     => $r->GSTIN,
               'PartyName' => $r->PartyName,
               'BaseValue' => (float) $r->BaseValue,
               'TaxPer'    => (float) $r->TaxPer,
               'CGSTAmt'   => (float) $r->CGSTAmt,
               'SGSTAmt'   => (float) $r->SGSTAmt,
               'IGSTAmt'   => (float) $r->IGSTAmt,
               'TotalTax'  => (float) ($r->CGSTAmt + $r->SGSTAmt + $r->IGSTAmt),
               'NetAmt'    => (float) $r->NetAmt,
            ];
         }
      }

      // â”€â”€ 3. BANQUET â€” SUNTRANH (tax lines via sundrytype) â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      if (in_array($source, ['all', 'banquet'])) {
         $banqTax = DB::table('suntranh AS SH')
            ->join('sundrytype AS ST', function ($j) use ($propertyid) {
               $j->on('SH.revcode', '=', 'ST.rev_code')
                  ->where('ST.vtype', "BANQ{$propertyid}")
                  ->where('ST.propertyid', $propertyid);
            })
            ->leftJoin('hallbook AS HB', 'SH.docid', '=', 'HB.DocId')
            ->leftJoin('subgroup AS SG', 'HB.PartyCode', '=', 'SG.sub_code')
            ->select(
               DB::raw("'Banquet' AS Source"),
               'HB.VNo AS BillNo',
               'SH.vdate AS VDate',
               DB::raw("TRIM(IFNULL(SG.GSTIN,'')) AS GSTIN"),
               DB::raw("TRIM(IFNULL(SG.Name,'')) AS PartyName"),
               'SH.baseamount AS BaseValue',
               'SH.svalue AS TaxPer',
               DB::raw(
                  "SUM(CASE WHEN ST.nature='CGST' THEN SH.amount ELSE 0 END) AS CGSTAmt"
               ),
               DB::raw(
                  "SUM(CASE WHEN ST.nature='SGST' THEN SH.amount ELSE 0 END) AS SGSTAmt"
               ),
               DB::raw(
                  "SUM(CASE WHEN ST.nature='IGST' THEN SH.amount ELSE 0 END) AS IGSTAmt"
               ),
               'HB.NetAmt AS NetAmt'
            )
            ->where('SH.propertyid', $propertyid)
            ->where('SH.delflag', 'N')
            ->whereBetween('SH.vdate', [$fromdate, $todate])
            ->where(function ($q) {
               $q->where('SH.svalue', '>', 0)
                  ->where('SH.amount', '>', 0);
            })
            ->groupBy('SH.docid', 'SH.baseamount', 'SH.svalue', 'HB.VNo', 'HB.VDate', 'HB.NetAmt', 'SG.GSTIN', 'SG.Name')
            ->havingRaw('ABS(CGSTAmt + SGSTAmt + IGSTAmt) > 0')
            ->get();

         foreach ($banqTax as $r) {
            $results[] = [
               'Source'    => 'Banquet',
               'BillNo'    => $r->BillNo,
               'VDate'     => $r->VDate,
               'GSTIN'     => $r->GSTIN,
               'PartyName' => $r->PartyName,
               'BaseValue' => (float) $r->BaseValue,
               'TaxPer'    => (float) $r->TaxPer,
               'CGSTAmt'   => (float) $r->CGSTAmt,
               'SGSTAmt'   => (float) $r->SGSTAmt,
               'IGSTAmt'   => (float) $r->IGSTAmt,
               'TotalTax'  => (float) ($r->CGSTAmt + $r->SGSTAmt + $r->IGSTAmt),
               'NetAmt'    => (float) $r->NetAmt,
            ];
         }
      }

      // â”€â”€ SUMMARY â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $summary = [];
      foreach ($results as $row) {
         $key = ($row['GSTIN'] ?: 'UNREGISTERED') . '|' . $row['TaxPer'];
         if (!isset($summary[$key])) {
            $summary[$key] = [
               'GSTIN'     => $row['GSTIN'] ?: 'UNREGISTERED',
               'TaxPer'    => $row['TaxPer'],
               'BaseValue' => 0,
               'CGSTAmt'   => 0,
               'SGSTAmt'   => 0,
               'IGSTAmt'   => 0,
               'TotalTax'  => 0,
               'BillCount' => 0,
            ];
         }
         $summary[$key]['BaseValue'] += $row['BaseValue'];
         $summary[$key]['CGSTAmt']   += $row['CGSTAmt'];
         $summary[$key]['SGSTAmt']   += $row['SGSTAmt'];
         $summary[$key]['IGSTAmt']   += $row['IGSTAmt'];
         $summary[$key]['TotalTax']  += $row['TotalTax'];
         $summary[$key]['BillCount']++;
      }
      usort($summary, fn($a, $b) => strcmp($a['GSTIN'], $b['GSTIN']) ?: $a['TaxPer'] <=> $b['TaxPer']);

      // grand total
      $grand = ['BaseValue' => 0, 'CGSTAmt' => 0, 'SGSTAmt' => 0, 'IGSTAmt' => 0, 'TotalTax' => 0];
      foreach ($summary as $s) {
         $grand['BaseValue'] += $s['BaseValue'];
         $grand['CGSTAmt']   += $s['CGSTAmt'];
         $grand['SGSTAmt']   += $s['SGSTAmt'];
         $grand['IGSTAmt']   += $s['IGSTAmt'];
         $grand['TotalTax']  += $s['TotalTax'];
      }

      return response()->json([
         'data'    => $results,
         'summary' => $summary,
         'grand'   => $grand,
      ]);
   }

   public function printgstconsolidatedregister(Request $request)
   {
      $data = $this->gstconsolidatedregisterfetch($request);
      if ($data instanceof \Illuminate\Http\JsonResponse) {
         $payload = $data->getData(true);
      } else {
         return $data; // redirect/error
      }

      $company  = Companyreg::where('propertyid', $this->propertyid)->first();
      $statename = '';
      if ($company) {
         $statename = DB::table('states')
            ->where('propertyid', $this->propertyid)
            ->where('state_code', $company->state_code)
            ->value('name') ?? '';
      }

      return view('property.print.printgstconsolidatedregister', [
         'data'      => $payload['data'] ?? [],
         'summary'   => $payload['summary'] ?? [],
         'grand'     => $payload['grand'] ?? [],
         'company'   => $company,
         'statename' => $statename,
         'fromdate'  => $request->input('fromdate'),
         'todate'    => $request->input('todate'),
         'source'    => $request->input('source', 'all'),
      ]);
   }

   public function exportgstconsolidatedregister(Request $request)
   {
      $data = $this->gstconsolidatedregisterfetch($request);
      if ($data instanceof \Illuminate\Http\JsonResponse) {
         $payload = $data->getData(true);
      } else {
         return $data;
      }

      $companyName = Companyreg::where('propertyid', $this->propertyid)->value('comp_name') ?? '';
      $export = new \App\Exports\GSTConsolidatedRegisterExport(
         $this->propertyid,
         $companyName,
         $request->input('fromdate'),
         $request->input('todate'),
         $payload['data'] ?? [],
         $payload['summary'] ?? [],
         $payload['grand'] ?? [],
      );

      return $export->download();
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Night Audit Reconciliation Report
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function nightauditrecon(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $ncurdate = $this->ncurdate;
      $company  = Companyreg::where('propertyid', $this->propertyid)->first();

      return view('property.nightauditrecon', [
         'fordate'  => $ncurdate,
         'company'  => $company,
      ]);
   }

   public function nightauditreconfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fordate    = $request->input('fordate');

      if (!$fordate) {
         return response()->json(['message' => 'Date required.'], 422);
      }

      $prevdate = date('Y-m-d', strtotime($fordate . ' -1 day'));

      // â”€â”€ 1. ROOM OCCUPANCY SNAPSHOT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $occupancy = DB::table('roomocc AS ro')
         ->select(
            'ro.roomno',
            'ro.name AS guestname',
            'ro.roomcat',
            'ro.roomtype',
            'ro.roomrate',
            'ro.chkindate',
            'ro.depdate',
            DB::raw('IFNULL(ro.chkoutdate, \'ACTIVE\') AS chkoutstatus')
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.chkindate', '<=', $fordate)
         ->where(function ($q) use ($fordate) {
            $q->whereNull('ro.chkoutdate')
              ->orWhere('ro.chkoutdate', '>', $fordate);
         })
         ->orderBy('ro.roomno')
         ->get();

      $totalRooms    = $occupancy->count();
      $activeGuests  = $occupancy->filter(fn($r) => $r->chkoutstatus === 'ACTIVE')->count();
      $checkedOut    = $totalRooms - $activeGuests;

      // â”€â”€ 2. CHARGES POSTED FOR THE DATE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $charges = DB::table('paycharge AS P')
         ->select(
            'P.vtype',
            DB::raw('COUNT(DISTINCT P.docid) AS billcount'),
            DB::raw('SUM(P.amtdr - P.amtcr) AS netamount'),
            DB::raw('SUM(P.amtdr) AS totaldr'),
            DB::raw('SUM(P.amtcr) AS totalcr')
         )
         ->where('P.propertyid', $propertyid)
         ->where('P.vdate', $fordate)
         ->whereRaw('(P.amtdr - P.amtcr) <> 0')
         ->groupBy('P.vtype')
         ->orderByDesc('netamount')
         ->get();

      $revenueByType = [];
      foreach ($charges as $c) {
         $revenueByType[] = [
            'vtype'      => $c->vtype,
            'billcount'  => (int) $c->billcount,
            'netamount'  => (float) $c->netamount,
            'totaldr'    => (float) $c->totaldr,
            'totalcr'    => (float) $c->totalcr,
         ];
      }

      $totalRevenue = $charges->sum('netamount');

      // â”€â”€ 3. SETTLEMENT STATUS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $unsettled = DB::table('paycharge AS P')
         ->join('roomocc AS ro', function ($j) use ($propertyid) {
            $j->on('P.folionodocid', '=', 'ro.docid')
               ->where('ro.propertyid', $propertyid)
               ->whereNull('ro.type');
         })
         ->select(
            'ro.roomno',
            'ro.name AS guestname',
            DB::raw('SUM(P.amtdr - P.amtcr) AS balance')
         )
         ->where('P.propertyid', $propertyid)
         ->where('P.vdate', '<=', $fordate)
         ->where('P.foliono', '<>', 0)
         ->where(function ($q) {
            $q->where('P.billno', '<>', 0)->orWhereNull('P.modeset');
         })
         ->groupBy('P.folionodocid', 'ro.roomno', 'ro.name')
         ->havingRaw('ABS(SUM(P.amtdr - P.amtcr)) > 0.01')
         ->orderByDesc('balance')
         ->get();

      // â”€â”€ 4. COMPARISON WITH PRIOR NIGHT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $prevCharges = DB::table('paycharge')
         ->selectRaw('SUM(amtdr - amtcr) AS total')
         ->where('propertyid', $propertyid)
         ->where('vdate', $prevdate)
         ->whereRaw('(amtdr - amtcr) <> 0')
         ->value('total') ?? 0;

      $prevOccupied = DB::table('roomocc')
         ->where('propertyid', $propertyid)
         ->whereNull('type')
         ->where('chkindate', '<=', $prevdate)
         ->where(function ($q) use ($prevdate) {
            $q->whereNull('chkoutdate')
              ->orWhere('chkoutdate', '>', $prevdate);
         })
         ->count();

      // â”€â”€ 5. NIGHT AUDIT LOG ENTRIES â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $naLog = DB::table('nightauditlog')
         ->where('propertyid', $propertyid)
         ->where('ncurdate', $fordate)
         ->orderBy('u_entdt')
         ->get(['narration', 'u_name', 'u_entdt']);

      return response()->json([
         'occupancy' => [
            'total'   => $totalRooms,
            'active'  => $activeGuests,
            'co'      => $checkedOut,
            'rooms'   => $occupancy,
         ],
         'revenue' => [
            'bytype'  => $revenueByType,
            'total'   => $totalRevenue,
         ],
         'unsettled' => $unsettled,
         'prev' => [
            'revenue'   => (float) $prevCharges,
            'occupied'  => $prevOccupied,
         ],
         'nalog' => $naLog,
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // AMR Morning Report
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function amrmorningreport(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.amrmorningreport', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function amrmorningreportfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fordate    = $request->input('fordate');
      if (!$fordate) return response()->json(['message' => 'Date required.'], 422);

      // â”€â”€ 1. ROOM TYPE OCCUPANCY â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $roomTypes = DB::table('room_cat')
         ->where('propertyid', $propertyid)
         ->orderBy('cat_code')
         ->get(['cat_code', 'name', 'noofrooms']);

      $occupancyByType = [];
      foreach ($roomTypes as $rt) {
         $occupied = DB::table('roomocc')
            ->where('propertyid', $propertyid)
            ->whereNull('type')
            ->where('roomcat', $rt->cat_code)
            ->where('chkindate', '<=', $fordate)
            ->where(function ($q) use ($fordate) {
               $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>', $fordate);
            })->count();
         $total = (int) ($rt->noofrooms ?? 0);
         $occupancyByType[] = [
            'category' => $rt->name,
            'total'    => $total,
            'occupied' => $occupied,
            'vacant'   => $total - $occupied,
            'occ_pct'  => $total > 0 ? round($occupied / $total * 100, 1) : 0,
         ];
      }

      $totalRooms = array_sum(array_column($occupancyByType, 'total'));
      $totalOccupied = array_sum(array_column($occupancyByType, 'occupied'));
      $overallOccPct = $totalRooms > 0 ? round($totalOccupied / $totalRooms * 100, 1) : 0;

      // â”€â”€ 2. ROOM STATUS BREAKDOWN â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $roomStatus = DB::table('room_mast')
         ->where('propertyid', $propertyid)
         ->where('type', 'RO')
         ->selectRaw('room_stat, COUNT(*) AS cnt')
         ->groupBy('room_stat')
         ->get();

      $statusMap = [];
      foreach ($roomStatus as $rs) {
         $statusMap[$rs->room_stat ?? 'U'] = (int) $rs->cnt;
      }

      // â”€â”€ 3. EXPECTED ARRIVALS (reservations with ArrDate = fordate, not checked in) â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $arrivals = DB::table('grpbookingdetails AS gb')
         ->leftJoin('room_mast AS rm', function ($j) use ($propertyid) {
            $j->on('gb.RoomNo', '=', 'rm.rcode')->where('rm.propertyid', $propertyid);
         })
         ->leftJoin('subgroup AS sg', 'gb.PartyCode', '=', 'sg.sub_code')
         ->leftJoin('guestprof AS gp', 'gb.GuestProf', '=', 'gp.guestcode')
         ->select(
            'gb.RoomNo',
            'gb.ArrDate',
            'gb.DepDate',
            'gb.NoOfRooms',
            'gb.NoOfAdults',
            'gb.NoOfChild',
            DB::raw('IFNULL(sg.name, gb.PartyName) AS CompanyName'),
            DB::raw('IFNULL(gp.name, gb.GuestName) AS GuestName'),
            'gb.Comments',
            'gb.ResStatus'
         )
         ->where('gb.Property_ID', $propertyid)
         ->where('gb.Cancel', 'N')
         ->where('gb.ArrDate', $fordate)
         ->where('gb.ContraDocId', '')
         ->orderBy('gb.RoomNo')
         ->get();

      // â”€â”€ 4. EXPECTED DEPARTURES (roomocc with depdate = fordate) â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $departures = DB::table('roomocc AS ro')
         ->leftJoin('guestprof AS gp', 'ro.guestprof', '=', 'gp.guestcode')
         ->select(
            'ro.roomno',
            'ro.name AS GuestName',
            'ro.roomrate',
            'ro.depdate',
            DB::raw('IFNULL(gp.complimentry, "N") AS Complimentary')
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.depdate', $fordate)
         ->whereNull('ro.chkoutdate')
         ->orderBy('ro.roomno')
         ->get();

      // â”€â”€ 5. TODAY REVENUE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $todayRevenue = DB::table('paycharge')
         ->selectRaw('vtype, COUNT(DISTINCT docid) AS bills, SUM(amtdr-amtcr) AS net')
         ->where('propertyid', $propertyid)
         ->where('vdate', $fordate)
         ->whereRaw('(amtdr-amtcr) <> 0')
         ->groupBy('vtype')
         ->orderByDesc('net')
         ->get();

      $totalRevenue = $todayRevenue->sum('net');

      // â”€â”€ 6. IN-HOUSE SUMMARY â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
      $inHouse = DB::table('roomocc')
         ->where('propertyid', $propertyid)
         ->whereNull('type')
         ->where('chkindate', '<=', $fordate)
         ->where(function ($q) use ($fordate) {
            $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>', $fordate);
         })
         ->count();

      return response()->json([
         'occupancy' => [
            'bytype'     => $occupancyByType,
            'total'      => $totalRooms,
            'occupied'   => $totalOccupied,
            'occ_pct'    => $overallOccPct,
            'roomstatus' => $statusMap,
            'inhouse'    => $inHouse,
         ],
         'arrivals'   => $arrivals,
         'departures' => $departures,
         'revenue'    => [
            'bytype' => $todayRevenue,
            'total'  => $totalRevenue,
         ],
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Checked-In Guest Detail Report
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function checkedinguestdetail(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.checkedinguestdetail', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function checkedinguestdetailfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fordate    = $request->input('fordate');
      if (!$fordate) return response()->json(['message' => 'Date required.'], 422);

      $guests = DB::table('roomocc AS ro')
         ->leftJoin('guestprof AS gp', 'ro.guestprof', '=', 'gp.guestcode')
         ->leftJoin('guestfolio AS gf', function ($j) {
            $j->on('gf.docid', '=', 'ro.docid')->on('gf.sno1', '=', 'ro.sno1');
         })
         ->leftJoin('subgroup AS sg', 'gf.company', '=', 'sg.sub_code')
         ->leftJoin('subgroup AS ta', 'gf.travelagent', '=', 'ta.sub_code')
         ->leftJoin('room_cat AS rc', 'ro.roomcat', '=', 'rc.cat_code')
         ->select(
            'ro.roomno',
            'ro.name AS GuestName',
            DB::raw('IFNULL(gp.nationality, "") AS Nationality'),
            DB::raw('IFNULL(gp.idno, "") AS IDNo'),
            DB::raw('IFNULL(gp.idtype, "") AS IDType'),
            DB::raw('IFNULL(gp.mobile, "") AS Mobile'),
            DB::raw('IFNULL(sg.name, "") AS Company'),
            DB::raw('IFNULL(ta.name, "") AS TravelAgent'),
            DB::raw('IFNULL(rc.name, "") AS RoomType'),
            'ro.roomrate',
            'ro.chkindate',
            'ro.depdate',
            DB::raw('DATEDIFF(?, ro.chkindate) + 1 AS NightsStayed'),
            DB::raw('IFNULL(ro.chkoutdate, "") AS CheckOut'),
            DB::raw('IFNULL(gp.adults, ro.adults) AS Adults'),
            DB::raw('IFNULL(gp.child, ro.child) AS Children'),
            'ro.leaderyn',
            'ro.sno1'
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.chkindate', '<=', $fordate)
         ->where(function ($q) use ($fordate) {
            $q->whereNull('ro.chkoutdate')->orWhere('ro.chkoutdate', '>', $fordate);
         })
         ->orderBy('ro.roomno')
         ->setBindings([$fordate])
         ->get();

      // Balance per folio
      $balances = [];
      $foliodocids = $guests->pluck('roomno')->unique();
      if ($guests->isNotEmpty()) {
         $balRows = DB::table('paycharge')
            ->select('folionodocid', DB::raw('SUM(amtdr-amtcr) AS balance'))
            ->where('propertyid', $propertyid)
            ->where('foliono', '<>', 0)
            ->groupBy('folionodocid')
            ->get();
         foreach ($balRows as $b) {
            $balances[$b->folionodocid] = (float) $b->balance;
         }
      }

      $result = [];
      foreach ($guests as $g) {
         $result[] = [
            'RoomNo'      => $g->roomno,
            'GuestName'   => $g->GuestName,
            'Nationality' => $g->Nationality,
            'IDType'      => $g->IDType,
            'IDNo'        => $g->IDNo,
            'Mobile'      => $g->Mobile,
            'Company'     => $g->Company,
            'TravelAgent' => $g->TravelAgent,
            'RoomType'    => $g->RoomType,
            'Rate'        => (float) $g->roomrate,
            'CheckIn'     => $g->chkindate,
            'Departure'   => $g->depdate,
            'Nights'      => (int) $g->NightsStayed,
            'CheckOut'    => $g->CheckOut,
            'Adults'      => (int) $g->Adults,
            'Children'    => (int) $g->Children,
            'Leader'      => $g->leaderyn === 'Y' ? 'Yes' : 'No',
            'Balance'     => $balances[$g->docid ?? $g->roomno] ?? 0,
         ];
      }

      $totalGuests = $guests->count();
      $totalAdults = $guests->sum('adults');
      $totalChildren = $guests->sum('child');
      $totalRevenue = array_sum($balances);

      return response()->json([
         'data' => $result,
         'summary' => [
            'totalGuests'  => $totalGuests,
            'totalAdults'  => $totalAdults,
            'totalChildren'=> $totalChildren,
            'totalBalance' => $totalRevenue,
         ],
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Room-Wise Room Revenue Report
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function roomwiseroomrevenue(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.roomwiseroomrevenue', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function roomwiseroomrevenuefetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fromdate   = $request->input('fromdate');
      $todate     = $request->input('todate');
      if (!$fromdate || !$todate) return response()->json(['message' => 'From and To dates required.'], 422);

      // All room charges (RC + REV) + POS charges (PPOS + IPOS) + tax per room
      $rows = DB::table('paycharge AS P')
         ->leftJoin('roomocc AS ro', function ($j) use ($propertyid) {
            $j->on('P.folionodocid', '=', 'ro.docid')
               ->on('P.sno1', '=', 'ro.sno1')
               ->where('ro.propertyid', $propertyid)
               ->whereNull('ro.type');
         })
         ->select(
            'P.roomno',
            DB::raw('MAX(ro.name) AS guestname'),
            DB::raw('MAX(ro.roomcat) AS roomcat'),
            DB::raw('MAX(ro.roomtype) AS roomtype'),
            DB::raw('SUM(CASE WHEN P.vtype IN ("RC","REV") THEN P.amtdr-P.amtcr ELSE 0 END) AS roomcharge'),
            DB::raw('SUM(CASE WHEN P.vtype IN ("PPOS","IPOS") THEN P.amtdr-P.amtcr ELSE 0 END) AS poscharge'),
            DB::raw('SUM(CASE WHEN P.vtype = "CGSS" OR P.vtype = "SGSS" OR P.vtype = "IGSS" OR P.paycode LIKE "CGSS%" OR P.paycode LIKE "SGSS%" OR P.paycode LIKE "IGSS%" THEN P.amtdr-P.amtcr ELSE 0 END) AS tax'),
            DB::raw('SUM(CASE WHEN P.vtype = "DISC" THEN P.amtdr-P.amtcr ELSE 0 END) AS discount'),
            DB::raw('SUM(P.amtdr-P.amtcr) AS netamount')
         )
         ->where('P.propertyid', $propertyid)
         ->whereBetween('P.vdate', [$fromdate, $todate])
         ->where('P.roomtype', 'RO')
         ->whereNotIn('P.vtype', ['ARRES', 'ADRES'])
         ->groupBy('P.roomno')
         ->orderBy('P.roomno')
         ->get();

      $totalRoom = 0; $totalPos = 0; $totalTax = 0; $totalDisc = 0; $totalNet = 0;
      $result = [];
      foreach ($rows as $r) {
         $rc = (float) $r->roomcharge;
         $pc = (float) $r->poscharge;
         $tx = (float) $r->tax;
         $dc = (float) $r->discount;
         $nt = (float) $r->netamount;
         $totalRoom += $rc; $totalPos += $pc; $totalTax += $tx; $totalDisc += $dc; $totalNet += $nt;
         $result[] = [
            'RoomNo'     => $r->roomno,
            'GuestName'  => $r->guestname,
            'RoomType'   => $r->roomcat,
            'RoomCharge' => $rc,
            'POSCharge'  => $pc,
            'Tax'        => $tx,
            'Discount'   => $dc,
            'NetAmount'  => $nt,
         ];
      }

      return response()->json([
         'data' => $result,
         'summary' => [
            'totalRoom' => $totalRoom,
            'totalPos'  => $totalPos,
            'totalTax'  => $totalTax,
            'totalDisc' => $totalDisc,
            'totalNet'  => $totalNet,
         ],
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Form C â€” Foreign Guest Registration (Compliance)
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function formcreport(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.formcreport', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function formcreportfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fromdate   = $request->input('fromdate');
      $todate     = $request->input('todate');
      if (!$fromdate || !$todate) return response()->json(['message' => 'From and To dates required.'], 422);

      // Form C requires foreign guests â€” filter by nationality != India (country_code != 'IN')
      // or idtype = 'Passport'
      $guests = DB::table('roomocc AS ro')
         ->leftJoin('guestprof AS gp', 'ro.guestprof', '=', 'gp.guestcode')
         ->leftJoin('guestfolio AS gf', function ($j) {
            $j->on('gf.docid', '=', 'ro.docid')->on('gf.sno1', '=', 'ro.sno1');
         })
         ->leftJoin('subgroup AS sg', 'gf.company', '=', 'sg.sub_code')
         ->leftJoin('countries AS c', 'gp.nationality', '=', 'c.nationality')
         ->select(
            'ro.roomno',
            'ro.name AS GuestName',
            DB::raw('IFNULL(gp.sex, "") AS Sex'),
            DB::raw('IFNULL(gp.nationality, "") AS Nationality'),
            DB::raw('IFNULL(c.name, "") AS Country'),
            DB::raw('IFNULL(gp.idtype, "") AS IDType'),
            DB::raw('IFNULL(gp.idno, "") AS IDNo'),
            DB::raw('IFNULL(gp.idate, "") AS IDate'),
            DB::raw('IFNULL(gp(passport_no, gp.idno), "") AS PassportNo'),
            DB::raw('IFNULL(gp.visa_no, "") AS VisaNo'),
            DB::raw('IFNULL(gp.visa_date, "") AS VisaDate'),
            DB::raw('IFNULL(gp.arrivedate, "") AS ArriveDate'),
            DB::raw('IFNULL(gp.mobile, "") AS Mobile'),
            DB::raw('IFNULL(gp.address, "") AS Address'),
            DB::raw('IFNULL(sg.name, "") AS Company'),
            'ro.chkindate',
            'ro.depdate',
            DB::raw('IFNULL(ro.chkoutdate, "") AS CheckOut')
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.chkindate', '<=', $todate)
         ->where(function ($q) use ($todate) {
            $q->whereNull('ro.chkoutdate')->orWhere('ro.chkoutdate', '>', $fromdate);
         })
         ->where(function ($q) {
            $q->where('gp.idtype', 'Passport')
              ->orWhere('gp.nationality', '!=', 'Indian')
              ->orWhere('gp.nationality', '!=', 'IN');
         })
         ->orderBy('ro.chkindate')
         ->orderBy('ro.roomno')
         ->get();

      $result = [];
      foreach ($guests as $g) {
         $result[] = [
            'RoomNo'      => $g->roomno,
            'GuestName'   => $g->GuestName,
            'Sex'         => $g->Sex,
            'Nationality' => $g->Nationality,
            'Country'     => $g->Country,
            'IDType'      => $g->IDType,
            'IDNo'        => $g->IDNo,
            'PassportNo'  => $g->PassportNo,
            'VisaNo'      => $g->VisaNo,
            'VisaDate'    => $g->VisaDate,
            'ArriveDate'  => $g->ArriveDate,
            'Mobile'      => $g->Mobile,
            'Address'     => $g->Address,
            'Company'     => $g->Company,
            'CheckIn'     => $g->chkindate,
            'Departure'   => $g->depdate,
            'CheckOut'    => $g->CheckOut,
         ];
      }

      return response()->json([
         'data'    => $result,
         'summary' => ['total' => count($result)],
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // FO Settlement Report (SettleRep parity)
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function fosettlereport(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.fosettlereport', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function fosettlereportfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fromdate   = $request->input('fromdate');
      $todate     = $request->input('todate');
      if (!$fromdate || !$todate) return response()->json(['message' => 'From and To dates required.'], 422);

      // Settlements = paycharge rows with modeset = 'S' (settled) within date range
      $settlements = DB::table('paycharge AS P')
         ->leftJoin('revmast AS R', 'P.paycode', '=', 'R.rev_code')
         ->leftJoin('roomocc AS ro', function ($j) use ($propertyid) {
            $j->on('P.folionodocid', '=', 'ro.docid')
               ->where('ro.propertyid', $propertyid)
               ->whereNull('ro.type');
         })
         ->select(
            'P.roomno',
            DB::raw('MAX(ro.name) AS guestname'),
            'P.settledate',
            DB::raw('MAX(P.billno) AS billno'),
            DB::raw('MAX(P.foliono) AS foliono'),
            DB::raw("MAX(CASE WHEN R.pay_type = 'Cash' THEN P.amtcr ELSE 0 END) AS cash"),
            DB::raw("MAX(CASE WHEN R.pay_type = 'Room' THEN P.amtcr ELSE 0 END) AS room"),
            DB::raw("MAX(CASE WHEN R.pay_type = 'Company' THEN P.amtcr ELSE 0 END) AS company_pay"),
            DB::raw("MAX(CASE WHEN R.pay_type = 'UPI' THEN P.amtcr ELSE 0 END) AS upi"),
            DB::raw("MAX(CASE WHEN R.pay_type = 'Credit Card' THEN P.amtcr ELSE 0 END) AS card"),
            DB::raw('SUM(P.amtcr) AS totalpaid'),
            DB::raw('MAX(P.u_name) AS settledby')
         )
         ->where('P.propertyid', $propertyid)
         ->where('P.modeset', 'S')
         ->whereBetween('P.settledate', [$fromdate, $todate])
         ->where('P.roomtype', 'RO')
         ->where('P.amtcr', '>', 0)
         ->groupBy('P.roomno', 'P.settledate', 'P.folionodocid')
         ->orderBy('P.settledate')
         ->orderBy('P.roomno')
         ->get();

      // Compute outstanding for each folio
      $result = [];
      foreach ($settlements as $s) {
         $result[] = [
            'RoomNo'     => $s->roomno,
            'GuestName'  => $s->guestname,
            'SettleDate' => $s->settledate,
            'BillNo'     => $s->billno,
            'FolioNo'    => $s->folioNo,
            'Cash'       => (float) $s->cash,
            'Room'       => (float) $s->room,
            'Company'    => (float) $s->company_pay,
            'UPI'        => (float) $s->upi,
            'Card'       => (float) $s->card,
            'TotalPaid'  => (float) $s->totalpaid,
            'SettledBy'  => $s->settledby,
         ];
      }

      $summary = [
         'totalCash'   => $settlements->sum('cash'),
         'totalRoom'   => $settlements->sum('room'),
         'totalCompany'=> $settlements->sum('company_pay'),
         'totalUPI'    => $settlements->sum('upi'),
         'totalCard'   => $settlements->sum('card'),
         'totalPaid'   => $settlements->sum('totalpaid'),
         'count'       => $settlements->count(),
      ];

      return response()->json(['data' => $result, 'summary' => $summary]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Reservation Status Dashboard
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function reservationstatus(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.reservationstatus', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function reservationstatusfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fordate    = $request->input('fordate');
      if (!$fordate) return response()->json(['message' => 'Date required.'], 422);

      // â”€â”€ 1. TODAY ARRIVALS (reservations with ArrDate = fordate, not checked in) â”€â”€
      $arrivals = DB::table('grpbookingdetails AS gb')
         ->leftJoin('room_mast AS rm', function ($j) use ($propertyid) {
            $j->on('gb.RoomNo', '=', 'rm.rcode')->where('rm.propertyid', $propertyid);
         })
         ->leftJoin('subgroup AS sg', 'gb.PartyCode', '=', 'sg.sub_code')
         ->leftJoin('guestprof AS gp', 'gb.GuestProf', '=', 'gp.guestcode')
         ->select(
            'gb.DocId', 'gb.RoomNo', 'gb.ArrDate', 'gb.DepDate',
            'gb.NoOfRooms', 'gb.NoOfAdults', 'gb.NoOfChild',
            DB::raw('IFNULL(sg.name, gb.PartyName) AS CompanyName'),
            DB::raw('IFNULL(gp.name, gb.GuestName) AS GuestName'),
            'gb.ResStatus', 'gb.Comments'
         )
         ->where('gb.Property_ID', $propertyid)
         ->where('gb.Cancel', 'N')
         ->where('gb.ArrDate', $fordate)
         ->where('gb.ContraDocId', '')
         ->orderBy('gb.RoomNo')
         ->get();

      // â”€â”€ 2. IN-HOUSE (roomocc active) â”€â”€
      $inhouse = DB::table('roomocc AS ro')
         ->leftJoin('guestprof AS gp', 'ro.guestprof', '=', 'gp.guestcode')
         ->leftJoin('guestfolio AS gf', function ($j) {
            $j->on('gf.docid', '=', 'ro.docid')->on('gf.sno1', '=', 'ro.sno1');
         })
         ->leftJoin('subgroup AS sg', 'gf.company', '=', 'sg.sub_code')
         ->select(
            'ro.docid', 'ro.roomno', 'ro.name AS GuestName',
            'ro.roomrate', 'ro.chkindate', 'ro.depdate',
            DB::raw('IFNULL(sg.name, "") AS Company'),
            DB::raw('IFNULL(ro.roomcat, "") AS RoomType')
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.chkindate', '<=', $fordate)
         ->where(function ($q) use ($fordate) {
            $q->whereNull('ro.chkoutdate')->orWhere('ro.chkoutdate', '>', $fordate);
         })
         ->orderBy('ro.roomno')
         ->get();

      // â”€â”€ 3. TODAY DEPARTURES â”€â”€
      $departures = DB::table('roomocc AS ro')
         ->leftJoin('guestprof AS gp', 'ro.guestprof', '=', 'gp.guestcode')
         ->select(
            'ro.roomno', 'ro.name AS GuestName', 'ro.roomrate',
            'ro.depdate',
            DB::raw('IFNULL(ro.chkoutdate, "") AS CheckOut')
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.depdate', $fordate)
         ->orderBy('ro.roomno')
         ->get();

      // â”€â”€ 4. CANCELLATIONS TODAY â”€â”€
      $cancellations = DB::table('grpbookingdetails AS gb')
         ->leftJoin('subgroup AS sg', 'gb.PartyCode', '=', 'sg.sub_code')
         ->select(
            'gb.DocId', 'gb.RoomNo', 'gb.ArrDate', 'gb.DepDate',
            DB::raw('IFNULL(sg.name, gb.PartyName) AS CompanyName'),
            DB::raw('IFNULL(gb.GuestName, "") AS GuestName'),
            'gb.CancelDate', 'gb.CancelUName'
         )
         ->where('gb.Property_ID', $propertyid)
         ->where('gb.Cancel', 'Y')
         ->whereDate('gb.CancelDate', $fordate)
         ->orderBy('gb.CancelDate')
         ->get();

      // â”€â”€ 5. NO-SHOWS TODAY â”€â”€
      $noshow = DB::table('grpbookingdetails AS gb')
         ->leftJoin('subgroup AS sg', 'gb.PartyCode', '=', 'sg.sub_code')
         ->select(
            'gb.DocId', 'gb.RoomNo', 'gb.ArrDate', 'gb.DepDate',
            DB::raw('IFNULL(sg.name, gb.PartyName) AS CompanyName'),
            DB::raw('IFNULL(gb.GuestName, "") AS GuestName')
         )
         ->where('gb.Property_ID', $propertyid)
         ->where('gb.Cancel', 'Y')
         ->where('gb.CancelUName', 'NOSHOW')
         ->whereDate('gb.CancelDate', $fordate)
         ->orderBy('gb.RoomNo')
         ->get();

      return response()->json([
         'arrivals'      => $arrivals,
         'inhouse'       => $inhouse,
         'departures'    => $departures,
         'cancellations' => $cancellations,
         'noshow'        => $noshow,
         'summary' => [
            'arrivals'      => $arrivals->count(),
            'inhouse'       => $inhouse->count(),
            'departures'    => $departures->count(),
            'cancellations' => $cancellations->count(),
            'noshow'        => $noshow->count(),
         ],
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Room Rent Audit Report
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function roomrentaudit(Request $request)
   {
      $permission = revokeopen(191212);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $company = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.roomrentaudit', [
         'fordate' => $this->ncurdate,
         'company' => $company,
      ]);
   }

   public function roomrentauditfetch(Request $request)
   {
      $propertyid = $this->propertyid;
      $fromdate   = $request->input('fromdate');
      $todate     = $request->input('todate');
      if (!$fromdate || !$todate) return response()->json(['message' => 'From and To dates required.'], 422);

      // Get all rooms that were active during the period
      $rooms = DB::table('roomocc AS ro')
         ->leftJoin('room_cat AS rc', 'ro.roomcat', '=', 'rc.cat_code')
         ->leftJoin('guestprof AS gp', 'ro.guestprof', '=', 'gp.guestcode')
         ->select(
            'ro.docid', 'ro.roomno', 'ro.sno1',
            'ro.name AS GuestName',
            'ro.roomrate',
            'ro.chkindate',
            'ro.depdate',
            DB::raw('IFNULL(ro.chkoutdate, "") AS chkoutdate'),
            DB::raw('IFNULL(rc.name, "") AS RoomType')
         )
         ->where('ro.propertyid', $propertyid)
         ->whereNull('ro.type')
         ->where('ro.chkindate', '<=', $todate)
         ->where(function ($q) use ($fromdate) {
            $q->whereNull('ro.chkoutdate')->orWhere('ro.chkoutdate', '>=', $fromdate);
         })
         ->orderBy('ro.roomno')
         ->get();

      $result = [];
      foreach ($rooms as $room) {
         // Compute nights in the selected period
         $arrive     = max($room->chkindate, $fromdate);
         $depart     = min(
            $room->chkoutdate ?: $room->depdate ?: $todate,
            $todate
         );
         $nights     = max(1, (int) (strtotime($depart) - strtotime($arrive)) / 86400 + 1);
         $expected   = $room->roomrate * $nights;

         // Sum actual RC charges posted for this folio in the period
         $actualRC = DB::table('paycharge')
            ->selectRaw('SUM(amtdr-amtcr) AS total')
            ->where('propertyid', $propertyid)
            ->where('folionodocid', $room->docid)
            ->where('sno1', $room->sno1)
            ->whereIn('vtype', ['RC', 'REV'])
            ->whereBetween('vdate', [$fromdate, $todate])
            ->value('total') ?? 0;

         $variance = (float) $actualRC - $expected;
         $flag = abs($variance) > 0.01 ? 'âš ï¸' : 'âœ…';

         $result[] = [
            'RoomNo'     => $room->roomno,
            'GuestName'  => $room->GuestName,
            'RoomType'   => $room->RoomType,
            'Rate'       => (float) $room->roomrate,
            'Nights'     => $nights,
            'Expected'   => $expected,
            'ActualRC'   => (float) $actualRC,
            'Variance'   => $variance,
            'Flag'       => $flag,
            'CheckIn'    => $room->chkindate,
            'Departure'  => $room->depdate,
            'CheckOut'   => $room->chkoutdate,
         ];
      }

      $totalExpected = array_sum(array_column($result, 'Expected'));
      $totalActual   = array_sum(array_column($result, 'ActualRC'));
      $flagged       = count(array_filter($result, fn($r) => $r['Flag'] === 'âš ï¸'));

      return response()->json([
         'data' => $result,
         'summary' => [
            'totalExpected' => $totalExpected,
            'totalActual'   => $totalActual,
            'variance'      => $totalActual - $totalExpected,
            'flagged'       => $flagged,
            'totalRooms'    => count($result),
         ],
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Movement List â€” daily booking movements (arrivals/departures/transfers)
   // Legacy: GRepFormName = "MovementList"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function movementlist(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();

      return view('property.movementlist', [
         'comp' => $comp,
         'fromdate' => $this->ncurdate,
      ]);
   }

   public function movementlistfetch(Request $request)
   {
      $data = $this->getMovementListData($request);

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
         'totalPax' => $data->sum('Pax') + $data->sum('Child'),
         'totalRooms' => $data->sum('RoomDet'),
         'totalAdvance' => $data->sum('advance'),
      ]);
   }

   public function printmovementlist(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $data = $this->getMovementListData($request);
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();

      return view('property.printmovementlist', [
         'data' => $data,
         'comp' => $comp,
         'fromdate' => $fromdate,
         'todate' => $todate,
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Discount Register â€” POS discount audit trail
   // Legacy: GRepFormName = "DiscountReg"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function discountregister(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      $outlets = DB::table('depart')->where('propertyid', $this->propertyid)->select('dcode', 'name')->get();

      return view('property.discountregister', [
         'comp' => $comp,
         'fromdate' => $this->ncurdate,
         'outlets' => $outlets,
      ]);
   }

   public function discountregisterfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $restcode = $request->input('restcode', 'all');

      $report = DB::table('stock AS S')
         ->leftJoin('depart AS D', 'S.RestCode', '=', 'D.dcode')
         ->leftJoin('itemmast AS I', function ($join) {
            $join->on('S.Item', '=', 'I.Code')
               ->on('S.RestCode', '=', 'I.RestCode');
         })
         ->where('S.DelFlag', '<>', 'D')
         ->where('S.propertyid', $this->propertyid)
         ->where('S.DiscPer', '>', 0)
         ->whereBetween('S.VDate', [$fromdate, $todate])
         ->select([
            'S.VDate', 'S.VType', 'S.VNo', 'S.QtyIss AS Quantity',
            'S.Rate', 'S.Amount', 'S.DiscPer', 'S.DiscAmt',
            'S.RestCode', 'I.Name AS ItemName', 'D.Name AS DeptName',
         ])
         ->orderBy('D.Name')
         ->orderBy('S.VDate');

      if ($restcode !== 'all') {
         $report->where('S.RestCode', $restcode);
      }

      $data = $report->get();

      // Group by outlet for summary
      $grouped = $data->groupBy('DeptName')->map(function ($items, $dept) {
         return [
            'dept' => $dept,
            'items' => $items,
            'totalAmount' => $items->sum('Amount'),
            'totalDiscount' => $items->sum('DiscAmt'),
         ];
      })->values();

      return response()->json([
         'data' => $data,
         'grouped' => $grouped,
         'total' => $data->count(),
         'totalAmount' => $data->sum('Amount'),
         'totalDiscount' => $data->sum('DiscAmt'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Food Cost Report â€” F&B cost analysis (opening + purchases - closing)
   // Legacy: GRepFormName = "FoodCost"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function foodcost(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();

      return view('property.foodcost', [
         'comp' => $comp,
         'fromdate' => $this->ncurdate,
      ]);
   }

   public function foodcostfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Column names: stock.propertyid, stock.departcode, stock.departcode, depart.dcode, depart.rest_type
      // 1. Opening Stock (Raw Material + Semi-Finish) â€” purchases before fromdate
      $openingStock = DB::table('stock AS S')
         ->join('itemmast AS I', 'S.Item', '=', 'I.Code')
         ->where('S.propertyid', $pid)
         ->where('I.ItemType', 'Store')
         ->whereIn('I.Type', ['Semi Finish', 'Raw Material'])
         ->where('S.departcode', $pid . 'PURC')
         ->where(function ($q) use ($fromdate) {
            $q->where('S.VDate', '<', $fromdate)
               ->orWhere(function ($q2) use ($fromdate) {
                  $q2->where('S.VType', 'STOP')->where('S.VDate', '=', $fromdate);
               });
         })
         ->selectRaw('SUM(CASE WHEN QtyRec > 0 THEN Amount ELSE -Amount END) AS Amt')
         ->value('Amt') ?? 0;

      // 2. Purchases during period
      $purchases = DB::table('stock AS S')
         ->join('itemmast AS I', 'S.Item', '=', 'I.Code')
         ->where('S.propertyid', $pid)
         ->where('I.ItemType', 'Store')
         ->whereIn('I.Type', ['Semi Finish', 'Raw Material'])
         ->where('S.departcode', $pid . 'PURC')
         ->whereBetween('S.VDate', [$fromdate, $todate])
         ->whereIn('S.VType', ['MRCH', 'MRCR', 'PBPB', 'PBPC'])
         ->selectRaw('SUM(CASE WHEN QtyRec > 0 THEN Amount ELSE -Amount END) AS Amt')
         ->value('Amt') ?? 0;

      // 3. Closing Stock â€” total store stock up to todate
      $closingStock = DB::table('stock AS S')
         ->join('itemmast AS I', 'S.Item', '=', 'I.Code')
         ->where('S.propertyid', $pid)
         ->where('I.ItemType', 'Store')
         ->whereIn('I.Type', ['Semi Finish', 'Raw Material'])
         ->where('S.departcode', $pid . 'PURC')
         ->where('S.VDate', '<=', $todate)
         ->selectRaw('SUM(CASE WHEN QtyRec > 0 THEN Amount ELSE -Amount END) AS Amt')
         ->value('Amt') ?? 0;

      $netStock = $openingStock + $purchases - $closingStock;

      // 4. Staff Kitchen Issue
      $staffKitchenIssue = DB::table('stock AS S')
         ->where('S.propertyid', $pid)
         ->whereBetween('S.VDate', [$fromdate, $todate])
         ->whereIn('S.departcode', function ($q) use ($pid) {
            $q->select('dcode')->from('depart')->where('rest_type', 'Staff Kitchen');
         })
         ->selectRaw('SUM(CASE WHEN QtyRec > 0 THEN Amount ELSE -Amount END) AS Amt')
         ->value('Amt') ?? 0;

      // 5. Kitchen Consumption (issues to kitchen outlets)
      $kitchenConsumption = DB::table('stock AS S')
         ->where('S.propertyid', $pid)
         ->whereBetween('S.VDate', [$fromdate, $todate])
         ->whereIn('S.departcode', function ($q) use ($pid) {
            $q->select('dcode')->from('depart')->where('rest_type', 'Kitchen');
         })
         ->selectRaw('SUM(CASE WHEN QtyRec > 0 THEN Amount ELSE -Amount END) AS Amt')
         ->value('Amt') ?? 0;

      // 6. NC KOT deduction
      $ncKotDeduction = DB::table('kot')
         ->join('nctype_mast', 'kot.NCType', '=', 'nctype_mast.nctype')
         ->where('kot.propertyid', $pid)
         ->whereBetween('kot.VDate', [$fromdate, $todate])
         ->selectRaw('SUM(Amount * ncper) / 100 AS Amt')
         ->value('Amt') ?? 0;

      // 7. Food Sales â€” POS outlets
      $foodSalesPOS = DB::table('stock AS S')
         ->join('itemmast AS I', 'S.Item', '=', 'I.Code')
         ->join('depart AS D', 'S.RestCode', '=', 'D.dcode')
         ->join('itemcatmast AS IC', 'I.ItemCatCode', '=', 'IC.Code')
         ->join('voucher_type AS VT', 'S.VType', '=', 'VT.V_Type')
         ->where('S.propertyid', $pid)
         ->whereBetween('S.VDate', [$fromdate, $todate])
         ->whereIn('IC.CatType', ['Food', 'Beverage', 'Confectionary', 'Liquor', 'Tobacco'])
         ->selectRaw('SUM(S.Amount) AS Amt, MAX(D.Name) AS RestName')
         ->groupBy('S.RestCode')
         ->get();

      $totalFoodSalesPOS = $foodSalesPOS->sum('Amt');

      // 8. Food Sales â€” Banquet
      $foodSalesBanquet = DB::table('hallstock AS HS')
         ->join('itemmast AS I', 'HS.Item', '=', 'I.Code')
         ->join('itemcatmast AS IC', 'I.ItemCatCode', '=', 'IC.Code')
         ->where('HS.propertyid', $pid)
         ->whereBetween('HS.VDate', [$fromdate, $todate])
         ->whereIn('IC.CatType', ['Food', 'Beverage', 'Confectionary', 'Liquor', 'Tobacco'])
         ->selectRaw('SUM(HS.Amount) AS Amt')
         ->value('Amt') ?? 0;

      $totalFoodSales = $totalFoodSalesPOS + $foodSalesBanquet;
      $netConsumption = $netStock - $staffKitchenIssue;
      $foodCostPct = $totalFoodSales > 0 ? round(($netConsumption / $totalFoodSales) * 100, 2) : 0;

      return response()->json([
         'openingStock' => $openingStock,
         'purchases' => $purchases,
         'closingStock' => $closingStock,
         'netStock' => $netStock,
         'staffKitchenIssue' => $staffKitchenIssue,
         'kitchenConsumption' => $kitchenConsumption,
         'ncKotDeduction' => $ncKotDeduction,
         'foodSalesPOS' => $totalFoodSalesPOS,
         'foodSalesBanquet' => $foodSalesBanquet,
         'totalFoodSales' => $totalFoodSales,
         'netConsumption' => $netConsumption,
         'foodCostPct' => $foodCostPct,
         'posBreakdown' => $foodSalesPOS,
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Cover Analysis â€” pax/covers per outlet per day
   // Legacy: GRepFormName = "CoverAnalysis"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function coveranalysis(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.coveranalysis', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function coveranalysisfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Bill-level summary: total net amount + covers per bill per outlet
      // Column names: sale1.propertyid, sale1.restcode (lowercase), depart.dcode
      $bills = DB::table('sale1 AS S1')
         ->join('stock AS S', 'S1.DocId', '=', 'S.DocId')
         ->join('depart AS D', 'S1.RestCode', '=', 'D.dcode')
         ->join('itemmast AS I', function ($join) {
            $join->on('S.Item', '=', 'I.Code')->on('S.RestCode', '=', 'I.RestCode');
         })
         ->join('itemcatmast AS IC', 'I.ItemCatCode', '=', 'IC.Code')
         ->where('S1.propertyid', $pid)
         ->whereBetween('S1.VDate', [$fromdate, $todate])
         ->whereIn('IC.CatType', ['Food', 'Liquor', 'Confectionary', 'Beverage', 'Miscellaneous', 'Tobacco'])
         ->where('S.DelFlag', '<>', 'D')
         ->select([
            'S1.VDate', 'S1.RestCode', 'D.Name AS DeptName', 'S1.VNo',
            DB::raw('SUM(S.Amount - S.DiscAmt) AS NetAmt'),
            DB::raw('MAX(S1.GuarAtt) AS Covers'),
         ])
         ->groupBy('S1.VDate', 'S1.RestCode', 'D.Name', 'S1.VNo')
         ->get();

      // Category-wise breakdown
      $categoryWise = DB::table('sale1 AS S1')
         ->join('stock AS S', 'S1.DocId', '=', 'S.DocId')
         ->join('depart AS D', 'S1.RestCode', '=', 'D.dcode')
         ->join('itemmast AS I', function ($join) {
            $join->on('S.Item', '=', 'I.Code')->on('S.RestCode', '=', 'I.RestCode');
         })
         ->join('itemcatmast AS IC', 'I.ItemCatCode', '=', 'IC.Code')
         ->where('S1.propertyid', $pid)
         ->whereBetween('S1.VDate', [$fromdate, $todate])
         ->whereIn('IC.CatType', ['Food', 'Liquor', 'Confectionary', 'Beverage', 'Miscellaneous', 'Tobacco'])
         ->where('S.DelFlag', '<>', 'D')
         ->select([
            'D.Name AS DeptName', 'IC.CatType',
            DB::raw('SUM(S.Amount - S.DiscAmt) AS NetAmt'),
            DB::raw('COUNT(DISTINCT S1.VNo) AS BillCount'),
         ])
         ->groupBy('D.Name', 'IC.CatType')
         ->orderBy('D.Name')
         ->orderBy('IC.CatType')
         ->get();

      // Daily summary
      $daily = $bills->groupBy('VDate')->map(function ($rows, $date) {
         return [
            'date' => $date,
            'bills' => $rows->count(),
            'covers' => $rows->sum('Covers'),
            'netAmt' => $rows->sum('NetAmt'),
            'avgPerCover' => $rows->sum('Covers') > 0 ? round($rows->sum('NetAmt') / $rows->sum('Covers'), 2) : 0,
         ];
      })->values();

      return response()->json([
         'bills' => $bills,
         'categoryWise' => $categoryWise,
         'daily' => $daily,
         'totalBills' => $bills->count(),
         'totalCovers' => $bills->sum('Covers'),
         'totalNetAmt' => $bills->sum('NetAmt'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Waiter-Wise Sale â€” sales by steward/waiter
   // Legacy: GRepFormName = "WaiterWiseSale"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function waitersale(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.waitersale', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function waitersalefetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Waiter code is stored directly in kot.Waiter (no separate waiter master table)
      // Link: stock.kotdocid = kot.docid, stock.kotsno = kot.sno
      $data = DB::table('stock AS S')
         ->leftJoin('sale1 AS S1', 'S.DocId', '=', 'S1.DocId')
         ->leftJoin('depart AS D', 'S1.RestCode', '=', 'D.dcode')
         ->leftJoin('kot AS K', function ($join) {
            $join->on('S.kotdocid', '=', 'K.DocId')
               ->on('S.kotsno', '=', 'K.Sno');
         })
         ->leftJoin('paycharge AS PC', function ($join) {
            $join->on('S1.DocId', '=', 'PC.DocId')
               ->where('PC.RoomCat', '=', 'REST');
         })
         ->where('S.propertyid', $pid)
         ->whereBetween('S.VDate', [$fromdate, $todate])
         ->whereRaw("COALESCE(K.Waiter, '') <> ''")
         ->select([
            'K.Waiter AS WaiterCode',
            DB::raw('K.Waiter AS WaiterName'),
            'S1.RestCode',
            DB::raw('MAX(D.Name) AS DeptName'),
            DB::raw('COUNT(DISTINCT S.KOTDocId) AS KOTCount'),
            DB::raw('SUM(S.Amount - S.DiscAmt) AS NetSale'),
            DB::raw('SUM(S.TaxAmt) AS TaxAmt'),
            DB::raw('MAX(PC.TipAmt) AS TipAmt'),
         ])
         ->groupBy('K.Waiter', 'S1.RestCode')
         ->orderBy('D.Name')
         ->orderBy('K.Waiter')
         ->get();

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
         'totalSale' => $data->sum('NetSale'),
         'totalTax' => $data->sum('TaxAmt'),
         'totalTips' => $data->sum('TipAmt'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Cashier Settlement â€” cashier collection/closing
   // Legacy: GRepFormName = "CashierSettlement"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function cashiersettlement(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.cashiersettlement', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function cashiersettlementfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Settlement by mode (CASH/CARD/UPI/ROOM/COMPANY etc.)
      // Column: paycharge.propertyid, paycharge.restcode
      $settlements = DB::table('paycharge AS PC')
         ->leftJoin('depart AS D', 'PC.RestCode', '=', 'D.dcode')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.VDate', [$fromdate, $todate])
         ->where('PC.VType', 'TOUT')
         ->select([
            'PC.VDate',
            DB::raw('MAX(D.Name) AS DeptName'),
            'PC.PayType',
            DB::raw('SUM(PC.AmtDr) AS Amount'),
         ])
         ->groupBy('PC.VDate', 'PC.PayType')
         ->orderBy('PC.VDate')
         ->orderBy('PC.PayType')
         ->get();

      // Mode-wise summary
      $modeWise = $settlements->groupBy('PayType')->map(function ($rows, $mode) {
         return ['mode' => $mode, 'amount' => $rows->sum('Amount'), 'count' => $rows->count()];
      })->values();

      // Daily summary
      $daily = $settlements->groupBy('VDate')->map(function ($rows, $date) {
         return ['date' => $date, 'total' => $rows->sum('Amount'), 'count' => $rows->count()];
      })->values();

      return response()->json([
         'data' => $settlements,
         'modeWise' => $modeWise,
         'daily' => $daily,
         'total' => $settlements->sum('Amount'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Guest Payments â€” payment summary by guest/folio
   // Legacy: GRepFormName = "GuestPayments"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function guestpayments(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.guestpayments', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function guestpaymentsfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Column: paycharge.propertyid, guestfolio.guestprof -> guestprof.guestcode
      $data = DB::table('paycharge AS PC')
         ->leftJoin('guestfolio AS GF', 'PC.FolioNoDocid', '=', 'GF.DocId')
         ->leftJoin('guestprof AS GP', 'GF.GuestProf', '=', 'GP.guestcode')
         ->leftJoin('roomocc AS RO', 'PC.FolioNoDocid', '=', 'RO.DocId')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.VDate', [$fromdate, $todate])
         ->where('PC.VType', 'REC')
         ->select([
            'PC.VDate', 'PC.VType', 'PC.VNo', 'PC.DocId',
            'GP.Name AS GuestName', 'RO.RoomNo', 'GF.folio_no AS FolioNo',
            'PC.PayType', 'PC.AmtDr AS Amount', 'PC.Remarks',
         ])
         ->orderBy('PC.VDate')
         ->orderBy('GP.Name')
         ->get();

      // Mode-wise summary
      $modeWise = $data->groupBy('PayType')->map(function ($rows, $mode) {
         return ['mode' => $mode, 'amount' => $rows->sum('Amount'), 'count' => $rows->count()];
      })->values();

      return response()->json([
         'data' => $data,
         'modeWise' => $modeWise,
         'total' => $data->sum('Amount'),
         'count' => $data->count(),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Room Change History â€” audit trail of room changes
   // Legacy: GRepFormName = "RoomChangeHistory"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function roomchangehistory(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.roomchangehistory', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function roomchangehistoryfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Room changes are tracked in roomocc table (Type='C' = room change record)
      // guestprof.docid = roomocc.docid (existing pattern from arrivallist)
      $data = DB::table('roomocc AS RO')
         ->leftJoin('guestprof AS GP', 'GP.docid', '=', 'RO.docid')
         ->leftJoin('room_cat AS RC', 'RC.cat_code', '=', 'RO.RoomCat')
         ->where('RO.propertyid', $pid)
         ->whereBetween('RO.ChngDate', [$fromdate, $todate])
         ->where('RO.Type', 'C')
         ->select([
            'RO.ChngDate',
            DB::raw("TIME(RO.u_entdt) AS ChngTime"),
            'GP.name AS GuestName',
            'RO.RoomNo AS NewRoom',
            'RO.NewRoomNo AS OldRoom',
            'RC.name AS RoomType',
            'RO.RoomRate',
            'RO.ChkInDate', 'RO.ChkOutDate',
            'RO.ReasonRChange AS Reason',
            'RO.U_Name AS ChangedBy',
         ])
         ->orderBy('RO.ChngDate', 'DESC')
         ->get();

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
      ]);
   }

   private function getMovementListData(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $reststatus = $request->input('reststatus', 'all');
      $sortby = $request->input('sortby', 'arrdate');
      $pendingyn = $request->input('pendingyn', 'all');
      $roomcat = $request->input('roomcat', 'all');

      $report = GrpBookinDetail::leftJoin('booking', 'booking.DocId', '=', 'grpbookingdetails.BookingDocid')
         ->leftJoin('guestprof', 'guestprof.docid', '=', 'grpbookingdetails.BookingDocid')
         ->leftJoin('plan_mast', 'grpbookingdetails.Plan_Code', '=', 'plan_mast.pcode')
         ->leftJoin('room_cat', 'room_cat.cat_code', '=', 'grpbookingdetails.RoomCat')
         ->leftJoin('subgroup AS S', 'booking.Company', '=', 'S.sub_code')
         ->leftJoin('subgroup AS T', 'booking.TravelAgency', '=', 'T.sub_code')
         ->leftJoin('paycharge', function ($join) {
            $join->on('paycharge.refdocid', '=', 'grpbookingdetails.BookingDocid')
               ->where('paycharge.sno', '1');
         })
         ->select([
            'booking.Vtype', 'booking.DocId', 'grpbookingdetails.Sno',
            'booking.BookNo AS ResNo', 'grpbookingdetails.GuestName AS GuestName',
            'booking.MobNo AS MobileNo',
            DB::raw("TRIM(CONCAT(COALESCE(S.name, ''), CASE WHEN COALESCE(T.name, '') <> '' THEN '/' ELSE '' END, COALESCE(T.name, ''))) AS Company"),
            'grpbookingdetails.RoomDet', 'grpbookingdetails.ArrDate', 'grpbookingdetails.ArrTime',
            'grpbookingdetails.Adults AS Pax', 'grpbookingdetails.Childs AS Child',
            'grpbookingdetails.DepDate', 'grpbookingdetails.DepTime',
            'plan_mast.name AS PlanName', 'grpbookingdetails.RoomNo',
            'room_cat.name AS RoomType', 'booking.ArrFrom AS ArrDetail',
            'booking.BookedBy', 'booking.ResStatus', 'booking.Remarks',
            DB::raw('COALESCE(SUM(paycharge.amtcr) - SUM(paycharge.amtdr), 0) AS advance'),
         ])
         ->where('grpbookingdetails.Cancel', 'N')
         ->where('grpbookingdetails.Property_ID', $this->propertyid)
         ->whereBetween('grpbookingdetails.ArrDate', [$fromdate, $todate])
         ->groupBy(
            'booking.DocId', 'grpbookingdetails.BookingDocid', 'grpbookingdetails.Sno',
            'booking.BookNo', 'grpbookingdetails.GuestName', 'booking.MobNo',
            'S.name', 'T.name', 'grpbookingdetails.RoomDet',
            'grpbookingdetails.ArrDate', 'grpbookingdetails.ArrTime',
            'grpbookingdetails.Adults', 'grpbookingdetails.Childs',
            'grpbookingdetails.DepDate', 'grpbookingdetails.DepTime',
            'plan_mast.name', 'grpbookingdetails.RoomNo', 'room_cat.name',
            'booking.ArrFrom', 'booking.BookedBy', 'booking.ResStatus', 'booking.Remarks', 'booking.Vtype'
         );

      if ($pendingyn === 'pending') {
         $report->whereRaw("NOT EXISTS (SELECT 1 FROM guestfolio WHERE guestfolio.BookingDocId = grpbookingdetails.BookingDocid AND guestfolio.BookingSno = grpbookingdetails.Sno AND COALESCE(guestfolio.BookingDocId, '') <> '')");
      }
      if ($reststatus === 'confirm') {
         $report->where(function ($q) {
            $q->where('booking.ResStatus', 'Confirm')
              ->orWhere('booking.ResStatus', '')
              ->orWhereNull('booking.ResStatus');
         });
      } elseif ($reststatus === 'tentative') {
         $report->where('booking.ResStatus', 'Tentative');
      } elseif ($reststatus === 'waiting') {
         $report->where('booking.ResStatus', 'Waiting');
      }
      if ($roomcat !== 'all') {
         $report->where('grpbookingdetails.RoomCat', $roomcat);
      }
      switch ($sortby) {
         case 'guest':
            $report->orderBy('grpbookingdetails.GuestName')->orderBy('grpbookingdetails.ArrDate');
            break;
         case 'company':
            $report->orderBy('S.name')->orderBy('grpbookingdetails.ArrDate');
            break;
         case 'travelagent':
            $report->orderBy('T.name')->orderBy('grpbookingdetails.ArrDate');
            break;
         case 'resstatus':
            $report->orderBy('booking.ResStatus')->orderBy('grpbookingdetails.ArrDate')->orderBy('grpbookingdetails.GuestName');
            break;
         default:
            $report->orderBy('grpbookingdetails.ArrDate')->orderBy('grpbookingdetails.GuestName');
            break;
      }

      return $report->get();
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Guest Trial Balance â€” charges vs payments per guest/folio
   // Legacy: GRepFormName = "GuestTrialBalance"
   // Filters: All / In House / Checked In / Checked Out
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function guesttrialbalance(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.guesttrialbalance', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function guesttrialbalancefetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $filter = $request->input('filter', 'all');
      $pid = $this->propertyid;

      // Guest Trial Balance: sum charges (AmtDr) and payments (AmtCr) per guest/folio
      // then compute balance = charges - payments
      $data = DB::table('paycharge AS PC')
         ->leftJoin('guestfolio AS GF', 'PC.folionodocid', '=', 'GF.docid')
         ->leftJoin('guestprof AS GP', 'GF.guestprof', '=', 'GP.guestcode')
         ->leftJoin('roomocc AS RO', 'PC.folionodocid', '=', 'RO.docid')
         ->leftJoin('room_mast AS RM', 'RO.roomno', '=', 'RM.rcode')
         ->leftJoin('room_cat AS RC', 'RO.roomcat', '=', 'RC.cat_code')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.vdate', [$fromdate, $todate])
         ->select([
            'PC.folionodocid',
            DB::raw('MAX(GP.name) AS GuestName'),
            DB::raw('MAX(RO.roomno) AS RoomNo'),
            DB::raw('MAX(RC.name) AS RoomType'),
            DB::raw('MAX(GF.vdate) AS CheckInDate'),
            DB::raw('MAX(RO.depdate) AS DepartDate'),
            DB::raw('SUM(PC.amtdr) AS TotalCharges'),
            DB::raw('SUM(PC.amtcr) AS TotalPayments'),
            DB::raw('SUM(PC.amtdr) - SUM(PC.amtcr) AS Balance'),
         ])
         ->groupBy('PC.folionodocid')
         ->havingRaw('ABS(SUM(PC.amtdr) - SUM(PC.amtcr)) > 0.01');

      // Filter by status
      if ($filter === 'inhouse') {
         $data->where(function ($q) {
            $q->whereNull('RO.chkoutdate')
              ->orWhere('RO.chkoutdate', '=', '0000-00-00')
              ->orWhere('RO.chkoutdate', '=', '');
         });
      } elseif ($filter === 'checkedin') {
         $data->whereNotNull('RO.chkindate')
            ->where('RO.chkindate', '<>', '');
      } elseif ($filter === 'checkedout') {
         $data->whereNotNull('RO.chkoutdate')
            ->where('RO.chkoutdate', '<>', '')
            ->where('RO.chkoutdate', '<>', '0000-00-00');
      }

      $results = $data->orderBy('GP.name')
         ->get();

      return response()->json([
         'data' => $results,
         'total' => $results->count(),
         'totalCharges' => $results->sum('TotalCharges'),
         'totalPayments' => $results->sum('TotalPayments'),
         'totalBalance' => $results->sum('Balance'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Room Nights Analysis â€” room nights consumed per room type
   // Legacy: GRepFormName = "RoomNights"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function roomnights(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.roomnights', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function roomnightsfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Room Nights: for each room type, count room nights consumed
      // A room night = one room occupied for one night
      $roomTypes = DB::table('room_cat')
         ->where('propertyid', $pid)
         ->get();

      $totalRooms = $roomTypes->sum('norooms');

      // RoomOcc with ChkInDate and ChkOutDate
      $occupancy = DB::table('roomocc AS RO')
         ->leftJoin('room_cat AS RC', 'RO.RoomCat', '=', 'RC.cat_code')
         ->where('RO.propertyid', $pid)
         ->where(function ($q) use ($fromdate, $todate) {
            $q->where(function ($q2) use ($fromdate, $todate) {
               // Rooms checked in before period end and not checked out (or checked out during/after period)
               $q2->where('RO.ChkInDate', '<=', $todate)
                  ->where(function ($q3) use ($fromdate) {
                     $q3->whereNull('RO.ChkOutDate')
                        ->orWhere('RO.ChkOutDate', '=', '0000-00-00')
                        ->orWhere('RO.ChkOutDate', '>=', $fromdate);
                  });
            });
         })
         ->select([
            'RC.cat_code',
            'RC.name AS RoomTypeName',
            'RC.norooms',
            DB::raw('COUNT(*) AS OccupiedRooms'),
            DB::raw('SUM(CASE WHEN RO.ChkInDate < ' . $fromdate . ' THEN DATEDIFF(LEAST(IFNULL(RO.ChkOutDate, ' . $todate . '), ' . $todate . '), ' . $fromdate . ') ELSE DATEDIFF(LEAST(IFNULL(RO.ChkOutDate, ' . $todate . '), ' . $todate . '), RO.ChkInDate) END) AS RoomNights')
         ])
         ->groupBy('RC.cat_code', 'RC.name', 'RC.norooms')
         ->orderBy('RC.name')
         ->get();

      $totalNights = $occupancy->sum('RoomNights');
      $periodDays = max(1, (strtotime($todate) - strtotime($fromdate)) / 86400);

      return response()->json([
         'data' => $occupancy,
         'totalRooms' => $totalRooms,
         'totalNights' => $totalNights,
         'periodDays' => $periodDays,
         'occupancyPct' => $totalRooms > 0 ? round(($totalNights / ($totalRooms * $periodDays)) * 100, 2) : 0,
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Check-Out Register â€” daily checkout list with bill details
   // Legacy: GRepFormName = "ChkOutRegister"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function checkoutregister(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.checkoutregister', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function checkoutregisterfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      $data = DB::table('roomocc AS RO')
         ->leftJoin('guestprof AS GP', 'GP.docid', '=', 'RO.docid')
         ->leftJoin('room_cat AS RC', 'RO.roomcat', '=', 'RC.cat_code')
         ->select([
            'RO.docid AS FolioDocId',
            'RO.foliono AS FolioNo',
            'GP.name AS GuestName',
            'GP.mobile_no AS Mobile',
            'RO.roomno AS RoomNo',
            'RC.name AS RoomType',
            'RO.roomrate AS RoomRate',
            'RO.chkindate AS ChkInDate',
            'RO.chkoutdate AS ChkOutDate',
            'RO.nodays AS Nights',
            'RO.u_name AS CheckOutBy',
            'RO.u_updatedt AS CheckOutTime',
         ])
         ->where('RO.propertyid', $pid)
         ->whereBetween('RO.chkoutdate', [$fromdate, $todate])
         ->whereNotNull('RO.chkoutdate')
         ->where('RO.chkoutdate', '<>', '')
         ->where('RO.chkoutdate', '<>', '0000-00-00')
         ->orderBy('RO.chkoutdate')
         ->orderBy('RO.roomno')
         ->get();

      $payments = DB::table('paycharge AS PC')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.settledate', [$fromdate, $todate])
         ->where('PC.modeset', 'S')
         ->select([
            'PC.folionodocid',
            'PC.paytype AS PayType',
            DB::raw('SUM(PC.amtcr) AS Amount'),
         ])
         ->groupBy('PC.folionodocid', 'PC.paytype')
         ->get()
         ->groupBy('folionodocid');

      $enriched = $data->map(function ($row) use ($payments) {
         $row->payments = $payments->get($row->FolioDocId, collect());
         $row->totalPaid = $row->payments->sum('Amount');
         return $row;
      });

      return response()->json([
         'data' => $enriched,
         'total' => $enriched->count(),
         'totalNights' => $enriched->sum('Nights'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Registered Guest Detail â€” guest master listing with visit history
   // Legacy: GRepFormName = "RegisteredGuestDetail"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function registeredguestdetail(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.registeredguestdetail', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function registeredguestdetailfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;
      $search = $request->input('search', '');

      // Guest master with visit count, last visit, total spend
      // Use subqueries for aggregations to avoid groupBy row explosion
      $guestVisits = DB::table('guestfolio')
         ->where('propertyid', $pid)
         ->select([
            'guestprof',
            DB::raw('COUNT(*) AS Visits'),
            DB::raw('MAX(vdate) AS LastVisit'),
         ])
         ->groupBy('guestprof')
         ->get()
         ->keyBy('guestprof');

      $guestSpend = DB::table('paycharge AS PC')
         ->join('guestfolio AS GF', 'PC.folionodocid', '=', 'GF.docid')
         ->where('PC.vtype', 'RC')
         ->where('GF.propertyid', $pid)
         ->select([
            'GF.guestprof',
            DB::raw('SUM(PC.amtdr) AS TotalSpend'),
         ])
         ->groupBy('GF.guestprof')
         ->get()
         ->keyBy('guestprof');

      $query = DB::table('guestprof AS GP')
         ->where('GP.propertyid', $pid)
         ->select([
            'GP.guestcode',
            'GP.name',
            'GP.add1',
            'GP.add2',
            'GP.city',
            'GP.mobile_no',
            'GP.email_id',
            'GP.nationality',
            'GP.type AS GuestType',
            'GP.gender',
            'GP.panno',
            'GP.guest_status',
            'GP.spl_instr',
         ]);

      if (!empty($search)) {
         $query->where(function ($q) use ($search) {
            $q->where('GP.name', 'LIKE', "%$search%")
              ->orWhere('GP.mobile_no', 'LIKE', "%$search%")
              ->orWhere('GP.email_id', 'LIKE', "%$search%")
              ->orWhere('GP.city', 'LIKE', "%$search%")
              ->orWhere('GP.panno', 'LIKE', "%$search%");
         });
      }

      $data = $query->orderBy('GP.name')
         ->get()
         ->map(function ($g) use ($guestVisits, $guestSpend) {
            $g->Visits = $guestVisits->get($g->guestcode)->Visits ?? 0;
            $g->LastVisit = $guestVisits->get($g->guestcode)->LastVisit ?? null;
            $g->TotalSpend = $guestSpend->get($g->guestcode)->TotalSpend ?? 0;
            return $g;
         });

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
         'totalSpend' => $data->sum('TotalSpend'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Edited Bills â€” audit trail of modified FOM bills
   // Legacy: GRepFormName = "EditedBills"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function editedbills(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.editedbills', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function editedbillsfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Edited bills: fombilldetails with u_ae = 'e' (edit)
      $data = DB::table('fombilldetails')
         ->where('propertyid', $pid)
         ->where('u_ae', 'e')
         ->whereBetween('billdate', [$fromdate, $todate])
         ->select([
            'billno',
            'billdate',
            'foliono',
            'guestname',
            'billamt',
            'settamt',
            'status',
            'u_name',
            'u_entdt',
            'u_updatedt',
         ])
         ->orderBy('billdate', 'DESC')
         ->orderBy('billno')
         ->get();

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
         'totalBillAmt' => $data->sum('billamt'),
         'totalSettAmt' => $data->sum('settamt'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // KOT Edit/Delete Log â€” audit trail of KOT modifications
   // Legacy: GRepFormName = "KOTEditDelete"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function koteditdeletelog(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      $outlets = DB::table('depart')->where('propertyid', $this->propertyid)->select('dcode', 'name')->get();

      return view('property.koteditdeletelog', [
         'comp' => $comp,
         'fromdate' => $this->ncurdate,
         'outlets' => $outlets,
      ]);
   }

   public function koteditdeletelogfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;
      $outlet = $request->input('outlet', 'all');
      $mode = $request->input('mode', 'all');

      $query = DB::table('kotlog AS KL')
         ->leftJoin('depart AS D', 'KL.restcode', '=', 'D.dcode')
         ->where('KL.propertyid', $pid)
         ->whereBetween('KL.vdate', [$fromdate, $todate])
         ->select([
            'KL.vdate',
            'KL.vno AS KOTNo',
            'KL.vtime AS KOTTime',
            DB::raw("COALESCE(D.name, KL.restcode) AS OutletName"),
            'KL.roomno',
            'KL.item',
            'KL.qty',
            'KL.rate',
            'KL.amount',
            'KL.voidyn',
            'KL.waiter',
            'KL.pending',
            'KL.u_name',
            'KL.u_entdt',
            'KL.u_ae',
            'KL.delflag',
            'KL.reasons',
            'KL.ncreason',
            'KL.remarks',
            'KL.nckot',
         ])
         ->orderBy('KL.vdate', 'DESC')
         ->orderBy('KL.vno');

      if ($outlet !== 'all') {
         $query->where('KL.restcode', $outlet);
      }

      if ($mode === 'edited') {
         $query->where('KL.u_ae', 'e');
      } elseif ($mode === 'deleted') {
         $query->where('KL.delflag', 'Y');
      } elseif ($mode === 'voided') {
         $query->where('KL.voidyn', 'Y');
      } elseif ($mode === 'nc') {
         $query->where('KL.nckot', 'Y');
      }

      $data = $query->get();

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
         'totalAmount' => $data->sum('amount'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Revenue Analysis â€” revenue breakdown by source/vtype
   // Legacy: GRepFormName = "RevAnalysis"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function revenueanalysis(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.revenueanalysis', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function revenueanalysisfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Revenue by vtype from paycharge (FO charges)
      $foRevenue = DB::table('paycharge AS PC')
         ->leftJoin('revmast AS RM', 'PC.paycode', '=', 'RM.rev_code')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.vdate', [$fromdate, $todate])
         ->where('PC.amtdr', '>', 0)
         ->select([
            'PC.vtype',
            DB::raw('MAX(COALESCE(RM.name, PC.vtype)) AS RevName'),
            DB::raw('SUM(PC.amtdr) AS Amount'),
            DB::raw('COUNT(*) AS TxnCount'),
         ])
         ->groupBy('PC.vtype')
         ->orderBy('Amount', 'DESC')
         ->get();

      // Revenue by outlet from sale1 (POS)
      $posRevenue = DB::table('sale1 AS S1')
         ->leftJoin('depart AS D', 'S1.restcode', '=', 'D.dcode')
         ->leftJoin('stock AS S', 'S1.docid', '=', 'S.docid')
         ->where('S1.propertyid', $pid)
         ->whereBetween('S1.vdate', [$fromdate, $todate])
         ->select([
            DB::raw('COALESCE(D.name, S1.restcode) AS OutletName'),
            DB::raw('SUM(S1.netamt) AS Amount'),
            DB::raw('COUNT(DISTINCT S1.docid) AS BillCount'),
         ])
         ->groupBy('S1.restcode', 'D.name')
         ->orderBy('Amount', 'DESC')
         ->get();

      // Revenue by vtype from suntran (accounting postings)
      $accRevenue = DB::table('suntran AS ST')
         ->leftJoin('suntranh AS STH', function ($j) {
            $j->on('ST.docid', '=', 'STH.docid')
              ->on('ST.propertyid', '=', 'STH.propertyid');
         })
         ->where('ST.propertyid', $pid)
         ->whereBetween('ST.vdate', [$fromdate, $todate])
         ->where('ST.amtdr', '>', 0)
         ->select([
            'ST.vtype',
            DB::raw('SUM(ST.amtdr) AS Amount'),
            DB::raw('COUNT(*) AS TxnCount'),
         ])
         ->groupBy('ST.vtype')
         ->orderBy('Amount', 'DESC')
         ->get();

      $totalFO = $foRevenue->sum('Amount');
      $totalPOS = $posRevenue->sum('Amount');
      $totalAcc = $accRevenue->sum('Amount');

      return response()->json([
         'foRevenue' => $foRevenue,
         'posRevenue' => $posRevenue,
         'accRevenue' => $accRevenue,
         'totalFO' => $totalFO,
         'totalPOS' => $totalPOS,
         'totalAcc' => $totalAcc,
         'grandTotal' => $totalFO + $totalPOS + $totalAcc,
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Guest Charges MIS â€” charges summary per guest/folio
   // Legacy: GRepFormName = "GuestChargesMIS"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function guestchargesmis(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.guestchargesmis', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function guestchargesmisfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Guest charges summary: charges vs payments per folio
      $data = DB::table('paycharge AS PC')
         ->leftJoin('guestfolio AS GF', 'PC.folionodocid', '=', 'GF.docid')
         ->leftJoin('guestprof AS GP', 'GF.guestprof', '=', 'GP.guestcode')
         ->leftJoin('roomocc AS RO', 'PC.folionodocid', '=', 'RO.docid')
         ->leftJoin('room_cat AS RC', 'RO.roomcat', '=', 'RC.cat_code')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.vdate', [$fromdate, $todate])
         ->select([
            'PC.folionodocid AS FolioDocId',
            'GF.folio_no AS FolioNo',
            DB::raw('MAX(GP.name) AS GuestName'),
            DB::raw('MAX(RO.roomno) AS RoomNo'),
            DB::raw('MAX(RC.name) AS RoomType'),
            DB::raw('MAX(GF.vdate) AS CheckInDate'),
            DB::raw('SUM(PC.amtdr) AS TotalCharges'),
            DB::raw('SUM(PC.amtcr) AS TotalPayments'),
            DB::raw('SUM(PC.amtdr) - SUM(PC.amtcr) AS Balance'),
         ])
         ->groupBy('PC.folionodocid', 'GF.folio_no')
         ->havingRaw('ABS(SUM(PC.amtdr) - SUM(PC.amtcr)) > 0.01')
         ->orderBy('GP.name')
         ->get();

      return response()->json([
         'data' => $data,
         'total' => $data->count(),
         'totalCharges' => $data->sum('TotalCharges'),
         'totalPayments' => $data->sum('TotalPayments'),
         'totalBalance' => $data->sum('Balance'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Extra Charges During Stay â€” non-room charges (POS, laundry, etc.)
   // Legacy: GRepFormName = "ExtraChargesDuringStay"
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function extrachargesduringstay(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.extrachargesduringstay', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function extrachargesduringstayfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;

      // Extra charges = PPOS (POS room charge) + IPOS (POS in-house) + other non-RC vtypes
      $data = DB::table('paycharge AS PC')
         ->leftJoin('guestfolio AS GF', 'PC.folionodocid', '=', 'GF.docid')
         ->leftJoin('guestprof AS GP', 'GF.guestprof', '=', 'GP.guestcode')
         ->leftJoin('roomocc AS RO', 'PC.folionodocid', '=', 'RO.docid')
         ->leftJoin('room_cat AS RC', 'RO.roomcat', '=', 'RC.cat_code')
         ->where('PC.propertyid', $pid)
         ->whereBetween('PC.vdate', [$fromdate, $todate])
         ->whereIn('PC.vtype', ['PPOS', 'IPOS'])
         ->select([
            'PC.folionodocid AS FolioDocId',
            'GF.folio_no AS FolioNo',
            DB::raw('MAX(GP.name) AS GuestName'),
            DB::raw('MAX(RO.roomno) AS RoomNo'),
            DB::raw('MAX(RC.name) AS RoomType'),
            DB::raw('MAX(GF.vdate) AS CheckInDate'),
            'PC.vtype',
            DB::raw('SUM(PC.amtdr) AS Amount'),
            DB::raw('COUNT(*) AS TxnCount'),
         ])
         ->groupBy('PC.folionodocid', 'GF.folio_no', 'PC.vtype')
         ->orderBy('GP.name')
         ->orderBy('PC.vtype')
         ->get();

      // Aggregate by folio
      $byFolio = $data->groupBy('FolioDocId')->map(function ($rows) {
         return [
            'FolioDocId' => $rows->first()->FolioDocId,
            'FolioNo' => $rows->first()->FolioNo,
            'GuestName' => $rows->first()->GuestName,
            'RoomNo' => $rows->first()->RoomNo,
            'RoomType' => $rows->first()->RoomType,
            'CheckInDate' => $rows->first()->CheckInDate,
            'TotalExtra' => $rows->sum('Amount'),
            'TxnCount' => $rows->sum('TxnCount'),
            'Breakdown' => $rows->pluck('Amount', 'vtype')->toArray(),
         ];
      })->values();

      return response()->json([
         'data' => $byFolio,
         'total' => $byFolio->count(),
         'totalExtra' => $byFolio->sum('TotalExtra'),
         'totalTxn' => $byFolio->sum('TxnCount'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Advance Reconciliation â€” 3-way match: Booking â†’ PayCharge â†’ Folio
   // Detects mismatches between reservation advance and posted payments
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function advancereconcil(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      $comp = DB::table('company')->where('propertyid', $this->propertyid)->first();
      return view('property.advancereconcil', ['comp' => $comp, 'fromdate' => $this->ncurdate]);
   }

   public function advancereconcilfetch(Request $request)
   {
      $fromdate = $request->input('fromdate');
      $todate = $request->input('todate');
      $pid = $this->propertyid;
      $filter = $request->input('filter', 'all');

      // Step 1: Get all ADRES (advance) PayCharge records grouped by refdocid
      $allAdvances = DB::table('paycharge AS PC')
         ->where('PC.propertyid', $pid)
         ->where('PC.vtype', 'ADRES')
         ->whereBetween('PC.vdate', [$fromdate, $todate])
         ->select([
            'PC.refdocid',
            'PC.paytype',
            'PC.amtcr',
            'PC.amtdr',
            'PC.folionodocid',
            'PC.vdate',
            'PC.sno',
         ])
         ->get()
         ->groupBy('refdocid');

      // Step 2: Get booking details for these refdocids
      $bookingIds = $allAdvances->keys()->toArray();
      $bookings = DB::table('booking AS B')
         ->leftJoin('guestprof AS GP', 'B.GuestProf', '=', 'GP.guestcode')
         ->leftJoin('grpbookingdetails AS GBD', function ($j) use ($pid) {
            $j->on('GBD.BookingDocid', '=', 'B.DocId')
              ->where('GBD.Property_ID', '=', $pid)
              ->where('GBD.Sno', '=', 1);
         })
         ->where('B.Property_ID', $pid)
         ->whereIn('B.DocId', $bookingIds)
         ->select([
            'B.DocId AS BookingDocId',
            'B.BookNo',
            'GP.name AS GuestName',
            'GBD.RoomNo',
            'GBD.ArrDate',
            'GBD.DepDate',
            'B.Cancel',
            'B.ResStatus',
         ])
         ->get();

      // Step 3: Reconcile
      $results = $bookings->map(function ($b) use ($allAdvances) {
         $pcRecords = $allAdvances->get($b->BookingDocId, collect());
         $postedCredit = (float) $pcRecords->sum('amtcr');
         $postedDebit = (float) $pcRecords->sum('amtdr');
         $totalAdvance = $postedCredit - $postedDebit; // This IS the booking advance

         // Check if any ADRES record has a folio reference (advance transferred at check-in)
         $hasFolio = $pcRecords->where('folionodocid', '<>', '')->where('folionodocid', '<>', null)->count() > 0;

         // Status determination
         // Since totalAdvance comes from paycharge, all records with ADRES are the source of truth
         if ($totalAdvance <= 0) {
            $status = 'NO_ADVANCE';
            $mismatch = 0;
         } elseif ($b->Cancel === 'Y') {
            // Cancelled booking with advance â€” check if refund was processed
            $refundCheck = DB::table('paycharge')
               ->where('propertyid', $b->BookingDocId ? '' : '')
               ->where('refdocid', $b->BookingDocId)
               ->where('vtype', 'ADRES')
               ->where('amtdr', '>', 0)
               ->sum('amtdr');
            $status = $refundCheck > 0 ? 'REFUNDED' : 'CANCELLED_NO_REFUND';
            $mismatch = $refundCheck > 0 ? 0 : $totalAdvance;
         } elseif ($hasFolio) {
            $status = 'RECONCILED';
            $mismatch = 0;
         } else {
            // Advance collected but not yet checked in or not transferred to folio
            $status = 'ADVANCE_ONLY';
            $mismatch = 0; // Not a mismatch â€” just advance collected, check-in pending
         }

         return [
            'BookingDocId' => $b->BookingDocId,
            'BookNo' => $b->BookNo,
            'GuestName' => $b->GuestName,
            'RoomNo' => $b->RoomNo,
            'ArrDate' => $b->ArrDate,
            'DepDate' => $b->DepDate,
            'Cancel' => $b->Cancel,
            'ResStatus' => $b->ResStatus,
            'BookingAdvance' => $totalAdvance,
            'PostedCredit' => $postedCredit,
            'PostedDebit' => $postedDebit,
            'NetPosted' => $totalAdvance,
            'Mismatch' => $mismatch,
            'HasFolio' => $hasFolio,
            'Status' => $status,
         ];
      });

      // Apply filter
      if ($filter === 'mismatch') {
         $results = $results->filter(fn($r) => $r['Mismatch'] > 0);
      } elseif ($filter === 'not_posted') {
         $results = $results->filter(fn($r) => $r['Status'] === 'NOT_POSTED');
      } elseif ($filter === 'cancelled') {
         $results = $results->filter(fn($r) => $r['Cancel'] === 'Y');
      }

      $results = $results->values();

      return response()->json([
         'data' => $results,
         'total' => $results->count(),
         'mismatchCount' => $results->filter(fn($r) => $r['Mismatch'] > 0)->count(),
         'totalBookingAdvance' => $results->sum('BookingAdvance'),
         'totalNetPosted' => $results->sum('NetPosted'),
         'totalMismatch' => $results->sum('Mismatch'),
      ]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Party Outstanding Report â€” banquet party wise outstanding
   // Legacy: PartyOutStanding â€” HallSale1 vs PaychargeH advance
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function partyoutstanding(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }
      return view('property.partyoutstanding');
   }

   public function partyoutstandingfetch(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return response()->json(['error' => 'No permission']);
      }

      $fromdate = $request->input('fromdate', date('Y-m-d'));
      $todate = $request->input('todate', date('Y-m-d'));

      $results = DB::select("
         SELECT H.DocID, HB.VDate AS BookDate,
                V.FromDate AS FuncStartDate, V.ToDate AS FuncEndDate,
                V.FromTime AS FuncStartTime, V.ToTime AS FuncEndTime,
                F.Name AS FuncName, H.Party AS PartyName,
                H.Vno as BillNo, H.VDate as BillDate,
                (H.NetAmt) AS Amount,
                IFNULL((SELECT SUM(amtcr) FROM paycharge WHERE contradocid=H.BookDocID AND vtype='HADV'),0) as Advance,
                (H.NetAmt) - IFNULL((SELECT SUM(amtcr) FROM paycharge WHERE contradocid=H.BookDocID AND vtype='HADV'),0) as Balance,
                H.BookDocID
         FROM hallsale1 AS H
         LEFT JOIN paycharge AS P ON H.DocId = P.DocId
         LEFT JOIN hallbook AS HB ON H.BookDocID = HB.DocID
         LEFT JOIN functiontype AS F ON HB.Func_Name = F.Code
         LEFT JOIN venueocc AS V ON HB.DocId = V.FPDocId
         WHERE H.propertyid = ?
         AND H.VDate BETWEEN ? AND ?
         AND (H.NetAmt) - IFNULL((SELECT SUM(amtcr) FROM paycharge WHERE contradocid=H.BookDocID AND vtype='HADV'),0) > 0
         ORDER BY H.Party, H.Vno
      ", [$this->propertyid, $fromdate, $todate]);

      return response()->json($results);
   }




   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // Plan Report â€” plan/room category wise booking analysis
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function planreport(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return redirect()->back()->with('error', 'No permission');
      }
      return view('property.planreport');
   }

   public function planreportfetch(Request $request)
   {
      $permission = revokeopen(131211);
      if (is_null($permission) || $permission->view == 0) {
         return response()->json(['error' => 'No permission']);
      }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $r = DB::select('SELECT B.Plan AS PlanCode, PM.Name AS PlanName, RC.Name AS RoomCategory, COUNT(*) AS TotalBookings, SUM(B.NoofRooms) AS TotalRooms, SUM(B.Adults) AS TotalAdults, SUM(B.Child) AS TotalChildren, AVG(B.Rate) AS AvgRate, SUM(B.Rate*B.NoofRooms) AS TotalRevenue FROM booking AS B LEFT JOIN planmast AS PM ON B.Plan=PM.Code LEFT JOIN roomcategory AS RC ON B.RoomCat=RC.Code WHERE B.Property_ID=? AND B.ArrDate BETWEEN ? AND ? GROUP BY B.Plan,PM.Name,RC.Name ORDER BY PlanName,RoomCategory', [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }


   public function guestwiseanalysis(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.guestwiseanalysis");
   }

   public function guestwiseanalysisfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT GP.DocID AS GuestCode,GP.FirstName,GP.LastName,GP.Mobile,CM.Name AS CompanyName,COUNT(DISTINCT B.DocID) AS TotalBookings,IFNULL(SUM(PC.amtdr),0) AS TotalCharges,IFNULL(SUM(PC.amtcr),0) AS TotalPayments FROM guestprof AS GP LEFT JOIN booking AS B ON GP.DocID=B.GuestCode AND B.Property_ID=? LEFT JOIN companyreg AS CM ON B.Compid=CM.Code LEFT JOIN paycharge AS PC ON B.DocID=PC.refdocid WHERE GP.propertyid=? AND B.ArrDate BETWEEN ? AND ? GROUP BY GP.DocID,GP.FirstName,GP.LastName,GP.Mobile,CM.Name ORDER BY TotalCharges DESC", [$this->propertyid,$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function guestwiserevenue(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.guestwiserevenue");
   }

   public function guestwiserevenuefetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT GP.DocID AS GuestCode,GP.FirstName,GP.LastName,B.RoomNo,RC.Name AS RoomCategory,B.Rate,B.ArrDate,B.DepDate,IFNULL(SUM(PC.amtdr),0) AS RoomRent,IFNULL(SUM(PC.amtcr),0) AS Payments FROM guestprof AS GP INNER JOIN booking AS B ON GP.DocID=B.GuestCode AND B.Property_ID=? LEFT JOIN roomcategory AS RC ON B.RoomCat=RC.Code LEFT JOIN paycharge AS PC ON B.DocID=PC.refdocid WHERE GP.propertyid=? AND B.ArrDate BETWEEN ? AND ? GROUP BY GP.DocID,GP.FirstName,GP.LastName,B.RoomNo,RC.Name,B.Rate,B.ArrDate,B.DepDate ORDER BY B.ArrDate", [$this->propertyid,$this->propertyid,$fd,$td]);
      return response()->json($r);
   }


   public function revenueanalysis2(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.revenueanalysis2");
   }

   public function revenueanalysis2fetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT D.Name AS DeptName, COUNT(DISTINCT PC.DocID) AS TotalBills, IFNULL(SUM(PC.amtdr),0) AS TotalRevenue, IFNULL(SUM(PC.amtcr),0) AS TotalPayments FROM paycharge AS PC LEFT JOIN depart AS D ON PC.dcode=D.Code WHERE PC.propertyid=? AND PC.vdate BETWEEN ? AND ? GROUP BY D.Name ORDER BY TotalRevenue DESC", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function gratuityreport(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.gratuityreport");
   }

   public function gratuityreportfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT PC.vdate AS VDate, PC.DocID, PC.Vno, PC.amtdr AS Amount, PC.remark AS Remark FROM paycharge AS PC WHERE PC.propertyid=? AND PC.vdate BETWEEN ? AND ? AND PC.vtype IN (GC,SC) ORDER BY PC.vdate", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function cashiercollectionmis(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.cashiercollectionmis");
   }

   public function cashiercollectionmisfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT PC.vdate AS VDate, U.name AS UserName, COUNT(*) AS TotalBills, IFNULL(SUM(PC.amtdr),0) AS TotalCharges, IFNULL(SUM(PC.amtcr),0) AS TotalCollections FROM paycharge AS PC LEFT JOIN users AS U ON PC.uid=U.id WHERE PC.propertyid=? AND PC.vdate BETWEEN ? AND ? GROUP BY PC.vdate, U.name ORDER BY PC.vdate", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }


   public function accountchecklist(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.accountchecklist");
   }

   public function accountchecklistfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT SG.name AS SubGroupName, IFNULL(SUM(L.amtdr),0) AS TotalDebit, IFNULL(SUM(L.amtcr),0) AS TotalCredit FROM ledger AS L LEFT JOIN subgroup AS SG ON L.subcode = SG.sub_code AND SG.propertyid = L.propertyid WHERE L.propertyid=? AND L.vdate BETWEEN ? AND ? GROUP BY SG.name ORDER BY SG.name", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function deliverystatus(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.deliverystatus");
   }

   public function deliverystatusfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT B.DocID AS BookingDocID, B.RoomNo, GP.FirstName, GP.LastName, SR.ReqType AS ServiceType, SR.Description, SR.Status, SR.CreatedAt FROM servicerequest AS SR LEFT JOIN booking AS B ON SR.booking_id=B.DocID LEFT JOIN guestprof AS GP ON B.GuestCode=GP.DocID WHERE SR.propertyid=? AND SR.created_at BETWEEN ? AND ? ORDER BY SR.created_at DESC", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function functionwiseitemdetail(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.functionwiseitemdetail");
   }

   public function functionwiseitemdetailfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT F.Name AS FuncName, H.Party AS PartyName, H.Vno AS BillNo, H2.ItemName, H2.Qty, H2.Rate, H2.Amount FROM hallsale2 AS H2 LEFT JOIN hallsale1 AS H ON H2.DocID=H.DocID LEFT JOIN hallbook AS HB ON H.BookDocID=HB.DocID LEFT JOIN functiontype AS F ON HB.Func_Name=F.Code WHERE H.propertyid=? AND H.Vdate BETWEEN ? AND ? ORDER BY F.Name,H.Party", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }


   public function itemwisesalehall(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.itemwisesalehall");
   }

   public function itemwisesalehallfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT H2.ItemName, SUM(H2.Qty) AS TotalQty, SUM(H2.Amount) AS TotalAmount FROM hallsale2 AS H2 LEFT JOIN hallsale1 AS H ON H2.DocID=H.DocID WHERE H.propertyid=? AND H.Vdate BETWEEN ? AND ? GROUP BY H2.ItemName ORDER BY TotalAmount DESC", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function htcashiersumm(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.htcashiersumm");
   }

   public function htcashiersummfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT PC.vdate AS VDate, PC.paymode AS PayMode, COUNT(*) AS TotalBills, IFNULL(SUM(PC.amtdr),0) AS TotalCharges, IFNULL(SUM(PC.amtcr),0) AS TotalCollections FROM paycharge AS PC WHERE PC.propertyid=? AND PC.vdate BETWEEN ? AND ? GROUP BY PC.vdate, PC.paymode ORDER BY PC.vdate", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   public function billwiseadjustment(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with("error","No permission"); }
      return view("property.billwiseadjustment");
   }

   public function billwiseadjustmentfetch(Request $request)
   {
      $p = revokeopen(131211);
      if (is_null($p) || $p->view == 0) { return response()->json(["error"=>"No permission"]); }
      $fd = $request->input("fromdate",date("Y-m-d"));
      $td = $request->input("todate",date("Y-m-d"));
      $r = DB::select("SELECT PC.vdate AS VDate, PC.DocID, PC.Vno, PC.vtype AS VType, PC.amtdr AS Debit, PC.amtcr AS Credit, PC.paymode AS PayMode, PC.remark AS Remark FROM paycharge AS PC WHERE PC.propertyid=? AND PC.vdate BETWEEN ? AND ? AND PC.vtype IN (ADJ,REV) ORDER BY PC.vdate", [$this->propertyid,$fd,$td]);
      return response()->json($r);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // MISSING REPORTS â€” POS, Banquet, HR, Membership (added by AI migration 2)
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   public function kotratechange(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.kotratechange", compact("propertyid", "fromdate", "todate"));
   }
   public function kotratechangefetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("kot as k")->join("kotdetail as kd", "k.kotid", "=", "kd.kotid")
         ->leftJoin("itemmast as im", "kd.itemcode", "=", "im.itemcode")
         ->where("k.propertyid", $propertyid)->whereBetween("k.kotdate", [$fromdate, $todate])
         ->select("k.kotid", "k.kotdate", "k.outletname", "im.itemname", "k.kotrate as original_rate", "kd.rate as charged_rate")
         ->orderByDesc("k.kotdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function fombillchangereport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.fombillchangereport", compact("propertyid", "fromdate", "todate"));
   }
   public function fombillchangereportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("paycharge as pc")
         ->leftJoin("roomocc as ro", "pc.foliono", "=", "ro.foliono")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("pc.propertyid", $propertyid)
         ->whereBetween("pc.vdate", [$fromdate, $todate])
         ->where("pc.paycode", "=", "C")
         ->whereNotNull("pc.u_updatedt")
         ->select("pc.docid", "pc.vdate", "pc.foliono", DB::raw("COALESCE(bd.guestname,\"Walk-in\") as guestname"),
            "pc.amtdr", "pc.mnarr", "pc.u_name", "pc.u_updatedt")
         ->orderByDesc("pc.u_updatedt")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function koteditdeletelog2(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.koteditdeletelog", compact("propertyid", "fromdate", "todate"));
   }
   public function koteditdeletelog2fetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("kotlog")->where("propertyid", $propertyid)
         ->whereBetween("kotdate", [$fromdate, $todate])
         ->orderByDesc("kotdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function liquorsalerep(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.liquorsalerep", compact("propertyid", "fromdate", "todate"));
   }
   public function liquorsalerepfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->join("sale2 as sd", "s.billno", "=", "sd.billno")
         ->leftJoin("itemmast as im", "sd.itemcode", "=", "im.itemcode")
         ->where("s.propertyid", $propertyid)->whereBetween("s.billdate", [$fromdate, $todate])
         ->select("s.billno", "s.billdate", "s.outletname", "im.itemname", "sd.qty", "sd.rate", "sd.amount")
         ->orderByDesc("s.billdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp, "total" => $rows->sum("amount")]);
   }

   public function tablewisesale(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.tablewisesale", compact("propertyid", "fromdate", "todate"));
   }
   public function tablewisesalefetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->where("s.propertyid", $propertyid)
         ->whereBetween("s.billdate", [$fromdate, $todate])
         ->select("s.tablename", DB::raw("COUNT(*) as bills"), DB::raw("SUM(s.netamt) as total_sale"),
            DB::raw("SUM(s.taxamount) as total_tax"))
         ->groupBy("s.tablename")->orderByDesc("total_sale")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function orderdetailreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.orderdetailreport", compact("propertyid", "fromdate", "todate"));
   }
   public function orderdetailreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("kot as k")->join("kotdetail as kd", "k.kotid", "=", "kd.kotid")
         ->leftJoin("itemmast as im", "kd.itemcode", "=", "im.itemcode")
         ->where("k.propertyid", $propertyid)->whereBetween("k.kotdate", [$fromdate, $todate])
         ->select("k.kotid", "k.kotdate", "k.outletname", "k.tablename", "k.guestname",
            "im.itemname", "kd.qty", "kd.rate", "kd.amount", "k.status")
         ->orderByDesc("k.kotdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function saleregpercover(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.saleregpercover", compact("propertyid", "fromdate", "todate"));
   }
   public function saleregpercoverfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->where("s.propertyid", $propertyid)
         ->whereBetween("s.billdate", [$fromdate, $todate])
         ->select("s.outletname", DB::raw("COUNT(*) as covers"), DB::raw("SUM(s.netamt) as total_sale"),
            DB::raw("SUM(s.netamt)/NULLIF(COUNT(*),0) as sale_per_cover"))
         ->groupBy("s.outletname")->orderByDesc("total_sale")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function tallyposreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.tallyposreport", compact("propertyid", "fromdate", "todate"));
   }
   public function tallyposreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->where("s.propertyid", $propertyid)
         ->whereBetween("s.billdate", [$fromdate, $todate])
         ->select("s.billno", "s.billdate", "s.outletname", "s.guestname",
            "s.netamt", "s.taxamount", "s.discount", "s.roundoff", "s.paymentmode")
         ->orderBy("s.billdate")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp, "total" => $rows->sum("netamt")]);
   }

   public function companywisesalehall(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.companywisesalehall", compact("propertyid", "fromdate", "todate"));
   }
   public function companywisesalehallfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("hallsale as hs")
         ->leftJoin("companyreg as cr", "hs.companyid", "=", "cr.companyid")
         ->where("hs.propertyid", $propertyid)->whereBetween("hs.billdate", [$fromdate, $todate])
         ->select("hs.billno", "hs.billdate", DB::raw("COALESCE(cr.comp_name,hs.companyname) as companyname"),
            "hs.hallname", "hs.netamt", "hs.taxamount")
         ->orderByDesc("hs.billdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp, "total" => $rows->sum("netamt")]);
   }

   public function excessconsumption(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.excessconsumption", compact("propertyid", "fromdate", "todate"));
   }
   public function excessconsumptionfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("hallbook as hb")
         ->leftJoin("hallsale as hs", "hb.bookno", "=", "hs.bookno")
         ->where("hb.propertyid", $propertyid)->whereBetween("hb.functiondate", [$fromdate, $todate])
         ->select("hb.bookno", "hb.hallname", "hb.companyname", "hb.functiondate",
            "hb.noofpax", "hs.consumption", "hs.excessconsumption")
         ->orderByDesc("hb.functiondate")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function productionreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.productionreport", compact("propertyid", "fromdate", "todate"));
   }
   public function productionreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("kot as k")->join("kotdetail as kd", "k.kotid", "=", "kd.kotid")
         ->leftJoin("itemmast as im", "kd.itemcode", "=", "im.itemcode")
         ->where("k.propertyid", $propertyid)->whereBetween("k.kotdate", [$fromdate, $todate])
         ->select("im.itemname", DB::raw("SUM(kd.qty) as total_qty"),
            DB::raw("SUM(kd.amount) as total_amount"))
         ->groupBy("im.itemname")->orderByDesc("total_qty")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function openitemsale(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.openitemsale", compact("propertyid", "fromdate", "todate"));
   }
   public function openitemsalefetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->join("sale2 as sd", "s.billno", "=", "sd.billno")
         ->leftJoin("itemmast as im", "sd.itemcode", "=", "im.itemcode")
         ->where("s.propertyid", $propertyid)->whereBetween("s.billdate", [$fromdate, $todate])
         ->where("s.paymentmode", "!=", "Room")
         ->select("s.billno", "s.billdate", "s.outletname", "im.itemname", "sd.qty", "sd.rate", "sd.amount")
         ->orderByDesc("s.billdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function abcanalysis(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.abcanalysis", compact("propertyid", "fromdate", "todate"));
   }
   public function abcanalysisfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("paycharge as pc")
         ->leftJoin("roomocc as ro", "pc.foliono", "=", "ro.foliono")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("pc.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->select(DB::raw("COALESCE(bd.guestname,\"Walk-in\") as guestname"),
            DB::raw("SUM(pc.amtdr) as total_charges"))
         ->groupBy("guestname")->orderByDesc("total_charges")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function abcanalysisSale(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.abcanalysissale", compact("propertyid", "fromdate", "todate"));
   }
   public function abcanalysisSaleFetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->join("sale2 as sd", "s.billno", "=", "sd.billno")
         ->leftJoin("itemmast as im", "sd.itemcode", "=", "im.itemcode")
         ->where("s.propertyid", $propertyid)->whereBetween("s.billdate", [$fromdate, $todate])
         ->select("im.itemname", DB::raw("SUM(sd.qty) as total_qty"), DB::raw("SUM(sd.amount) as total_amount"))
         ->groupBy("im.itemname")->orderByDesc("total_amount")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function cancellletter(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.cancellletter", compact("propertyid"));
   }
   public function cancellletterdata(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $docid = $request->input("docid");
      $row = DB::table("grpbookingdetails as bd")
         ->leftJoin("guestprofile as gp", "bd.guestprofid", "=", "gp.id")
         ->where("bd.propertyid", $propertyid)->where("bd.docid", $docid)->first();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $row, "comp" => $comp]);
   }

   public function confirletter(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.confirletter", compact("propertyid"));
   }
   public function confirletterdata(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $docid = $request->input("docid");
      $row = DB::table("grpbookingdetails as bd")
         ->leftJoin("guestprofile as gp", "bd.guestprofid", "=", "gp.id")
         ->where("bd.propertyid", $propertyid)->where("bd.docid", $docid)->first();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $row, "comp" => $comp]);
   }

   public function guestchargesmis2(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.guestchargesmis", compact("propertyid", "fromdate", "todate"));
   }
   public function guestchargesmis2fetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("paycharge as pc")
         ->leftJoin("roomocc as ro", "pc.foliono", "=", "ro.foliono")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("pc.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->where("pc.paycode", "C")
         ->select(DB::raw("COALESCE(bd.guestname,\"Walk-in\") as guestname"),
            DB::raw("COALESCE(ro.rmcode,\"\") as room"),
            "pc.foliono", "pc.vdate", "pc.docid", "pc.amtdr", "pc.mnarr")
         ->orderByDesc("pc.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp, "total" => $rows->sum("amtdr")]);
   }

   public function memled(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.memled", compact("propertyid", "fromdate", "todate"));
   }
   public function memledfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $memberid = $request->input("memberid");
      $query = DB::table("membermaster as mm")
         ->leftJoin("memberledger as ml", "mm.memberid", "=", "ml.memberid")
         ->where("mm.propertyid", $propertyid);
      if ($memberid) $query->where("mm.memberid", $memberid);
      $rows = $query->select("mm.memberid", "mm.membername", "ml.vdate", "ml.docid", "ml.amtdr", "ml.amtcr", "ml.mnarr")
         ->orderBy("mm.membername")->orderBy("ml.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function memtaxreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.memtaxreport", compact("propertyid", "fromdate", "todate"));
   }
   public function memtaxreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("membermaster as mm")
         ->leftJoin("paycharge as pc", "mm.memberid", "=", "pc.subcode")
         ->where("mm.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->select("mm.memberid", "mm.membername", "pc.vdate", "pc.amtdr", "pc.tax")
         ->orderBy("mm.membername")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function payslip(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.payslip", compact("propertyid"));
   }
   public function payslipfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("Y-m"));
      $rows = DB::table("salarycreation as sc")
         ->leftJoin("employee as e", "sc.empid", "=", "e.empid")
         ->where("sc.propertyid", $propertyid)->whereRaw("DATE_FORMAT(sc.paydate, \"%Y-%m\") = ?", [$month])
         ->select("e.empid", "e.empname", "e.designation", "sc.gross", "sc.deduction", "sc.netpay", "sc.paydate")
         ->orderBy("e.empname")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function pfstatement(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.pfstatement", compact("propertyid"));
   }
   public function pfstatementfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("Y-m"));
      $rows = DB::table("salarycreation as sc")
         ->leftJoin("employee as e", "sc.empid", "=", "e.empid")
         ->where("sc.propertyid", $propertyid)->whereRaw("DATE_FORMAT(sc.paydate, \"%Y-%m\") = ?", [$month])
         ->select("e.empid", "e.empname", "sc.pfemployee", "sc.pfemployer", "sc.paydate")
         ->orderBy("e.empname")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function payrollreg(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.payrollreg", compact("propertyid", "fromdate", "todate"));
   }
   public function payrollregfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("salarycreation as sc")
         ->leftJoin("employee as e", "sc.empid", "=", "e.empid")
         ->where("sc.propertyid", $propertyid)->whereBetween("sc.paydate", [$fromdate, $todate])
         ->select("e.empid", "e.empname", "e.designation", "sc.basic", "sc.hra", "sc.da",
            "sc.gross", "sc.deduction", "sc.pfemployee", "sc.esi", "sc.netpay", "sc.paydate")
         ->orderBy("e.empname")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function dailydiet(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.dailydiet", compact("propertyid", "fromdate", "todate"));
   }
   public function dailydietfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("sale1 as s")->join("sale2 as sd", "s.billno", "=", "sd.billno")
         ->leftJoin("itemmast as im", "sd.itemcode", "=", "im.itemcode")
         ->where("s.propertyid", $propertyid)->whereBetween("s.billdate", [$fromdate, $todate])
         ->select("s.billdate", "im.itemname", DB::raw("SUM(sd.qty) as qty"), DB::raw("SUM(sd.amount) as amount"))
         ->groupBy("s.billdate", "im.itemname")->orderBy("s.billdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function annexure(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.annexure", compact("propertyid", "fromdate", "todate"));
   }
   public function annexurefetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("suntran as st")
         ->where("st.propertyid", $propertyid)->whereBetween("st.vdate", [$fromdate, $todate])
         ->select("st.docid", "st.vdate", "st.vtype", "st.narr", "st.amt", "st.amtcr", "st.amtdr")
         ->orderBy("st.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }


   public function cardstatusreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.cardstatusreport", compact("propertyid"));
   }
   public function cardstatusreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $rows = DB::table("smartcard as sc")
         ->leftJoin("guestprofile as gp", "sc.guestprofid", "=", "gp.id")
         ->where("sc.propertyid", $propertyid)
         ->select("sc.cardno", "sc.cardstatus", DB::raw("COALESCE(gp.guestname,\"\") as guestname"),
            "sc.balance", "sc.issuedate", "sc.expdate")
         ->orderBy("sc.cardno")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }


   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // MISSING REPORTS â€” Batch 3 (29 remaining reports)
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   // Membership Reports
   public function birthmarrrep(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.birthmarrrep", compact("propertyid"));
   }
   public function birthmarrrepfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("m"));
      $rows = DB::table("membermaster")->where("propertyid", $propertyid)
         ->whereRaw("MONTH(dob) = ? OR MONTH(weddate) = ?", [$month, $month])
         ->select("memberid", "membername", "dob", "weddate", "phone", "email")
         ->orderBy("membername")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function membillmissingreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.membillmissingreport", compact("propertyid", "fromdate", "todate"));
   }
   public function membillmissingreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("membermaster as mm")
         ->leftJoin("paycharge as pc", "mm.memberid", "=", "pc.subcode")
         ->where("mm.propertyid", $propertyid)
         ->where(function($q) use ($fromdate, $todate) {
            $q->whereNull("pc.docid")->orWhere("pc.vdate", "not between", [$fromdate, $todate]);
         })
         ->select("mm.memberid", "mm.membername", "mm.phone", "pc.docid", "pc.vdate")
         ->orderBy("mm.membername")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function membirthanndtls(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("m"));
      return view("property.membirthanndtls", compact("propertyid", "month"));
   }
   public function membirthanndtlsfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("m"));
      $rows = DB::table("membermaster")->where("propertyid", $propertyid)
         ->whereRaw("MONTH(dob) = ?", [$month])
         ->select("memberid", "membername", "dob", "phone", "email", "address")
         ->orderBy("dob")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function memalinglabels(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.memalinglabels", compact("propertyid"));
   }
   public function memalinglabelsfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $rows = DB::table("membermaster")->where("propertyid", $propertyid)
         ->select("membername", "address", "city", "pin", "phone", "email")
         ->orderBy("membername")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function memsalesregister(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.memsalesregister", compact("propertyid", "fromdate", "todate"));
   }
   public function memsalesregisterfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("membermaster as mm")
         ->leftJoin("paycharge as pc", "mm.memberid", "=", "pc.subcode")
         ->where("mm.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->select("mm.memberid", "mm.membername", "pc.vdate", "pc.docid", "pc.amtdr", "pc.mnarr")
         ->orderBy("mm.membername")->orderBy("pc.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function memvisitdetail(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.memvisitdetail", compact("propertyid", "fromdate", "todate"));
   }
   public function memvisitdetailfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("membermaster as mm")
         ->leftJoin("roomocc as ro", "mm.memberid", "=", "ro.subcode")
         ->where("mm.propertyid", $propertyid)
         ->whereBetween("ro.checkindt", [$fromdate, $todate])
         ->select("mm.memberid", "mm.membername", "ro.rmcode", "ro.checkindt", "ro.checkoutdt", "ro.foliono")
         ->orderBy("mm.membername")->orderBy("ro.checkindt")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   // Front Office Reports
   public function complaintlist(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.complaintlist", compact("propertyid", "fromdate", "todate"));
   }
   public function complaintlistfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("tickets as t")
         ->where("t.propertyid", $propertyid)->whereBetween("t.created_at", [$fromdate, $todate . " 23:59:59"])
         ->select("t.id", "t.problem", "t.status", "t.created_at", "t.u_name")
         ->orderByDesc("t.created_at")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function formiii(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.formiii", compact("propertyid", "fromdate", "todate"));
   }
   public function formiiifetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->leftJoin("guestprofile as gp", "bd.guestprofid", "=", "gp.id")
         ->where("ro.propertyid", $propertyid)
         ->whereBetween("ro.checkindt", [$fromdate, $todate])
         ->select("ro.foliono", "ro.rmcode", "ro.checkindt", "ro.checkoutdt",
            DB::raw("COALESCE(gp.guestname, bd.guestname) as guestname"),
            DB::raw("COALESCE(gp.idproof, bd.idproof) as idproof"),
            DB::raw("COALESCE(gp.idno, bd.idno) as idno"),
            "ro.adult", "ro.children")
         ->orderByDesc("ro.checkindt")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function registrationcard(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.registrationcard", compact("propertyid"));
   }
   public function registrationcarddata(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $docid = $request->input("docid");
      $row = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->leftJoin("guestprofile as gp", "bd.guestprofid", "=", "gp.id")
         ->where("ro.propertyid", $propertyid)->where("ro.docid", $docid)->first();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $row, "comp" => $comp]);
   }

   // Plan/Meal Reports
   public function planmealtokens(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.planmealtokens", compact("propertyid", "fromdate", "todate"));
   }
   public function planmealtokensfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->leftJoin("kot as k", "ro.foliono", "=", "k.foliono")
         ->where("ro.propertyid", $propertyid)
         ->whereBetween("k.kotdate", [$fromdate, $todate])
         ->select("ro.rmcode", "ro.foliono", DB::raw("COALESCE(bd.guestname,\"Walk-in\") as guestname"),
            "ro.plan", "k.tokenno", "k.outletname", "k.kotdate")
         ->orderBy("k.kotdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function planpackschedule(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.planpackschedule", compact("propertyid", "fromdate", "todate"));
   }
   public function planpackschedulefetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("planmaster as pm")
         ->leftJoin("planpack as pp", "pm.planid", "=", "pp.planid")
         ->where("pm.propertyid", $propertyid)
         ->select("pm.planname", "pp.mealtype", "pp.itemname", "pp.dayno")
         ->orderBy("pm.planname")->orderBy("pp.dayno")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function planpackservice(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.planpackservice", compact("propertyid"));
   }
   public function planpackservicefetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $rows = DB::table("planmaster as pm")
         ->leftJoin("planpack as pp", "pm.planid", "=", "pp.planid")
         ->where("pm.propertyid", $propertyid)
         ->select("pm.planname", "pm.planrate", "pp.mealtype", "pp.itemname", "pp.qty")
         ->orderBy("pm.planname")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   // HR Report
   public function attendancerep(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.attendancerep", compact("propertyid", "fromdate", "todate"));
   }
   public function attendancerepfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("attendance as a")
         ->leftJoin("employee as e", "a.empid", "=", "e.empid")
         ->where("a.propertyid", $propertyid)->whereBetween("a.attdate", [$fromdate, $todate])
         ->select("e.empid", "e.empname", "e.designation", "a.attdate", "a.intime", "a.outtime", "a.status")
         ->orderBy("e.empname")->orderBy("a.attdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   // Finance/Analysis Reports
   public function budgetanalysis(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.budgetanalysis", compact("propertyid", "fromdate", "todate"));
   }
   public function budgetanalysisfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("budget as b")
         ->leftJoin("acgroup as ag", "b.group_code", "=", "ag.group_code")
         ->where("b.propertyid", $propertyid)
         ->select("ag.group_name", "b.budgetamt", "b.period",
            DB::raw("(SELECT COALESCE(SUM(amtdr-amtcr),0) FROM ledger WHERE propertyid=b.propertyid AND vdate BETWEEN ? AND ?) as actual"))
         ->addBinding([$fromdate, $todate], "select")
         ->orderBy("ag.group_name")->limit(100)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function businessanalysis(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.businessanalysis", compact("propertyid", "fromdate", "todate"));
   }
   public function businessanalysisfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->where("ro.propertyid", $propertyid)
         ->whereRaw("ro.checkindt <= ? AND (ro.checkoutdt IS NULL OR ro.checkoutdt >= ?)", [$todate, $fromdate])
         ->select(DB::raw("DATE(ro.checkindt) as date"), DB::raw("COUNT(*) as rooms"),
            DB::raw("SUM(ro.adult + COALESCE(ro.children,0)) as pax"))
         ->groupBy(DB::raw("DATE(ro.checkindt)"))
         ->orderBy("date")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function bussoccupancyreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.bussoccupancyreport", compact("propertyid", "fromdate", "todate"));
   }
   public function bussoccupancyreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("ro.propertyid", $propertyid)
         ->whereBetween("ro.checkindt", [$fromdate, $todate])
         ->select("bd.source", DB::raw("COUNT(*) as rooms"),
            DB::raw("SUM(ro.roomrate * DATEDIFF(ro.checkoutdt, ro.checkindt)) as revenue"))
         ->groupBy("bd.source")->orderByDesc("rooms")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function costanalysis(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.costanalysis", compact("propertyid", "fromdate", "todate"));
   }
   public function costanalysisfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("suntran as st")
         ->leftJoin("ledger as l", "st.docid", "=", "l.docid")
         ->leftJoin("subgroup as sg", "l.subcode", "=", "sg.subcode")
         ->leftJoin("acgroup as ag", "sg.group_code", "=", "ag.group_code")
         ->where("st.propertyid", $propertyid)->whereBetween("st.vdate", [$fromdate, $todate])
         ->select("ag.group_name", DB::raw("SUM(st.amtdr) as total_dr"),
            DB::raw("SUM(st.amtcr) as total_cr"))
         ->groupBy("ag.group_name")->orderByDesc("total_dr")->limit(50)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function marketseganalysis(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.marketseganalysis", compact("propertyid", "fromdate", "todate"));
   }
   public function marketseganalysisfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("ro.propertyid", $propertyid)->whereBetween("ro.checkindt", [$fromdate, $todate])
         ->select("bd.source", DB::raw("COUNT(*) as rooms"),
            DB::raw("AVG(ro.roomrate) as avg_rate"),
            DB::raw("SUM(ro.roomrate * DATEDIFF(ro.checkoutdt, ro.checkindt)) as revenue"))
         ->groupBy("bd.source")->orderByDesc("revenue")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   // Cash Card Reports
   public function cashcardcollectsumm(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.cashcardcollectsumm", compact("propertyid", "fromdate", "todate"));
   }
   public function cashcardcollectsummfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("smartcard as sc")
         ->leftJoin("paycharge as pc", "sc.cardno", "=", "pc.subcode")
         ->where("sc.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->select("sc.cardno", "sc.cardstatus", DB::raw("SUM(pc.amtdr) as collected"),
            DB::raw("SUM(pc.amtcr) as utilized"))
         ->groupBy("sc.cardno", "sc.cardstatus")->limit(200)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function cashcardtransrep(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.cashcardtransrep", compact("propertyid", "fromdate", "todate"));
   }
   public function cashcardtransrepfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("paycharge as pc")
         ->where("pc.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->where("pc.paymodedetail", "LIKE", "%Card%")
         ->select("pc.docid", "pc.vdate", "pc.foliono", "pc.amtdr", "pc.amtcr", "pc.paymodedetail", "pc.mnarr")
         ->orderByDesc("pc.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   // Other Reports
   public function epabxcallrep(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.epabxcallrep", compact("propertyid", "fromdate", "todate"));
   }
   public function epabxcallrepfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("epabxlog as e")
         ->where("e.propertyid", $propertyid)->whereBetween("e.calldate", [$fromdate, $todate])
         ->select("e.roomno", "e.calldate", "e.calltime", "e.calltype", "e.duration", "e.callcharge")
         ->orderByDesc("e.calldate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function fbcoststatement(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.fbcoststatement", compact("propertyid", "fromdate", "todate"));
   }
   public function fbcoststatementfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("suntran as st")
         ->leftJoin("ledger as l", "st.docid", "=", "l.docid")
         ->leftJoin("subgroup as sg", "l.subcode", "=", "sg.subcode")
         ->where("st.propertyid", $propertyid)->whereBetween("st.vdate", [$fromdate, $todate])
         ->whereRaw("(sg.name LIKE "%food%" OR sg.name LIKE "%beverage%" OR sg.name LIKE "%fb%")")
         ->select("sg.name", DB::raw("SUM(st.amtdr) as amount"))
         ->groupBy("sg.name")->orderByDesc("amount")->limit(50)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function facilitybillreg(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.facilitybillreg", compact("propertyid", "fromdate", "todate"));
   }
   public function facilitybillregfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("paycharge as pc")
         ->leftJoin("roomocc as ro", "pc.foliono", "=", "ro.foliono")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("pc.propertyid", $propertyid)->whereBetween("pc.vdate", [$fromdate, $todate])
         ->where("pc.paycode", "C")
         ->select(DB::raw("COALESCE(bd.guestname,\"Walk-in\") as guestname"),
            "pc.foliono", "pc.vdate", "pc.docid", "pc.amtdr", "pc.mnarr")
         ->orderByDesc("pc.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function monthlystatisticalreturn(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("Y-m"));
      return view("property.monthlystatisticalreturn", compact("propertyid", "month"));
   }
   public function monthlystatisticalreturnfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $month = $request->input("month", date("Y-m"));
      $fromdate = $month . "-01";
      $todate = date("Y-m-t", strtotime($fromdate));
      $rooms = DB::table("roommast")->where("propertyid", $propertyid)->count();
      $occupied = DB::table("roomocc")->where("propertyid", $propertyid)
         ->whereRaw("checkindt <= ? AND (checkoutdt IS NULL OR checkoutdt >= ?)", [$todate, $fromdate])->count();
      $revenue = DB::table("paycharge")->where("propertyid", $propertyid)
         ->whereBetween("vdate", [$fromdate, $todate])->where("paycode", "C")
         ->sum("amtdr");
      $rows = [["month" => $month, "total_rooms" => $rooms, "occupied" => $occupied,
         "vacant" => $rooms - $occupied, "occupancy_pct" => $rooms > 0 ? round($occupied/$rooms*100,1) : 0,
         "revenue" => $revenue, "adr" => $occupied > 0 ? round($revenue/$occupied,2) : 0,
         "revpar" => $rooms > 0 ? round($revenue/$rooms,2) : 0]];
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function packageforecast(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.packageforecast", compact("propertyid", "fromdate", "todate"));
   }
   public function packageforecastfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->where("ro.propertyid", $propertyid)
         ->whereBetween("ro.checkindt", [$fromdate, $todate])
         ->select("ro.plan", DB::raw("COUNT(*) as rooms"),
            DB::raw("SUM(ro.roomrate * DATEDIFF(ro.checkoutdt, ro.checkindt)) as revenue"))
         ->groupBy("ro.plan")->orderByDesc("rooms")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function paymentdueletter(Request $request) {
      $propertyid = Auth::user()->propertyid;
      return view("property.paymentdueletter", compact("propertyid"));
   }
   public function paymentdueletterdata(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $subcode = $request->input("subcode");
      $rows = DB::table("ledger as l")
         ->leftJoin("subgroup as sg", "l.subcode", "=", "sg.subcode")
         ->where("l.propertyid", $propertyid)->where("l.subcode", $subcode)
         ->select("sg.name", "sg.add1", "sg.phoneo", "l.docid", "l.vdate", "l.amtdr", "l.amtcr", "l.mnarr")
         ->orderBy("l.vdate")->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function refreport(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.refreport", compact("propertyid", "fromdate", "todate"));
   }
   public function refreportfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("suntran as st")
         ->where("st.propertyid", $propertyid)->whereBetween("st.vdate", [$fromdate, $todate])
         ->select("st.docid", "st.vdate", "st.vtype", "st.vno", "st.narr", "st.amt", "st.amtcr", "st.amtdr")
         ->orderBy("st.vdate")->limit(500)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   public function travelagentanalysis(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->get("fromdate", date("Y-m-d"));
      $todate = $request->get("todate", date("Y-m-d"));
      return view("property.travelagentanalysis", compact("propertyid", "fromdate", "todate"));
   }
   public function travelagentanalysisfetch(Request $request) {
      $propertyid = Auth::user()->propertyid;
      $fromdate = $request->input("fromdate"); $todate = $request->input("todate");
      $rows = DB::table("roomocc as ro")
         ->leftJoin("grpbookingdetails as bd", "ro.docid", "=", "bd.docid")
         ->leftJoin("travelagent as ta", "bd.agentid", "=", "ta.agentid")
         ->where("ro.propertyid", $propertyid)->whereBetween("ro.checkindt", [$fromdate, $todate])
         ->select(DB::raw("COALESCE(ta.agentname, bd.agentname, \"Direct\") as agent"),
            DB::raw("COUNT(*) as rooms"),
            DB::raw("SUM(ro.roomrate * DATEDIFF(ro.checkoutdt, ro.checkindt)) as revenue"))
         ->groupBy("agent")->orderByDesc("revenue")->limit(50)->get();
      $comp = DB::table("company")->where("propertyid", $propertyid)->first();
      return response()->json(["success" => true, "data" => $rows, "comp" => $comp]);
   }

   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•
   // MISSING HMS REPORTS â€” Migration Batch
   // â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•â•

   // 1. Arrival/Departure Register â€” Legacy: ArrDepReg
   public function arrdepreg(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.arrdepreg', ['comp' => $comp]);
   }
   public function arrdepregfetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $data = DB::table('roomocc')->where('propertyid', $this->propertyid)
         ->whereBetween('chkindate', [$fd, $td])
         ->select('docid','name','roomno','roomcat','chkindate','depdate','roomrate','nodays','guestprof')
         ->orderBy('chkindate')->get();
      return response()->json(['data' => $data]);
   }

   // 2. Bank Clearance â€” Legacy: Clg
   public function bankclg(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.bankclg', ['comp' => $comp]);
   }
   public function bankclgfetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $data = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td])
         ->whereIn('suncode', ['16103','42103','455103','459103'])
         ->select('docid','vdate','suncode','dispname','amount','vno')
         ->orderBy('vdate')->get();
      return response()->json(['data' => $data]);
   }

   // 3. Bank Not Cleared â€” Legacy: ClgNot
   public function bankclgnot(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.bankclgnot', ['comp' => $comp]);
   }
   public function bankclgnotfetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $data = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td])
         ->whereIn('suncode', ['16103','42103','455103','459103'])
         ->whereNull('sunappdate')
         ->select('docid','vdate','suncode','dispname','amount','vno')
         ->orderBy('vdate')->get();
      return response()->json(['data' => $data]);
   }

   // 4. Debit Ledger â€” Legacy: LedDeb
   public function ledgerdeb(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      $accounts = DB::table('subgroup')->where('propertyid', $this->propertyid)->where('activeyn', 'Y')->orderBy('name')->get();
      return view('property.ledgerdeb', ['comp' => $comp, 'accounts' => $accounts]);
   }
   public function ledgerdebfetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $party = $request->input('partycode', '');
      $q = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td])
         ->where('amount', '>', 0);
      if ($party) $q->where('partycode', $party);
      $data = $q->select('docid','vdate','suncode','dispname','amount','partycode','vno')
         ->orderBy('vdate')->get();
      return response()->json(['data' => $data]);
   }

   // 5. Interest Ledger â€” Legacy: LedInt
   public function ledgerint(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.ledgerint', ['comp' => $comp]);
   }
   public function ledgerintfetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $data = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td])
         ->where('suncode', 'LIKE', '%INT%')
         ->select('docid','vdate','suncode','dispname','amount','partycode','vno')
         ->orderBy('vdate')->get();
      return response()->json(['data' => $data]);
   }

   // 6. Daily Cash Register (Roz Namcha) â€” Legacy: RozNamcha
   public function roznamcha(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.roznamcha', ['comp' => $comp]);
   }
   public function roznamchafetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $data = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->where('vdate', $fd)
         ->whereIn('suncode', ['16103','42103','455103','459103','5103','6103'])
         ->select('docid','vdate','suncode','dispname','amount','vno','restcode')
         ->orderBy('vdate')->get();
      $receipts = $data->where('amount', '>', 0)->sum('amount');
      $payments = $data->where('amount', '<', 0)->sum('amount');
      return response()->json(['data' => $data, 'receipts' => $receipts, 'payments' => $payments, 'balance' => $receipts + $payments]);
   }

   // 7. Goods Receipt Challan â€” Legacy: GRC
   public function grc(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.grc', ['comp' => $comp]);
   }
   public function grcfetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $data = DB::table('stock')->where('propertyid', $this->propertyid)
         ->whereBetween('vdate', [$fd, $td])
         ->where('qtyrec', '>', 0)
         ->select('docid','vdate','item','qtyrec','rate','amount','partycode','restcode')
         ->orderBy('vdate')->get();
      return response()->json(['data' => $data]);
   }

   // 8. GSTR-1 Report â€” Legacy: GSTR1
   public function gstr1report(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.gstr1report', ['comp' => $comp]);
   }
   public function gstr1reportfetch(Request $request) {
      $month = $request->input('month', date('Y-m'));
      $fd = $month . '-01';
      $td = date('Y-m-t', strtotime($fd));
      $data = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td])
         ->whereIn('suncode', ['5103','8103','9103','10103','58103','59103','60103'])
         ->select('suncode','dispname','SUM(amount) as total')
         ->groupBy('suncode','dispname')
         ->get();
      $invoiced = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td])
         ->select(DB::raw("COUNT(DISTINCT docid) as invoices, SUM(amount) as taxable, SUM(CASE WHEN suncode='58103' THEN amount ELSE 0 END) as cgst, SUM(CASE WHEN suncode='59103' THEN amount ELSE 0 END) as sgst"))
         ->first();
      return response()->json(['data' => $data, 'summary' => $invoiced]);
   }

   // 9. PLU File Export â€” Legacy: PLUFile
   public function plufile(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      return view('property.plufile', ['comp' => $comp]);
   }
   public function plufilefetch(Request $request) {
      $data = DB::table('itemmast')->where('Property_ID', $this->propertyid)
         ->join('itemrate', function($j) {
            $j->on('itemrate.Property_ID', '=', 'itemmast.Property_ID')
              ->on('itemrate.ItemCode', '=', 'itemmast.Code')
              ->on('itemrate.RestCode', '=', 'itemmast.RestCode');
         })
         ->select('itemmast.Code','itemmast.Name','itemrate.Rate','itemmast.Unit','itemmast.RestCode','itemrate.AppDate')
         ->orderBy('itemmast.Name')->get();
      return response()->json(['data' => $data]);
   }

   // 10. General Ledger 2 â€” Legacy: Led
   public function generalledger2(Request $request) {
      $comp = Companyreg::where('propertyid', $this->propertyid)->first();
      $accounts = DB::table('subgroup')->where('propertyid', $this->propertyid)->where('activeyn', 'Y')->orderBy('name')->get();
      return view('property.generalledger2', ['comp' => $comp, 'accounts' => $accounts]);
   }
   public function generalledger2fetch(Request $request) {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $party = $request->input('partycode', '');
      $q = DB::table('suntran')->where('propertyid', $this->propertyid)
         ->where('delflag', 'N')
         ->whereBetween('vdate', [$fd, $td]);
      if ($party) $q->where('partycode', $party);
      $data = $q->select('docid','vdate','suncode','dispname','amount','partycode','vno','restcode')
         ->orderBy('vdate')->get();
      $opening = $q->where('vdate', '<', $fd)->sum('amount');
      return response()->json(['data' => $data, 'opening' => $opening]);
   }

   /* =====================================================================
     |  HMS.text MISSING REPORTS BATCH A â€” FRONT OFFICE + RESERVATION       |
     |  Added 2026-08-23 : BookingDetail, DaysForecastRep, GuestBillDetails |
     |  GuestChgJournal(+Log), GuestObservRep, InsHouseCount, GuestInHouse, |
     |  DelBillUnsetBill, ResvAdvRecd(+Arr,+InHouse)                        |
     ===================================================================== */

   // A1. Booking Detail Register â€” Legacy: FrmBookingDetail / GRepFormName "BookingDetail"
   public function bookingdetail(Request $request)
   {
      $p = revokeopen(131214);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.bookingdetail');
   }

   public function bookingdetailfetch(Request $request)
   {
      $p = revokeopen(131214);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $status = $request->input('resstatus', '');
      $q = DB::table('booking')->where('Property_ID', $this->propertyid)->whereBetween('vdate', [$fd, $td]);
      if ($status != '') $q->where('ResStatus', $status);
      if ($request->input('inccancelled', 'N') != 'Y') $q->where('Cancel', 'N');
      $rows = $q->orderBy('BookNo')->get();
      return response()->json(['data' => $rows]);
   }

   // A2. Days Forecast Report â€” Legacy: GRepFormName "DaysForecastRep"
   public function daysforecastrep(Request $request)
   {
      $p = revokeopen(131215);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.daysforecastrep');
   }

   public function daysforecastrepfetch(Request $request)
   {
      $p = revokeopen(131215);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $rooms = [];
      $cur = strtotime($fd);
      $end = strtotime($td);
      while ($cur <= $end && count($rooms) < 120) {
         $d = date('Y-m-d', $cur);
         $occupied = DB::table('roomocc')->where('propertyid', $this->propertyid)
            ->where('chkindate', '<=', $d)->where(function ($w) use ($d) {
               $w->whereNull('chkoutdate')->orWhere('chkoutdate', '>', $d);
            })->count();
         $arrivals = DB::table('roomocc')->where('propertyid', $this->propertyid)
            ->whereDate('chkindate', $d)->count();
         $arrPax = DB::table('roomocc')->where('propertyid', $this->propertyid)
            ->whereDate('chkindate', $d)->selectRaw('IFNULL(SUM(adult+children),0) AS px')->value('px');
         $bookings = DB::table('booking')->where('Property_ID', $this->propertyid)
            ->where('Cancel', 'N')->whereBetween('vdate', [$fd, $d])->count();
         $rooms[] = ['fdate' => $d, 'occupied' => $occupied, 'arrivals' => $arrivals,
                     'arrpax' => (int)$arrPax, 'bookings' => $bookings];
         $cur = strtotime('+1 day', $cur);
      }
      return response()->json(['data' => $rooms]);
   }

   // A3. Guest Bill Details â€” Legacy: GRepFormName "GuestBillDetails"
   public function guestbilldetails(Request $request)
   {
      $p = revokeopen(131216);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      $folios = DB::table('guestfolio')->where('propertyid', $this->propertyid)->orderBy('folio_no')->get();
      return view('property.guestbilldetails', ['folios' => $folios]);
   }

   public function guestbilldetailsfetch(Request $request)
   {
      $p = revokeopen(131216);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $folio = $request->input('foliono', '');
      $rows = DB::table('paycharge AS PC')
         ->leftJoin('guestfolio AS GF', function ($j) {
            $j->on('GF.propertyid', '=', 'PC.propertyid')->on('GF.docid', '=', 'PC.docid');
         })
         ->where('PC.propertyid', $this->propertyid)->whereBetween('PC.vdate', [$fd, $td])
         ->when($folio !== '', fn ($qq) => $qq->where('PC.foliono', $folio))
         ->orderBy('PC.foliono')->orderBy('PC.vdate')->orderBy('PC.sno')
         ->select('PC.foliono','PC.vdate','PC.vtype','PC.paycode','PC.paytype',
                  'PC.amtcr','PC.amtdr','PC.roomno','PC.u_name','GF.name')
         ->get();
      $bal = []; $data = [];
      foreach ($rows as $r) {
         $f = $r->foliono;
         $bal[$f] = ($bal[$f] ?? 0) + $r->amtdr - $r->amtcr;
         $data[] = ['foliono'=>$f,'name'=>$r->name,'vdate'=>$r->vdate,'vtype'=>$r->vtype,
                    'particulars'=>trim($r->paytype.' '.$r->paycode),'dr'=>$r->amtdr,
                    'cr'=>$r->amtcr,'balance'=>round($bal[$f],2),'user'=>$r->u_name];
      }
      return response()->json(['data' => $data]);
   }

   // A4. Guest Charge Journal â€” Legacy: GRepFormName "GuestChgJournal"
   public function guestchgjournal(Request $request)
   {
      $p = revokeopen(131217);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.guestchgjournal');
   }

   public function guestchgjournalfetch(Request $request)
   {
      $p = revokeopen(131217);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $rows = DB::table('paycharge')->where('propertyid', $this->propertyid)
         ->whereIn('vtype', ['CHG', 'RCP', 'ADV'])
         ->whereBetween('vdate', [$fd, $td])
         ->groupBy('vdate', 'paycode', 'paytype')
         ->orderBy('vdate')->orderBy('paycode')
         ->selectRaw("vdate, paycode, paytype, COUNT(*) docs, IFNULL(SUM(amtdr),0) debit, IFNULL(SUM(amtcr),0) credit")
         ->get();
      return response()->json(['data' => $rows]);
   }

   // A5. Guest Charge Journal Log (audit view) â€” Legacy: "GuestChgJournalLog"
   public function guestchgjournallog(Request $request)
   {
      $p = revokeopen(131218);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.guestchgjournallog');
   }

   public function guestchgjournallogfetch(Request $request)
   {
      $p = revokeopen(131218);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $rows = DB::table('paycharge')->where('propertyid', $this->propertyid)
         ->whereIn('vtype', ['CHG', 'RCP', 'ADV'])
         ->whereBetween('u_entdt', [$fd . ' 00:00:00', $td . ' 23:59:59'])
         ->orderBy('u_entdt')
         ->select('u_entdt','vdate','docid','vtype','paycode','paytype','amtdr','amtcr','roomno','u_name')
         ->limit(5000)->get();
      return response()->json(['data' => $rows]);
   }

   // A6. Guest Observation Report â€” Legacy: "GuestObservRep"
   public function guestobservrep(Request $request)
   {
      $p = revokeopen(131219);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.guestobservrep');
   }

   public function guestobservrepfetch(Request $request)
   {
      $p = revokeopen(131219);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $rows = DB::table('roomocc AS RO')
         ->leftJoin('guestprof AS GP', function ($j) {
            $j->on('GP.propertyid', '=', 'RO.propertyid')->on('GP.docid', '=', 'RO.docid');
         })
         ->where('RO.propertyid', $this->propertyid)
         ->whereBetween('RO.chkindate', [$fd, $td])
         ->where(function ($w) {
            $w->whereNotNull('RO.reasonrchange')->where('RO.reasonrchange', '!=', '')
              ->orWhere('RO.extrabed', '>', 0)
              ->orWhere('RO.rodisc', '>', 0)->orWhere('RO.rsdisc', '>', 0)
              ->orWhere('GP.guest_status', 'VIP');
         })
         ->orderBy('RO.roomno')
         ->select('RO.roomno','RO.name','RO.chkindate','RO.depdate','RO.roomrate',
                  'RO.reasonrchange','RO.extrabed','RO.rodisc','RO.rsdisc','GP.mobile_no','GP.guest_status')
         ->get();
      return response()->json(['data' => $rows]);
   }

   // A7. Instant House Count â€” Legacy: "InsHouseCount"
   public function inhousecount(Request $request)
   {
      $p = revokeopen(131220);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.inhousecount');
   }

   public function inhousecountfetch(Request $request)
   {
      $p = revokeopen(131220);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $pid = $this->propertyid;
       $totalRooms = DB::table('room_mast')->where('propertyid', $pid)->count();
       $occupiedQ = DB::table('roomocc')->where('propertyid', $pid)
          ->whereNull('chkoutdate');
      $occupied = (clone $occupiedQ)->distinct()->count('roomno');
      $pax = (clone $occupiedQ)->selectRaw('IFNULL(SUM(adult+children),0) AS px')->value('px');
      $male = (clone $occupiedQ)->sum('adult');
      $blocked = DB::table('roomblockout')->where('propertyid', $pid)->where('block', 'B')
         ->where(function ($w) {
            $w->whereNull('todate')->orWhere('todate', '>=', date('Y-m-d'));
         })->count();
      $vacant = max($totalRooms - $occupied - $blocked, 0);
      $arrToday = DB::table('roomocc')->where('propertyid', $pid)->whereDate('chkindate', date('Y-m-d'))->count();
      $depToday = DB::table('roomocc')->where('propertyid', $pid)->whereDate('depdate', date('Y-m-d'))->whereNull('chkoutdate')->count();
      return response()->json(['data' => [[
         'total_rooms' => $totalRooms, 'occupied' => $occupied, 'vacant' => $vacant,
         'blocked' => $blocked, 'pax' => (int)$pax, 'adults' => (int)$male,
         'expected_arrivals' => $arrToday, 'expected_departures' => $depToday,
      ]]]);
   }

   // A8. Guest In House List â€” Legacy: "GuestInHouse"
   public function guestinhousereport(Request $request)
   {
      $p = revokeopen(131221);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.guestinhousereport');
   }

   public function guestinhousereportfetch(Request $request)
   {
      $p = revokeopen(131221);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $pid = $this->propertyid;
      $rows = DB::table('roomocc AS RO')
         ->leftJoin('guestprof AS GP', function ($j) {
            $j->on('GP.propertyid', '=', 'RO.propertyid')->on('GP.docid', '=', 'RO.docid');
         })
         ->leftJoin(DB::raw("(SELECT propertyid, foliono,
                IFNULL(SUM(amtdr),0)-IFNULL(SUM(amtcr),0) bal
                FROM paycharge WHERE propertyid = $pid GROUP BY foliono) PB"),
               function ($j) { $j->on('PB.foliono', '=', 'RO.folioNo'); })
          ->where('RO.propertyid', $pid)
          ->whereNull('RO.chkoutdate')
          ->when($request->filled('fromdate'), fn ($q) => $q->where('RO.chkindate', '>=', $request->fromdate))
         ->orderBy('RO.roomno')
         ->select('RO.roomno','RO.name','GP.city','GP.mobile_no','RO.chkindate','RO.depdate',
                  'RO.adult','RO.children','RO.roomrate','RO.roomcat',
                  DB::raw('IFNULL(PB.bal,0) AS balance'))
         ->get();
      return response()->json(['data' => $rows]);
   }

   // A9. Deleted / Unsettled Bills â€” Legacy: "DelBillUnsetBill"
   public function delbillunsetbill(Request $request)
   {
      $p = revokeopen(131222);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.delbillunsetbill');
   }

   public function delbillunsetbillfetch(Request $request)
   {
      $p = revokeopen(131222);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $pid = $this->propertyid;
      $unsettled = DB::table(DB::raw("(SELECT foliono, docid, MAX(vdate) vd,
             IFNULL(SUM(amtdr),0)-IFNULL(SUM(amtcr),0) bal
             FROM paycharge WHERE propertyid = $pid AND foliono > 0
             GROUP BY foliono, docid HAVING bal <> 0) T"))
         ->whereBetween('vd', [$fd, $td])->orderBy('vd')->get();
      $deleted = [];
      try {
         $deleted = DB::table('paychargeh')->where('propertyid', $pid)
            ->whereBetween('vdate', [$fd, $td])->orderBy('u_entdt', 'desc')
            ->limit(2000)->get()->all();
      } catch (\Exception $e) { $deleted = []; }
      return response()->json(['unsettled' => $unsettled, 'deleted' => $deleted]);
   }

   // A10-A12. Reservation Advance Received â€” Legacy: "ResvAdvRecd" (+Arr / +InHouse)
   public function resvadvrecdcommon(Request $request, $mode)
   {
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $q = DB::table('paycharge AS PC')
         ->leftJoin('booking AS BK', function ($j) {
            $j->on('BK.Property_ID', '=', 'PC.propertyid')->on('BK.DocId', '=', 'PC.docid');
         })
         ->where('PC.propertyid', $this->propertyid)->where('PC.vtype', 'ADV')
         ->whereBetween('PC.vdate', [$fd, $td]);
      if ($mode == 'arr') {
         $q->whereIn('PC.docid', function ($sq) use ($fd, $td) {
            $sq->select('docid')->from('roomocc')
               ->whereColumn('roomocc.docid', 'PC.docid')
               ->whereBetween('chkindate', [$fd, $td]);
         });
       } elseif ($mode == 'inh') {
          $q->whereIn('PC.docid', function ($sq) {
             $sq->select('docid')->from('roomocc')
                ->whereColumn('roomocc.docid', 'PC.docid')
                ->whereNull('chkoutdate');
          });
       }
      return $q->orderBy('PC.vdate')
         ->select('PC.vdate','PC.docid','BK.BookNo','BK.GuestName',
                  DB::raw('PC.amtdr AS amount'),'PC.modeset','PC.u_name')
         ->get();
   }

   public function resvadvrecd(Request $request)
   {
      $p = revokeopen(131223);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.resvadvrecd');
   }

   public function resvadvrecdfetch(Request $request)
   {
      $p = revokeopen(131223);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      return response()->json(['data' => $this->resvadvrecdcommon($request, 'all')]);
   }

   public function resvadvrecdarr(Request $request)
   {
      $p = revokeopen(131224);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.resvadvrecdarr');
   }

   public function resvadvrecdarrfetch(Request $request)
   {
      $p = revokeopen(131224);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      return response()->json(['data' => $this->resvadvrecdcommon($request, 'arr')]);
   }

   public function resvadvrecdinhouse(Request $request)
   {
      $p = revokeopen(131225);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.resvadvrecdinhouse');
   }

   public function resvadvrecdinhousefetch(Request $request)
   {
      $p = revokeopen(131225);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      return response()->json(['data' => $this->resvadvrecdcommon($request, 'inh')]);
   }

   // ==================== BATCH B: ACCOUNTS (HMS.text missing reports) ====================
   // Legacy refs: "BankReg", "LedCred", "CONTROLLED", "PartyWiseOutStanding", "PmtByCashier"
   // Data conventions: suntran (suncode/partycode/amount/sunappdate), subgroup.nature,
   // ledger amtdr/amtcr per subcode, paycharge receipts by u_name.

   // B1. Bank Register - cheque/txn-wise bank book w/ clearance status
   public function bankreg(Request $request)
   {
      $p = revokeopen(131226);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      $banks = DB::table('subgroup')->where('propertyid', $this->propertyid)
         ->where('nature', 'Bank')->orderBy('name')
         ->select('sub_code', 'name')->get();
      return view('property.bankreg', ['banks' => $banks]);
   }

   public function bankregfetch(Request $request)
   {
      $p = revokeopen(131226);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $status = $request->input('clrstatus', '');       // '' | C | P
      $bank = $request->input('bankcode', '');
      $bankCodes = DB::table('subgroup')->where('propertyid', $this->propertyid)
         ->where('nature', 'Bank')->pluck('sub_code')->all();
      if (empty($bankCodes)) { return response()->json(['data' => [], 'total' => 0]); }
      $q = DB::table('suntran AS ST')
         ->leftJoin('subgroup AS SG', function ($j) {
            $j->on('SG.sub_code', '=', 'ST.suncode')->on('SG.propertyid', '=', 'ST.propertyid');
         })
         ->where('ST.propertyid', $this->propertyid)
         ->where('ST.delflag', 'N')
         ->whereIn('ST.suncode', $bankCodes)
         ->whereBetween('ST.vdate', [$fd, $td]);
      if ($bank) { $q->where('ST.suncode', $bank); }
      if ($status == 'C') { $q->whereNotNull('ST.sunappdate'); }
      if ($status == 'P') { $q->whereNull('ST.sunappdate'); }
      $rows = $q->orderBy('ST.vdate')->orderBy('ST.sn')
         ->select('ST.docid','ST.vdate','ST.vno','ST.vtype','ST.suncode',
                  DB::raw("IFNULL(SG.name,'') AS bankname"),
                  'ST.dispname','ST.amount','ST.sunappdate','ST.u_name',
                  DB::raw("CASE WHEN ST.sunappdate IS NULL THEN 'Pending' ELSE 'Cleared' END AS clrstatus"))
         ->get();
      $total = $rows->sum('amount');
      return response()->json(['data' => $rows, 'total' => $total]);
   }

   // B2. Ledger Creditors - party transactions (creditors/debtors view)
   public function ledgercred(Request $request)
   {
      $p = revokeopen(131227);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      $accounts = DB::table('subgroup')->where('propertyid', $this->propertyid)
         ->whereIn('nature', ['Supplier', 'Customer'])->where('activeyn', 'Y')
         ->orderBy('name')->select('sub_code', 'name', 'nature')->get();
      return view('property.ledgercred', ['accounts' => $accounts]);
   }

   public function ledgercredfetch(Request $request)
   {
      $p = revokeopen(131227);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $ptype = $request->input('partytype', 'Supplier');  // Supplier | Customer
      $party = $request->input('partycode', '');
      $parties = DB::table('subgroup')->where('propertyid', $this->propertyid)
         ->where('nature', $ptype)->pluck('sub_code')->all();
      if (empty($parties)) { return response()->json(['data' => [], 'total' => 0]); }
      $q = DB::table('ledger AS L')
         ->leftJoin('subgroup AS PG', function ($j) {
            $j->on('PG.sub_code', '=', 'L.subcode')->on('PG.propertyid', '=', 'L.propertyid');
         })
         ->where('L.propertyid', $this->propertyid)
         ->whereIn('L.subcode', $parties)
         ->whereBetween('L.vdate', [$fd, $td]);
      if ($party) { $q->where('L.subcode', $party); }
      $rows = $q->orderBy('L.vdate')->orderBy('L.sn')
         ->select('L.docid','L.vdate','L.vno','L.vtype','L.subcode AS partycode',
                  DB::raw("IFNULL(PG.name,'') AS partyname"),
                  'L.narration','L.chqno','L.amtdr','L.amtcr','L.u_name',
                  DB::raw('(IFNULL(L.amtdr,0)-IFNULL(L.amtcr,0)) AS net'))
         ->get();
      $total = $rows->sum('net');
      return response()->json(['data' => $rows, 'total' => $total]);
   }

   // B3. Controlled Accounts - system-controlled a/c closing balances by nature
   public function controlledaccounts(Request $request)
   {
      $p = revokeopen(131228);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.controlledaccounts');
   }

   public function controlledaccountsfetch(Request $request)
   {
      $p = revokeopen(131228);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $nature = $request->input('nature', '');
      $controlled = ['Cash','Bank','TDS','Expenditure','Sale','Purchase','Others'];
      $q = DB::table('subgroup AS SG')
         ->leftJoin('ledger AS L', function ($j) use ($fd, $td) {
            $j->on('L.subcode', '=', 'SG.sub_code')->on('L.propertyid', '=', 'SG.propertyid')
              ->whereBetween('L.vdate', [$fd, $td]);
         })
         ->where('SG.propertyid', $this->propertyid)
         ->whereIn('SG.nature', $controlled);
      if ($nature) { $q->where('SG.nature', $nature); }
      $rows = $q->groupBy('SG.sub_code', 'SG.name', 'SG.nature')
         ->orderBy('SG.nature')->orderBy('SG.name')
         ->select('SG.sub_code','SG.name','SG.nature',
                  DB::raw('IFNULL(SUM(L.amtdr),0) AS dr'),
                  DB::raw('IFNULL(SUM(L.amtcr),0) AS cr'),
                  DB::raw('IFNULL(SUM(L.amtdr),0)-IFNULL(SUM(L.amtcr),0) AS balance'))
         ->havingRaw('dr <> 0 OR cr <> 0')
         ->get();
      $totDr = $rows->sum('dr'); $totCr = $rows->sum('cr');
      return response()->json(['data' => $rows, 'totdr' => $totDr, 'totcr' => $totCr]);
   }

   // B4. Party-wise Outstanding - receivable/payable per party
   public function partywiseoutstanding(Request $request)
   {
      $p = revokeopen(131229);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.partywiseoutstanding');
   }

   public function partywiseoutstandingfetch(Request $request)
   {
      $p = revokeopen(131229);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $asof = $request->input('todate', date('Y-m-d'));
      $balType = $request->input('baltype', '');          // '' | R | P
      $ntype = $request->input('nature', '');             // '' | Customer | Supplier
      $q = DB::table('subgroup AS SG')
         ->leftJoin('ledger AS L', function ($j) use ($asof) {
            $j->on('L.subcode', '=', 'SG.sub_code')->on('L.propertyid', '=', 'SG.propertyid')
              ->where('L.vdate', '<=', $asof);
         })
         ->where('SG.propertyid', $this->propertyid)
         ->whereIn('SG.nature', ['Customer', 'Supplier']);
      if ($ntype) { $q->where('SG.nature', $ntype); }
      $q->groupBy('SG.sub_code', 'SG.name', 'SG.nature')
        ->orderByDesc(DB::raw('ABS(IFNULL(SUM(L.amtdr),0)-IFNULL(SUM(L.amtcr),0))'))
        ->select('SG.sub_code','SG.name','SG.nature',
                 DB::raw('IFNULL(SUM(L.amtdr),0) AS dr'),
                 DB::raw('IFNULL(SUM(L.amtcr),0) AS cr'),
                 DB::raw('IFNULL(SUM(L.amtdr),0)-IFNULL(SUM(L.amtcr),0) AS outstanding'));
      if ($balType == 'R') { $q->havingRaw('outstanding > 0'); }
      elseif ($balType == 'P') { $q->havingRaw('outstanding < 0'); }
      else { $q->havingRaw('outstanding <> 0'); }
      $rows = $q->get();
      $totR = $rows->where('outstanding', '>', 0)->sum('outstanding');
      $totP = abs($rows->where('outstanding', '<', 0)->sum('outstanding'));
      return response()->json(['data' => $rows, 'totrecvd' => $totR, 'totpayble' => $totP]);
   }

   // B5. Payments by Cashier - receipt register grouped cashier/mode/day
   public function pmtbycashier(Request $request)
   {
      $p = revokeopen(131230);
      if (is_null($p) || $p->view == 0) { return redirect()->back()->with('error', 'You have no permission'); }
      return view('property.pmtbycashier');
   }

   public function pmtbycashierfetch(Request $request)
   {
      $p = revokeopen(131230);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }
      $fd = $request->input('fromdate', date('Y-m-d'));
      $td = $request->input('todate', date('Y-m-d'));
      $groupby = $request->input('groupby', 'cashier');   // cashier | mode | date
      $map = [
         'cashier' => ['u_name', 'Cashier'],
         'mode'    => ['paytype', 'Mode'],
         'date'    => ['vdate', 'Date'],
      ];
      if (!isset($map[$groupby])) { $groupby = 'cashier'; }
      $col = $map[$groupby][0];
      $rows = DB::table('paycharge AS PC')
         ->where('PC.propertyid', $this->propertyid)
         ->where('PC.amtcr', '>', 0)
         ->whereBetween('PC.vdate', [$fd, $td])
         ->groupBy(DB::raw('PC.' . $col))
         ->orderByDesc('totamt')
         ->select('PC.' . $col . ' AS grpval',
                  DB::raw("MAX(PC.paytype) AS paytype"),
                  DB::raw("MAX(PC.modeset) AS modeset"),
                  DB::raw('COUNT(*) AS docs'),
                  DB::raw('SUM(PC.amtcr) AS totamt'))
         ->get();
      $total = $rows->sum('totamt');
      return response()->json(['data' => $rows, 'total' => $total, 'groupby' => $groupby]);
   }

   // ═══════════════════════════════════════════════════════════════════════════
   // SALES DAY BOOK — HMS.text: "SalesDayBook"
   // Shows day-wise POS sales with tax breakdown
   // ═══════════════════════════════════════════════════════════════════════════

   public function salesdaybook(Request $request)
   {
      $fromdate = $this->ncurdate;
      $todate = $this->ncurdate;
      return view('property.salesdaybook', compact('fromdate', 'todate'));
   }

   public function salesdaybookfetch(Request $request)
   {
      $p = revokeopen(131225);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }

      $fromdate = $request->input('fromdate', $this->ncurdate);
      $todate = $request->input('todate', $this->ncurdate);

      $rows = DB::table('sale1')
         ->join('sale2 as S2', 'S2.docid', '=', 'sale1.docid')
         ->leftJoin('rest_mast as RM', 'RM.restcode', '=', 'sale1.restcode')
         ->leftJoin('room_mast as room', 'room.roomno', '=', 'sale1.roomno')
         ->where('sale1.propertyid', $this->propertyid)
         ->whereBetween('sale1.saledate', [$fromdate, $todate])
         ->select(
            'sale1.saledate',
            'sale1.docid',
            'sale1.billno',
            'RM.restname as outlet',
            'sale1.roomno',
            'sale1.guestname',
            'sale1.pMode',
            DB::raw('SUM(S2.amt) AS grossamt'),
            DB::raw('SUM(S2.taxamt) AS taxamt'),
            DB::raw('SUM(S2.discamt) AS discamt'),
            DB::raw('SUM(S2.amt + S2.taxamt - S2.discamt) AS netamt'),
            'sale1.u_name as user'
         )
         ->groupBy('sale1.docid', 'sale1.saledate', 'sale1.billno', 'sale1.restcode',
                   'sale1.roomno', 'sale1.guestname', 'sale1.pMode', 'RM.restname', 'sale1.u_name')
         ->orderBy('sale1.saledate')
         ->orderBy('sale1.billno')
         ->get();

      $total = $rows->sum('netamt');
      return response()->json(['data' => $rows, 'total' => $total]);
   }

   // ═══════════════════════════════════════════════════════════════════════════
   // STOCK LEDGER — HMS.text: "StockLedger"
   // Shows item-wise opening, receipt, issue, closing
   // ═══════════════════════════════════════════════════════════════════════════

   public function stockledger(Request $request)
   {
      $fromdate = $this->ncurdate;
      $todate = $this->ncurdate;
      return view('property.stockledger', compact('fromdate', 'todate'));
   }

   public function stockledgerfetch(Request $request)
   {
      $p = revokeopen(131225);
      if (is_null($p) || $p->view == 0) { return response()->json(['error' => 'No permission']); }

      $fromdate = $request->input('fromdate', $this->ncurdate);
      $todate = $request->input('todate', $this->ncurdate);
      $itemcode = $request->input('itemcode', '');

      // Opening stock from suntran before fromdate
      $opening = DB::table('suntran')
         ->leftJoin('itemmast as IM', 'IM.itemcode', '=', 'suntran.itemcode')
         ->where('suntran.propertyid', $this->propertyid)
         ->where('suntran.sundate', '<', $fromdate)
         ->when($itemcode, function ($q) use ($itemcode) { $q->where('suntran.itemcode', $itemcode); })
         ->select(
            'suntran.itemcode',
            DB::raw('MAX(IM.itemname) AS itemname'),
            DB::raw('MAX(IM.unit) AS unit'),
            DB::raw("SUM(CASE WHEN suntran.suntypes = 'R' THEN suntran.qty ELSE 0 END) AS openingreceipt"),
            DB::raw("SUM(CASE WHEN suntran.suntypes = 'I' THEN suntran.qty ELSE 0 END) AS openingissue")
         )
         ->groupBy('suntran.itemcode')
         ->get();

      // Transactions in period
      $transactions = DB::table('suntran')
         ->leftJoin('itemmast as IM', 'IM.itemcode', '=', 'suntran.itemcode')
         ->where('suntran.propertyid', $this->propertyid)
         ->whereBetween('suntran.sundate', [$fromdate, $todate])
         ->when($itemcode, function ($q) use ($itemcode) { $q->where('suntran.itemcode', $itemcode); })
         ->select(
            'suntran.itemcode',
            DB::raw('MAX(IM.itemname) AS itemname'),
            DB::raw('MAX(IM.unit) AS unit'),
            'suntran.sundate',
            'suntran.vtype',
            'suntran.vno',
            'suntran.suntypes',
            DB::raw('SUM(suntran.qty) AS qty'),
            DB::raw('SUM(suntran.rate * suntran.qty) AS amount')
         )
         ->groupBy('suntran.itemcode', 'suntran.sundate', 'suntran.vtype', 'suntran.vno', 'suntran.suntypes')
         ->orderBy('suntran.itemcode')
         ->orderBy('suntran.sundate')
         ->get();

      // Group by item for summary
      $items = $transactions->groupBy('itemcode')->map(function ($rows, $code) {
         $receipt = $rows->where('suntypes', 'R')->sum('qty');
         $issue = $rows->where('suntypes', 'I')->sum('qty');
         return [
            'itemcode' => $code,
            'itemname' => $rows->first()->itemname ?? '',
            'unit' => $rows->first()->unit ?? '',
            'receipt' => $receipt,
            'issue' => $issue,
            'balance' => $receipt - $issue,
         ];
      })->values();

      return response()->json([
         'opening' => $opening,
         'transactions' => $transactions,
         'items' => $items,
      ]);
   }

}
