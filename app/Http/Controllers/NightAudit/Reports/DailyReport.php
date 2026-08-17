<?php

namespace App\Http\Controllers\NightAudit\Reports;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\DateHelper;
use App\Services\DailyReportSnapshotService;
use App\Models\Bookings;
use Illuminate\Support\Facades\DB;
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
use App\Models\Sundrytype;
use Barryvdh\DomPDF\Facade\Pdf;

class DailyReport extends Controller
{
    protected $username;
    protected $email;
    protected $propertyid;
    protected $currenttime;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->email = Auth::user()->email;
            $this->propertyid = Auth::user()->propertyid;
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');
            return $next($request);
        });
    }

    public function dailyreportfetch(Request $request, DailyReportSnapshotService $dailyReportSnapshotService)
    {
        $fordate = $request->fordate;
        $ranges = DateHelper::calculateDateRanges($fordate);

        $revmast = Revmast::select('rev_code', 'Name', 'field_type', 'Nature')
            ->where('propertyid', $this->propertyid)
            ->where('Flag_Type', 'FOM')
            ->where('field_type', 'C')
            ->orderBy('rev_code')
            ->get();

        // return $revmast;

        $departments = Depart::select('dcode', 'name', 'short_name')
            ->where('propertyid', $this->propertyid)
            ->whereIn('nature', ['Room Service', 'Outlet'])
            ->get();

        $categories = ItemCatMast::select('CatType AS NAME')
            ->where('propertyid', $this->propertyid)
            ->whereNotNull('RevCode')
            ->where('RevCode', '<>', '')
            ->whereNotNull('CatType')
            ->where('CatType', '<>', '')
            ->whereNot('RestCode', "BANQ$this->propertyid")
            ->distinct()
            ->orderBy('CatType')
            ->get();

        $discountByDepart = [];
        $discountaccounts = Sundrytype::select(
            'sundrytype.revcode',
            'depart.name as departname',
            'depart.dcode',
            'depart.short_name'
        )
            ->join('depart', 'depart.dcode', '=', 'sundrytype.vtype')
            ->where('sundrytype.propertyid', $this->propertyid)
            ->where('sundrytype.nature', 'Discount')
            ->where('sundrytype.revcode', '!=', '')
            ->get();

        if ($discountaccounts->isNotEmpty()) {
            $discountToday = Paycharge::selectRaw('paycode, SUM(amtdr) as total')
                ->where('propertyid', $this->propertyid)
                ->where('vtype', 'PPOS')
                ->whereDate('vdate', $fordate)
                ->groupBy('paycode')
                ->pluck('total', 'paycode');

            $discountMtd = Paycharge::selectRaw('paycode, SUM(amtdr) as total')
                ->where('propertyid', $this->propertyid)
                ->where('vtype', 'PPOS')
                ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
                ->groupBy('paycode')
                ->pluck('total', 'paycode');

            $discountYtd = Paycharge::selectRaw('paycode, SUM(amtdr) as total')
                ->where('propertyid', $this->propertyid)
                ->where('vtype', 'PPOS')
                ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
                ->groupBy('paycode')
                ->pluck('total', 'paycode');

            foreach ($discountaccounts as $row) {
                $dcode = (string) ($row->dcode ?? '');
                if ($dcode === '') {
                    continue;
                }

                if (!isset($discountByDepart[$dcode])) {
                    $discountByDepart[$dcode] = [
                        'departname' => $row->departname,
                        'short_name' => $row->short_name,
                        'Today' => 0.0,
                        'MTD' => 0.0,
                        'YTD' => 0.0,
                    ];
                }

                $revcode = (string) ($row->revcode ?? '');
                $discountByDepart[$dcode]['Today'] += (float) ($discountToday[$revcode] ?? 0);
                $discountByDepart[$dcode]['MTD'] += (float) ($discountMtd[$revcode] ?? 0);
                $discountByDepart[$dcode]['YTD'] += (float) ($discountYtd[$revcode] ?? 0);
            }

            foreach ($discountByDepart as $dcode => $vals) {
                $discountByDepart[$dcode]['Today'] = (float) round($vals['Today'], 2);
                $discountByDepart[$dcode]['MTD'] = (float) round($vals['MTD'], 2);
                $discountByDepart[$dcode]['YTD'] = (float) round($vals['YTD'], 2);
            }
        }

        $reportData = [];

        // Batch the per-revcode FO aggregates (was 4 queries per revmast row).
        $foPaycodes = $revmast->pluck('rev_code')->all();
        $foToday = Paycharge::selectRaw('paycode, SUM(amtdr) - SUM(amtcr) AS Today')
            ->where('restcode', 'FOM' . $this->propertyid)
            ->whereIn('paycode', $foPaycodes)
            ->where('paycode', '!=', 'TOUT' . $this->propertyid)
            ->where('propertyid', $this->propertyid)
            ->where('vdate', $fordate)
            ->groupBy('paycode')
            ->pluck('Today', 'paycode');

        $foMtd = Paycharge::selectRaw('paycode, SUM(amtdr) - SUM(amtcr) AS MTD')
            ->where('restcode', 'FOM' . $this->propertyid)
            ->whereIn('paycode', $foPaycodes)
            ->where('paycode', '!=', 'TOUT' . $this->propertyid)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->groupBy('paycode')
            ->pluck('MTD', 'paycode');

        $foFtd = Paycharge::selectRaw('paycode, SUM(amtdr) - SUM(amtcr) AS FTD')
            ->where('restcode', 'FOM' . $this->propertyid)
            ->whereIn('paycode', $foPaycodes)
            ->where('paycode', '!=', 'TOUT' . $this->propertyid)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->groupBy('paycode')
            ->pluck('FTD', 'paycode');

        $foYtd = Paycharge::selectRaw('paycode, SUM(amtdr) - SUM(amtcr) AS YTD')
            ->where('restcode', 'FOM' . $this->propertyid)
            ->whereIn('paycode', $foPaycodes)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->groupBy('paycode')
            ->pluck('YTD', 'paycode');

        foreach ($revmast as $row) {
            $reportData[] = [
                'category' => 'Front Office',
                'rev_code' => $row->rev_code,
                'Name' => $row->Name,
                'field_type' => $row->field_type,
                'Nature' => $row->Nature,
                'Today' => $foToday[$row->rev_code] ?? null,
                'MTD' => $foMtd[$row->rev_code] ?? null,
                'FTD' => $foFtd[$row->rev_code] ?? null,
                'YTD' => $foYtd[$row->rev_code] ?? null
            ];
        }

        // return $reportData;

        // Batch the per-cell Sales Summary aggregates (was 3 queries per
        // department x category cell). Each paycode maps to one CatType.
        $catPaycodes = ItemCatMast::select('CatType', 'RevCode')
            ->where('propertyid', $this->propertyid)
            ->whereNotNull('RevCode')
            ->where('RevCode', '<>', '')
            ->whereNotNull('CatType')
            ->where('CatType', '<>', '')
            ->get()
            ->groupBy('CatType')
            ->map(function ($rows) {
                return $rows->pluck('RevCode')->unique()->values()->all();
            });

        $salesDcodes = $departments->pluck('dcode')->all();
        $allSalesPaycodes = $catPaycodes->flatten()->unique()->values()->all();

        $cellToday = Paycharge::selectRaw('restcode, paycode, SUM(amtdr - amtcr) AS Today')
            ->whereIn('restcode', $salesDcodes)
            ->whereIn('paycode', $allSalesPaycodes)
            ->where('propertyid', $this->propertyid)
            ->where('vdate', $fordate)
            ->groupBy('restcode', 'paycode')
            ->get()
            ->keyBy(fn ($r) => $r->restcode . '|' . $r->paycode);

        $cellMtd = Paycharge::selectRaw('restcode, paycode, SUM(amtdr - amtcr) AS MTD')
            ->whereIn('restcode', $salesDcodes)
            ->whereIn('paycode', $allSalesPaycodes)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->groupBy('restcode', 'paycode')
            ->get()
            ->keyBy(fn ($r) => $r->restcode . '|' . $r->paycode);

        $cellYtd = Paycharge::selectRaw('restcode, paycode, SUM(amtdr - amtcr) AS YTD')
            ->whereIn('restcode', $salesDcodes)
            ->whereIn('paycode', $allSalesPaycodes)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->groupBy('restcode', 'paycode')
            ->get()
            ->keyBy(fn ($r) => $r->restcode . '|' . $r->paycode);

        foreach ($departments as $department) {
            foreach ($categories as $category) {
                $today = null;
                $mtd = null;
                $ytd = null;
                foreach ($catPaycodes[$category->NAME] ?? [] as $pc) {
                    $cell = $cellToday[$department->dcode . '|' . $pc] ?? null;
                    if ($cell !== null) {
                        $today = ($today ?? 0.0) + (float) $cell->Today;
                    }
                    $cell = $cellMtd[$department->dcode . '|' . $pc] ?? null;
                    if ($cell !== null) {
                        $mtd = ($mtd ?? 0.0) + (float) $cell->MTD;
                    }
                    $cell = $cellYtd[$department->dcode . '|' . $pc] ?? null;
                    if ($cell !== null) {
                        $ytd = ($ytd ?? 0.0) + (float) $cell->YTD;
                    }
                }

                $reportData[] = [
                    'rcategory' => 'Sales Summary',
                    'category' => $department->name,
                    'rev_code' => $department->dcode,
                    'Name' => $category->NAME,
                    'short_name' => $department->short_name,
                    'Today' => $today,
                    'MTD' => $mtd,
                    'YTD' => $ytd
                ];
            }

            $discount = $discountByDepart[$department->dcode] ?? null;
            if ($discount) {
                $discToday = (float) ($discount['Today'] ?? 0);
                $discMtd = (float) ($discount['MTD'] ?? 0);
                $discYtd = (float) ($discount['YTD'] ?? 0);

                if ($discToday != 0.0 || $discMtd != 0.0 || $discYtd != 0.0) {
                    $reportData[] = [
                        'rcategory' => 'Sales Summary',
                        'category' => $department->name,
                        'rev_code' => $department->dcode,
                        'Name' => 'Discount',
                        'short_name' => $department->short_name,
                        'Today' => (float) round(-1 * $discToday, 2),
                        'MTD' => (float) round(-1 * $discMtd, 2),
                        'YTD' => (float) round(-1 * $discYtd, 2),
                    ];
                }
            }
        }

        // return $reportData;

        $banqsaletoday = PaychargeH::select('paytype as name', DB::raw('SUM(amtcr) as today'))
            ->where('propertyid', $this->propertyid)
            ->where('vdate', $fordate)
            ->whereNotIn('paycode', ["CGSP$this->propertyid", "SGSP$this->propertyid"])
            ->groupBy('paycode', 'paytype')
            ->get();

        $banqsalemtd = PaychargeH::select('paytype as name', DB::raw('SUM(amtcr) as MTD'))
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->whereNotIn('paycode', ["CGSP$this->propertyid", "SGSP$this->propertyid"])
            ->groupBy('paycode', 'paytype')
            ->get();

        $banqsaleytd = PaychargeH::select('paytype as name', DB::raw('SUM(amtcr) as YTD'))
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->whereNotIn('paycode', ["CGSP$this->propertyid", "SGSP$this->propertyid"])
            ->groupBy('paycode', 'paytype')
            ->get();

        // $reportData = [];

        foreach ($banqsaletoday as $today) {
            $mtd = $banqsalemtd->firstWhere('name', $today->name);
            $ytd = $banqsaleytd->firstWhere('name', $today->name);

            $reportData[] = [
                'category' => 'Payment Summary',
                'rev_code' => '',
                'Name' => $today->name,
                'short_name' => '',
                'Today' => $today->today ?? 0,
                'MTD' => $mtd->MTD ?? 0,
                'YTD' => $ytd->YTD ?? 0
            ];
        }

        // return $reportData;

        $hallToday = DB::table('hallstock')
            ->leftJoin('itemmast', function ($join) {
                $join->on('itemmast.Code', '=', 'hallstock.item')
                    ->on('itemmast.RestCode', '=', 'hallstock.restcode');
            })
            ->leftJoin('itemcatmast', function ($join) {
                $join->on('itemcatmast.Code', '=', 'itemmast.ItemCatCode')
                    ->on('itemcatmast.RestCode', '=', 'itemmast.RestCode');
            })
            ->select(
                'itemmast.ItemCatCode',
                'itemcatmast.Name as categoryname',
                DB::raw('SUM(hallstock.amount) as today')
            )
            ->where('hallstock.propertyid', $this->propertyid)
            ->where('hallstock.restcode', 'BANQ' . $this->propertyid)
            ->whereDate('hallstock.vdate', $fordate)
            ->groupBy('itemmast.ItemCatCode', 'itemcatmast.Name')
            ->get();

        // 🔹 MTD Data
        $hallMTD = DB::table('hallstock')
            ->leftJoin('itemmast', function ($join) {
                $join->on('itemmast.Code', '=', 'hallstock.item')
                    ->on('itemmast.RestCode', '=', 'hallstock.restcode');
            })
            ->leftJoin('itemcatmast', function ($join) {
                $join->on('itemcatmast.Code', '=', 'itemmast.ItemCatCode')
                    ->on('itemcatmast.RestCode', '=', 'itemmast.RestCode');
            })
            ->select(
                'itemmast.ItemCatCode',
                DB::raw('SUM(hallstock.amount) as MTD')
            )
            ->where('hallstock.propertyid', $this->propertyid)
            ->where('hallstock.restcode', 'BANQ' . $this->propertyid)
            ->whereBetween('hallstock.vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->groupBy('itemmast.ItemCatCode')
            ->get();

        // 🔹 YTD Data
        $hallYTD = DB::table('hallstock')
            ->leftJoin('itemmast', function ($join) {
                $join->on('itemmast.Code', '=', 'hallstock.item')
                    ->on('itemmast.RestCode', '=', 'hallstock.restcode');
            })
            ->leftJoin('itemcatmast', function ($join) {
                $join->on('itemcatmast.Code', '=', 'itemmast.ItemCatCode')
                    ->on('itemcatmast.RestCode', '=', 'itemmast.RestCode');
            })
            ->select(
                'itemmast.ItemCatCode',
                DB::raw('SUM(hallstock.amount) as YTD')
            )
            ->where('hallstock.propertyid', $this->propertyid)
            ->where('hallstock.restcode', 'BANQ' . $this->propertyid)
            ->whereBetween('hallstock.vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->groupBy('itemmast.ItemCatCode')
            ->get();

        foreach ($hallToday as $row) {
            $mtd = $hallMTD->firstWhere('ItemCatCode', $row->ItemCatCode);
            $ytd = $hallYTD->firstWhere('ItemCatCode', $row->ItemCatCode);

            $reportData[] = [
                'category' => 'Banquet',
                'rev_code' => $row->ItemCatCode,
                'Name' => $row->categoryname,
                'short_name' => '',
                'Today' => $row->today ?? 0,
                'MTD' => $mtd->MTD ?? 0,
                'YTD' => $ytd->YTD ?? 0
            ];
        }

        // 🔹 Today
        $banquetToday = DB::table('hallsale1')
            ->select(DB::raw("'Banquet Sale' as name"), DB::raw('SUM(totalpercover - discamt) as today'))
            ->where('propertyid', $this->propertyid)
            ->where('restcode', 'BANQ' . $this->propertyid)
            ->whereDate('vdate', $fordate)
            ->first();

        // 🔹 MTD
        $banquetMTD = DB::table('hallsale1')
            ->select(DB::raw('SUM(totalpercover - discamt) as MTD'))
            ->where('propertyid', $this->propertyid)
            ->where('restcode', 'BANQ' . $this->propertyid)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->first();

        // 🔹 YTD
        $banquetYTD = DB::table('hallsale1')
            ->select(DB::raw('SUM(totalpercover - discamt) as YTD'))
            ->where('propertyid', $this->propertyid)
            ->where('restcode', 'BANQ' . $this->propertyid)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->first();

        $reportData[] = [
            'category' => 'Banquet',
            'rev_code' => '',
            'Name' => $banquetToday->name ?? 'Banquet Sale',
            'short_name' => '',
            'Today' => $banquetToday->today ?? 0,
            'MTD' => $banquetMTD->MTD ?? 0,
            'YTD' => $banquetYTD->YTD ?? 0
        ];

        $taxp = Revmast::select('rev_code', 'name')
            ->where('propertyid', $this->propertyid)
            ->whereIn('field_type', ['T'])
            ->get();

        // Batch the per-tax-revcode aggregates (was 3 queries per tax code).
        $taxPaycodes = $taxp->pluck('rev_code')->all();
        $taxToday = Paycharge::selectRaw('paycode, SUM(amtdr) AS Today')
            ->where('propertyid', $this->propertyid)
            ->whereIn('paycode', $taxPaycodes)
            ->where('vdate', $fordate)
            ->groupBy('paycode')
            ->pluck('Today', 'paycode');

        $taxMtd = Paycharge::selectRaw('paycode, SUM(amtdr) AS MTD')
            ->where('propertyid', $this->propertyid)
            ->whereIn('paycode', $taxPaycodes)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->groupBy('paycode')
            ->pluck('MTD', 'paycode');

        $taxYtd = Paycharge::selectRaw('paycode, SUM(amtdr) AS YTD')
            ->where('propertyid', $this->propertyid)
            ->whereIn('paycode', $taxPaycodes)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->groupBy('paycode')
            ->pluck('YTD', 'paycode');

        foreach ($taxp as $row) {
            $reportData[] = [
                'category' => 'Tax Summary',
                'rev_code' => $row->rev_code,
                'Name' => $row->name,
                'short_name' => $row->name,
                'Today' => $taxToday[$row->rev_code] ?? null,
                'MTD' => $taxMtd[$row->rev_code] ?? null,
                'YTD' => $taxYtd[$row->rev_code] ?? null
            ];
        }

        $deposit = Revmast::select('rev_code', 'name')->where('propertyid', $this->propertyid)->whereNot('nature', 'Room')
            ->whereIn('field_type', ['P'])->get();

        // Batch the per-deposit-revcode aggregates (was 3 queries per code).
        // CHK advance docids (any deposit paytype) are excluded from the sums.
        $depRevcodes = $deposit->pluck('rev_code')->all();
        $depNames = $deposit->pluck('name')->all();

        $chkToday = Paycharge::where('vtype', 'CHK')
            ->whereIn('paytype', $depNames)
            ->where('propertyid', $this->propertyid)
            ->where('vdate', $fordate)
            ->pluck('docid')->all();

        $chkMtd = Paycharge::where('vtype', 'CHK')
            ->whereIn('paytype', $depNames)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->pluck('docid')->all();

        $chkYtd = Paycharge::where('vtype', 'CHK')
            ->whereIn('paytype', $depNames)
            ->where('propertyid', $this->propertyid)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->pluck('docid')->all();

        $depToday = Paycharge::selectRaw('paycode, SUM(amtcr) - SUM(amtdr) AS Today')
            ->where('propertyid', $this->propertyid)
            ->whereIn('paycode', $depRevcodes)
            ->where('vdate', $fordate)
            ->whereNotIn('docid', $chkToday)
            ->groupBy('paycode')
            ->pluck('Today', 'paycode');

        $depMtd = Paycharge::selectRaw('paycode, SUM(amtcr) - SUM(amtdr) AS MTD')
            ->where('propertyid', $this->propertyid)
            ->whereIn('paycode', $depRevcodes)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->whereNotIn('docid', $chkMtd)
            ->groupBy('paycode')
            ->pluck('MTD', 'paycode');

        $depYtd = Paycharge::selectRaw('paycode, SUM(amtcr) - SUM(amtdr) AS YTD')
            ->where('propertyid', $this->propertyid)
            ->whereIn('paycode', $depRevcodes)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->whereNotIn('docid', $chkYtd)
            ->groupBy('paycode')
            ->pluck('YTD', 'paycode');

        foreach ($deposit as $row) {
            $reportData[] = [
                'category' => 'Payment Summary',
                'rev_code' => $row->rev_code,
                'Name' => $row->name,
                'short_name' => $row->name,
                'Today' => $depToday[$row->rev_code] ?? null,
                'MTD' => $depMtd[$row->rev_code] ?? null,
                'YTD' => $depYtd[$row->rev_code] ?? null
            ];
        }

        $occupancy = RoomCat::select(
            'norooms',
            'cat_code AS roomcat',
            'name as roomcatname',
            'shortname'
        )
            ->where('type', 'RO')
            ->where('propertyid', $this->propertyid)
            ->orderBy('name', 'ASC')
            ->get();

        // return $occupancy;

        $totalRooms = (float) $occupancy->sum('norooms');
        $monthDays = ((int) ($ranges['diffcount']['frommon']->days ?? 0)) + 1;
        $financialDays = ((int) ($ranges['diffcount']['fromfin']->days ?? 0)) + 1;
        $occupancyCategoryRows = [];

        $chkrevmast = Revmast::where('propertyid', $this->propertyid)
            ->where('flag_type', 'FOM')
            ->where('field_type', 'C')
            ->where('nature', 'Room Charge')
            ->pluck('rev_code')
            ->count();

        if ($chkrevmast == 0) {
            return response()->json([
                'success' => false,
                'error' => 'No revenue codes found for room charges. Please check the FOM Charge Master'
            ]);
        }

        // Batch the per-roomcat occupancy counts (was 3 queries per category).
        $roomcats = $occupancy->pluck('roomcat')->all();
        $occToday = Paycharge::selectRaw('roomcat, COUNT(*) AS Today')
            ->whereIn('roomcat', $roomcats)
            ->where('vdate', $fordate)
            ->where('amtdr', '>', 0)
            ->where('paycode', "RMCH$this->propertyid")
            ->where('propertyid', $this->propertyid)
            ->groupBy('roomcat')
            ->pluck('Today', 'roomcat');

        $occMtd = Paycharge::selectRaw('roomcat, COUNT(*) AS MTD')
            ->whereIn('roomcat', $roomcats)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->where('amtdr', '>', 0)
            ->where('paycode', "RMCH$this->propertyid")
            ->where('propertyid', $this->propertyid)
            ->groupBy('roomcat')
            ->pluck('MTD', 'roomcat');

        $occYtd = Paycharge::selectRaw('roomcat, COUNT(*) AS YTD')
            ->whereIn('roomcat', $roomcats)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->where('amtdr', '>', 0)
            ->where('paycode', "RMCH$this->propertyid")
            ->where('propertyid', $this->propertyid)
            ->groupBy('roomcat')
            ->pluck('YTD', 'roomcat');

        foreach ($occupancy as $row) {
            $occupancyRow = [
                'totalrooms' => $row->norooms,
                'category' => "Room Category",
                'rev_code' => $row->roomcat,
                'Name' => $row->roomcatname,
                'short_name' => $row->roomcatname,
                'Today' => $occToday[$row->roomcat] ?? 0,
                'MTD' => $occMtd[$row->roomcat] ?? 0,
                'YTD' => $occYtd[$row->roomcat] ?? 0
            ];

            $reportData[] = $occupancyRow;
            $occupancyCategoryRows[] = $occupancyRow;
        }

        // return $occupancyRow;

        $totalOccupiedToday = collect($occupancyCategoryRows)->sum(function ($row) {
            return (float) ($row['Today'] ?? 0);
        });

        $totalOccupiedMtd = collect($occupancyCategoryRows)->sum(function ($row) {
            return (float) ($row['MTD'] ?? 0);
        });

        $totalOccupiedYtd = collect($occupancyCategoryRows)->sum(function ($row) {
            return (float) ($row['YTD'] ?? 0);
        });

        $totalComplimentaryToday = DB::table('guestprof')
            ->join('roomocc', function ($join) use ($fordate) {
                $join->on('roomocc.guestprof', '=', 'guestprof.guestcode')
                    ->where('roomocc.propertyid', $this->propertyid)
                    ->whereDate('roomocc.chkindate', $fordate)
                    ->where(function ($q) {
                        $q->where('roomocc.type', '!=', 'C')
                            ->orWhereNull('roomocc.type');
                    });
            })
            ->where('guestprof.propertyid', $this->propertyid)
            ->where('guestprof.complimentry', 'Y')
            ->count();

        $totalComplimentaryMtd = DB::table('guestprof')
            ->join('roomocc', function ($join) use ($ranges) {
                $join->on('roomocc.guestprof', '=', 'guestprof.guestcode')
                    ->where('roomocc.propertyid', $this->propertyid)
                    ->whereBetween('roomocc.chkindate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
                    ->where(function ($q) {
                        $q->where('roomocc.type', '!=', 'C')
                            ->orWhereNull('roomocc.type');
                    });
            })
            ->where('guestprof.propertyid', $this->propertyid)
            ->where('guestprof.complimentry', 'Y')
            ->count();

        $totalComplimentaryYtd = DB::table('guestprof')
            ->join('roomocc', function ($join) use ($ranges) {
                $join->on('roomocc.guestprof', '=', 'guestprof.guestcode')
                    ->where('roomocc.propertyid', $this->propertyid)
                    ->whereBetween('roomocc.chkindate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
                    ->where(function ($q) {
                        $q->where('roomocc.type', '!=', 'C')
                            ->orWhereNull('roomocc.type');
                    });
            })
            ->where('guestprof.propertyid', $this->propertyid)
            ->where('guestprof.complimentry', 'Y')
            ->count();

        $totalDayUseToday = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->whereColumn('chkindate', 'chkoutdate')
            ->where('type', 'O')
            ->whereDate('chkindate', $fordate)
            ->count();

        $totalDayUseMtd = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->whereColumn('chkindate', 'chkoutdate')
            ->where('type', 'O')
            ->whereBetween('chkindate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->count();

        $totalDayUseYtd = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->whereColumn('chkindate', 'chkoutdate')
            ->where('type', 'O')
            ->whereBetween('chkindate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->count();

        $totaloutoforder = DB::table('roomblockout')
            ->where('propertyid', $this->propertyid)
            ->where('block', 'Out of Order')
            ->whereDate('fromdate', $fordate)
            ->count();

        $totaloutofordermtd = DB::table('roomblockout')
            ->where('propertyid', $this->propertyid)
            ->where('block', 'Out of Order')
            ->whereBetween('fromdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->count();

        $totaloutoforderytd = DB::table('roomblockout')
            ->where('propertyid', $this->propertyid)
            ->where('block', 'Out of Order')
            ->whereBetween('fromdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->count();

        $totalcheckintoday = RoomOcc::where('propertyid', $this->propertyid)
            ->whereDate('chkindate', $fordate)
            ->where(function ($q) {
                $q->where('type', '!=', 'C')
                    ->orWhereNull('type')
                    ->orWhere('type', 'O');
            })
            ->count();

        $totalcheckinmtd = RoomOcc::where('propertyid', $this->propertyid)
            ->whereBetween('chkindate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->where(function ($q) {
                $q->where('type', '!=', 'C')
                    ->orWhereNull('type')
                    ->orWhere('type', 'O');
            })
            ->count();

        $totalcheckinytd = RoomOcc::where('propertyid', $this->propertyid)
            ->whereBetween('chkindate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->where(function ($q) {
                $q->where('type', '!=', 'C')
                    ->orWhereNull('type')
                    ->orWhere('type', 'O');
            })
            ->count();

        $totalcheckouttoday = RoomOcc::where('propertyid', $this->propertyid)
            ->whereDate('chkoutdate', $fordate)
            ->where('type', 'O')
            ->count();

        $totalcheckoutmtd = RoomOcc::where('propertyid', $this->propertyid)
            ->whereBetween('chkoutdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->where('type', 'O')
            ->count();

        $totalcheckoutytd = RoomOcc::where('propertyid', $this->propertyid)
            ->whereBetween('chkoutdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->where('type', 'O')
            ->count();

        $totalpaxtoday = Roomocc::select(
            DB::raw('SUM(adult) + SUM(children) AS totalpax')
        )
            ->where(function ($q) {
                $q->where('roomocc.type', '!=', 'C')
                    ->orWhereNull('roomocc.type')
                    ->orWhere('roomocc.type', 'O');
            })
            ->where('propertyid', $this->propertyid)
            ->whereDate('chkindate', $fordate)
            ->first();

        $totalpaxmtd = RoomOcc::select(
            DB::raw('SUM(adult) + SUM(children) AS totalpax')
        )
            ->where(function ($q) {
                $q->where('roomocc.type', '!=', 'C')
                    ->orWhereNull('roomocc.type')
                    ->orWhere('roomocc.type', 'O');
            })
            ->where('propertyid', $this->propertyid)
            ->whereBetween('chkindate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->first();

        $totalpaxytd = RoomOcc::select(
            DB::raw('SUM(adult) + SUM(children) AS totalpax')
        )
            ->where(function ($q) {
                $q->where('roomocc.type', '!=', 'C')
                    ->orWhereNull('roomocc.type')
                    ->orWhere('roomocc.type', 'O');
            })
            ->where('propertyid', $this->propertyid)
            ->whereBetween('chkindate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->first();

        $occupancySummaryTotals = [
            [
                'name' => 'Total Vacant Room',
                'today' => max(0, $totalRooms - $totalOccupiedToday),
                'MTD' => max(0, ($totalRooms * $monthDays) - $totalOccupiedMtd),
                'YTD' => max(0, ($totalRooms * $financialDays) - $totalOccupiedYtd),
            ],
            [
                'name' => 'Total Complimentary',
                'today' => $totalComplimentaryToday,
                'MTD' => $totalComplimentaryMtd,
                'YTD' => $totalComplimentaryYtd,
            ],
            [
                'name' => 'Total Day Use',
                'today' => $totalDayUseToday,
                'MTD' => $totalDayUseMtd,
                'YTD' => $totalDayUseYtd,
            ],
            [
                'name' => 'Total Out of Order',
                'today' => $totaloutoforder,
                'MTD' => $totaloutofordermtd,
                'YTD' => $totaloutoforderytd,
            ],
            [
                'name' => 'Total Checkin Room',
                'today' => $totalcheckintoday,
                'MTD' => $totalcheckinmtd,
                'YTD' => $totalcheckinytd,
            ],
            [
                'name' => 'Total Checkout Room',
                'today' => $totalcheckouttoday,
                'MTD' => $totalcheckoutmtd,
                'YTD' => $totalcheckoutytd,
            ],
            [
                'name' => 'Total Pax',
                'today' => $totalpaxtoday->totalpax ?? 0,
                'MTD' => $totalpaxmtd->totalpax ?? 0,
                'YTD' => $totalpaxytd->totalpax ?? 0,
            ],
        ];

        // Batch the per-roomcat room-average aggregates (was 3 queries per category).
        $roomChargeRevcodes = Revmast::where('propertyid', $this->propertyid)
            ->where('flag_type', 'FOM')
            ->where('field_type', 'C')
            ->where('nature', 'Room Charge')
            ->pluck('rev_code')->all();

        $avgToday = Paycharge::selectRaw('roomcat, COUNT(roomno) as todaycount, SUM(amtdr - amtcr) as Today')
            ->whereIn('roomcat', $roomcats)
            ->where('vdate', $fordate)
            ->where('propertyid', $this->propertyid)
            ->where('amtdr', '>', 0)
            ->whereIn('paycode', $roomChargeRevcodes)
            ->groupBy('roomcat')
            ->get()
            ->keyBy('roomcat');

        $avgMtd = Paycharge::selectRaw('roomcat, COUNT(roomno) as mtdcount, SUM(amtdr - amtcr) as MTD')
            ->whereIn('roomcat', $roomcats)
            ->whereBetween('vdate', [$ranges['mtd']['start'], $ranges['mtd']['end']])
            ->where('propertyid', $this->propertyid)
            ->where('amtdr', '>', 0)
            ->whereIn('paycode', $roomChargeRevcodes)
            ->groupBy('roomcat')
            ->get()
            ->keyBy('roomcat');

        $avgYtd = Paycharge::selectRaw('roomcat, COUNT(roomno) as ytdcount, SUM(amtdr - amtcr) as YTD')
            ->whereIn('roomcat', $roomcats)
            ->whereBetween('vdate', [$ranges['ftd']['start'], $ranges['ftd']['end']])
            ->where('propertyid', $this->propertyid)
            ->where('amtdr', '>', 0)
            ->whereIn('paycode', $roomChargeRevcodes)
            ->groupBy('roomcat')
            ->get()
            ->keyBy('roomcat');

        foreach ($occupancy as $row) {
            $avg = $avgToday[$row->roomcat] ?? null;
            $avgm = $avgMtd[$row->roomcat] ?? null;
            $avgy = $avgYtd[$row->roomcat] ?? null;

            $reportData[] = [
                'totalrooms' => $row->norooms,
                'category' => "Room Average",
                'rev_code' => $row->roomcat,
                'Name' => $row->roomcatname,
                'short_name' => $row->roomcatname,
                'todaycount' => $avg->todaycount ?? 0,
                'mtdcount' => $avgm->mtdcount ?? 0,
                'ytdcount' => $avgy->ytdcount ?? 0,
                'Today' => $avg->Today ?? 0,
                'MTD' => $avgm->MTD ?? 0,
                'YTD' => $avgy->YTD ?? 0
            ];
        }

        // Company Query

        $companydatatoday = DB::table('paycharge as P')
            ->leftJoin('revmast as PY', 'P.paycode', '=', 'PY.rev_code')
            ->leftJoin('guestfolio as G', 'P.folionodocid', '=', 'G.docid')
            ->leftJoin('subgroup as S', 'P.comp_code', '=', 'S.sub_code')
            ->leftJoin('fombilldetails', function($join) {
                $join->on('P.folionodocid', '=', 'fombilldetails.folionodocid')
                ->where('fombilldetails.propertyid', $this->propertyid)
                ->where('fombilldetails.status', 'settle');
            })
            ->leftJoin(DB::raw("(SELECT DISTINCT folionoDocid, billno FROM paycharge WHERE propertyid = $this->propertyid AND amtdr <> 0 AND (modeset IS NULL OR modeset = '')) AS B"), 'P.folionodocid', '=', 'B.folionoDocid')
            ->select([
                'P.docid',
                'P.vtype',
                'P.vno',
                'P.msno1',
                'P.foliono AS foliono',
                'P.folionodocid',
                'S.name AS name',
                'P.paytype AS paycode',
                'P.amtcr AS amount',
                'fombilldetails.billno'
            ])
            ->where(function ($query) {
                $query->whereIn('P.vtype', ['ARRES', 'ADRES', 'AWRES'])
                    ->orWhere(function ($query) {
                        $query->whereNotIn('P.vtype', ['ARRES', 'ADRES', 'AWRES'])
                            ->where(function ($subquery) {
                                $subquery->whereNull('P.contraid')
                                    ->orWhere('P.contraid', '=', '');
                            });
                    });
            })
            ->where([
                ['P.vdate', '=', $fordate],
                ['P.modeset', '=', 'S'],
                ['P.propertyid', '=', $this->propertyid],
                ['P.restcode', '=', 'FOM' . $this->propertyid],
                ['PY.field_type', '=', 'P'],
                ['P.paytype', '=', 'Company']
            ])
            ->where('P.vtype', '<>', 'CHK')
            ->groupBy('P.docid')
            ->orderBy('S.name')
            ->orderBy('P.folionodocid')
            ->orderBy('P.foliono')
            ->orderBy('P.vtype')
            ->orderBy('P.vno')
            ->get();

        foreach ($companydatatoday as $row) {
            $reportData[] = [
                'category' => "CompanyData",
                'Name' => $row->name,
                'billno' => 'FOM/' . $row->billno,
                'amount' => $row->amount ?? 0,
            ];
        }

        $poscodes = [];
        $depname = [];
        $companypos = Depart::selectRaw("'' AS opt, name AS outlet, dcode")
            ->where('propertyid', $this->propertyid)
            ->whereIn('rest_type', ['Outlet', 'Room Service'])
            ->whereIn('pos', ['Y'])
            ->orderBy('name')
            ->get();

        foreach ($companypos as $row) {
            $poscodes[] = $row->dcode;
            $depname[] = $row->outlet;
        }

        $companyposa = DB::table('paycharge as P')
            ->leftJoin('subgroup as S', 'P.comp_code', '=', 'S.sub_code')
            ->select([
                'P.vno as billno',
                'P.vtype',
                'P.paytype',
                'S.name',
                'P.restcode',
                DB::raw('(P.amtcr - P.amtdr) AS amount')
            ])
            ->where('P.propertyid', $this->propertyid)
            ->whereIn('P.restcode', $poscodes)
            ->whereDate('P.vdate', $fordate)
            ->where('P.paytype', 'Company')
            ->orderBy('S.name')
            ->get();

        foreach ($companyposa as $row) {
            $key = array_search($row->restcode, $poscodes);
            $departmentName = $key !== false ? $depname[$key] : '';

            $reportData[] = [
                'category' => "CompanyData",
                'Name' => $row->name,
                'billno' =>  $departmentName . '/' . $row->billno,
                'amount' => $row->amount ?? 0,
            ];
        }


        $frontOfficeRevenue = ['today' => 0.0, 'MTD' => 0.0, 'YTD' => 0.0];
        $salesSummaryRevenue = ['today' => 0.0, 'MTD' => 0.0, 'YTD' => 0.0];
        $banquetRevenue = ['today' => 0.0, 'MTD' => 0.0, 'YTD' => 0.0];

        foreach ($reportData as $row) {
            $today = (float) ($row['Today'] ?? 0);
            $mtd = (float) ($row['MTD'] ?? 0);
            $ytd = (float) ($row['YTD'] ?? 0);

            if ($today == 0.0 && $mtd == 0.0 && $ytd == 0.0) {
                continue;
            }

            if (($row['category'] ?? '') === 'Front Office') {
                $frontOfficeRevenue['today'] += $today;
                $frontOfficeRevenue['MTD'] += $mtd;
                $frontOfficeRevenue['YTD'] += $ytd;
                continue;
            }

            if (($row['rcategory'] ?? '') === 'Sales Summary') {
                $salesSummaryRevenue['today'] += $today;
                $salesSummaryRevenue['MTD'] += $mtd;
                $salesSummaryRevenue['YTD'] += $ytd;
                continue;
            }

            if (($row['category'] ?? '') === 'Banquet') {
                $banquetRevenue['today'] += $today;
                $banquetRevenue['MTD'] += $mtd;
                $banquetRevenue['YTD'] += $ytd;
                continue;
            }
        }

        $totalRevenue = [
            'today' => (float) ($frontOfficeRevenue['today'] + $salesSummaryRevenue['today'] + $banquetRevenue['today']),
            'MTD' => (float) ($frontOfficeRevenue['MTD'] + $salesSummaryRevenue['MTD'] + $banquetRevenue['MTD']),
            'YTD' => (float) ($frontOfficeRevenue['YTD'] + $salesSummaryRevenue['YTD'] + $banquetRevenue['YTD']),
            'breakdown' => [
                'front_office' => $frontOfficeRevenue,
                'sales_summary' => $salesSummaryRevenue,
                'banquet' => $banquetRevenue,
            ],
        ];

        $totalRevenue['today'] = (float) round($totalRevenue['today'], 2);
        $totalRevenue['MTD'] = (float) round($totalRevenue['MTD'], 2);
        $totalRevenue['YTD'] = (float) round($totalRevenue['YTD'], 2);
        $totalRevenue['breakdown']['front_office']['today'] = (float) round($totalRevenue['breakdown']['front_office']['today'], 2);
        $totalRevenue['breakdown']['front_office']['MTD'] = (float) round($totalRevenue['breakdown']['front_office']['MTD'], 2);
        $totalRevenue['breakdown']['front_office']['YTD'] = (float) round($totalRevenue['breakdown']['front_office']['YTD'], 2);
        $totalRevenue['breakdown']['sales_summary']['today'] = (float) round($totalRevenue['breakdown']['sales_summary']['today'], 2);
        $totalRevenue['breakdown']['sales_summary']['MTD'] = (float) round($totalRevenue['breakdown']['sales_summary']['MTD'], 2);
        $totalRevenue['breakdown']['sales_summary']['YTD'] = (float) round($totalRevenue['breakdown']['sales_summary']['YTD'], 2);
        $totalRevenue['breakdown']['banquet']['today'] = (float) round($totalRevenue['breakdown']['banquet']['today'], 2);
        $totalRevenue['breakdown']['banquet']['MTD'] = (float) round($totalRevenue['breakdown']['banquet']['MTD'], 2);
        $totalRevenue['breakdown']['banquet']['YTD'] = (float) round($totalRevenue['breakdown']['banquet']['YTD'], 2);

        $snapshotPayload = $dailyReportSnapshotService->buildPayload($reportData, $ranges, $fordate, $occupancySummaryTotals, $totalRevenue);
        $snapshotKey = $dailyReportSnapshotService->storeSnapshot(
            $this->propertyid,
            $fordate,
            Auth::user()->name ?? null,
            $snapshotPayload
        );

        $data = [
            'occupancy' => $occupancy,
            'taxp' => $taxp,
            'ranges' => $ranges,
            'reportData' => $reportData,
            'occupancySummaryTotals' => $occupancySummaryTotals,
            'poscodes' => $poscodes,
            'total_revenue' => $totalRevenue,
            'snapshot_key' => $snapshotKey,
        ];

        return json_encode($data);
    }

    public function dailyreportprint(Request $request, DailyReportSnapshotService $dailyReportSnapshotService)
    {
        $permission = revokeopen(191212);
        if (is_null($permission) || $permission->print == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $snapshotKey = $request->query('snapshot_key');
        if (!$snapshotKey) {
            return redirect()->back()->with('error', 'Daily report snapshot key is missing.');
        }

        $snapshotData = $dailyReportSnapshotService->getSnapshot($snapshotKey, $this->propertyid);
        if (!$snapshotData) {
            return redirect()->back()->with('error', 'Daily report snapshot not found or expired.');
        }

        $comp = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)->where('state_code', $comp->state_code)->value('name');
        $payload = $snapshotData['payload'];
        $logoPath = companylogo();

        $pdf = Pdf::loadView('property.dailyreportprint', [
            'comp' => $comp,
            'statename' => $statename,
            'payload' => $payload,
            'snapshotKey' => $snapshotKey,
            'logoPath' => $logoPath,
        ])->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->setOption('dpi', 110);

        $pdf->render();
        $pdf->addInfo([
            'Title' => 'Daily Register Report',
            'Author' => 'Analysis HMS',
            'Creator' => 'Analysis HMS',
            'Producer' => 'Analysis HMS',
            'Subject' => 'Daily Register Report',
        ]);

        $filename = 'Daily_Report_' . ($payload['fordate'] ?? date('Y-m-d')) . '.pdf';
        return $pdf->stream($filename);
    }
}
