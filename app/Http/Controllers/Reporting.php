<?php

namespace App\Http\Controllers;

use App\Helpers\DateHelper;
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
   # Warning: Abandon hope, all who enter here. 😱

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
      if ($this->revokeopen(141212)->view == 0) {
         return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
      }

      $fromdate = $this->ncurdate;
      $bsource = DB::table('busssource')
         ->where('propertyid', $this->propertyid)
         ->orderBy('name', 'ASC')->get();
      $todate = date('Y-m-d', strtotime('-1 month', strtotime($this->ncurdate)));
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

      // dd($chkbilltrue);

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

      // fromSub / joinSub let Laravel track bindings itself — no toSql()/mergeBindings surgery
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

      // Wrap again as a sub-builder for filtering/count/paginate — bindings still auto-tracked
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

      // whitelist order column + direction — never trust raw request input in ORDER BY
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
      // + 1 Paycharge query per voucher, twice — today and yesterday).
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
         'todate',
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
    * Advance / Folio Reconciliation Report (read-only diagnostic, mission §10).
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
    * Safe Restore / Re-post of a missing folio advance (mission §10).
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
         return response()->json(['status' => false, 'message' => 'Reservation is not checked-in — no folio to restore to']);
      }
      $folioDocid = $folio->docid;
      $folioNo = $folio->folio_no;

      // Refuse if the folio is already settled / guest checked out
      $checkedOut = DB::table('roomocc')->where('propertyid', $pid)->where('docid', $folioDocid)->where('type', 'O')->exists();
      $settled = DB::table('paycharge')->where('propertyid', $pid)->where('folionodocid', $folioDocid)->whereNotNull('settledate')->exists();
      if ($checkedOut || $settled) {
         return response()->json(['status' => false, 'message' => 'Folio is settled / guest checked-out — restore not allowed']);
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
         return response()->json(['status' => false, 'message' => 'No missing advance to restore (already balanced) — nothing to re-post']);
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
            return response()->json(['status' => false, 'message' => 'Voucher docid collision — restore aborted (no duplicate created)']);
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
         DB::table('paychargelog')->insert([
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
            'remarks' => 'ADVANCE RESTORED via reconciliation by ' . (Auth::user()->u_name ?? Auth::user()->name) . ' — original reservation ' . ($booking->BookNo ?? $docid),
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
    * Room Management reconciliation — read-only diagnostics.
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

         // 6. Blocked (OOO/Maint) rooms that are simultaneously occupied — must never happen
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
      // page (the per-row Depart lookup was an N+1 — 1 query per payment row).
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
      //     print_r( $request->all());
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
      // NOTE: the original had NO propertyid filter on these lookups — preserved.
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
            // Check if rate exists
            // $existingRate = DB::table('itemrate')
            //    ->where('RestCode', $restCode)
            //    ->where('ItemCode', $item['itemcode'])
            //    ->first();

            // if ($existingRate) {
            // Update existing rate
            // $updated = DB::table('itemrate')
            //    ->where('RestCode', $restCode)
            //    ->where('ItemCode', $item['itemcode'])
            //    ->update([
            //       'Rate' => $item['rate'],
            //       'AppDate' => $item['app_date']
            //    ]);

            // if ($updated) {
            //    $updatedCount++;
            // }
            //} else {
            // Insert new rate
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
            // }
         }

         $message = '';
         // if ($updatedCount > 0) {
         //    $message .= $updatedCount . ' rate(s) updated';
         // }
         if ($insertedCount > 0) {
            $message .= ($updatedCount > 0 ? ' and ' : '') . $insertedCount . ' rate(s) inserted';
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

   // ─── Reward Point Report ──────────────────────────────────────────────────

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
            // Party wise — filter by mobile, no date filter, order by date DESC
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

   // ─── Occupancy Forecast Report ──────────────────────────────────────────────
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

   // ─── Occupancy Forecast Print (DomPDF) ─────────────────────────────────────
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

   // ─── Occupancy Forecast Excel Export ───────────────────────────────────────
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
}
