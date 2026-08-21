<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Helpers\DateHelper;
use App\Models\ACGroup;
use App\Models\Companyreg;
use App\Models\Ledger;
use App\Models\TdsCategory;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class FinanceController extends Controller
{
    protected $username;
    protected $email;
    protected $propertyid;
    protected $currenttime;
    protected $ptlngth;
    protected $prpid;
    protected $ncurdate;
    protected $datemanage;
    protected $company;

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
            $this->datemanage = DateHelper::calculateDateRanges($this->ncurdate);
            $this->company = Companyreg::where('propertyid', $this->propertyid)->first();
            return $next($request);
        });
    }

    public function trialgroupbalance(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        return view('property.finance.trialgroupbalance');
    }

    public function trialgroupmainquery(Request $request)
    {
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $openingbalance = $request->openingbalance;
        $allproperties = $request->allproperties;

        if ($openingbalance != 'checked') {
            // WITHOUT opening balance
            // $mainrows = Ledger::select(
            //     'acgroup.maingroupname as name',
            //     'acgroup.group_code',
            //     'acgroup.maingroupcode',
            //     DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
            //     'acgroup.undergroup'
            // )
            //     ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
            //     ->leftJoin('acgroup', function ($join) {
            //         $join->on('acgroup.group_code', '=', 'subgroup.group_code')
            //             ->where('acgroup.sys_group', 'Y');
            //     })
            //     ->whereIn('ledger.propertyid', $allproperties)
            //     ->whereBetween('ledger.vdate', [$fromdate, $todate])
            //     ->groupBy(
            //         // 'acgroup.maingroupcode',
            //         // 'acgroup.maingroupname',
            //         'acgroup.group_code'
            //     )
            //     ->orderBy('acgroup.maingroupname')
            //     ->get();
            $mainrows = DB::table('acgroup as A')
                ->leftJoin('ledger as L', function ($join) {
                    $join->on('A.group_code', '=', 'L.groupcode');
                })
                ->select(
                    'A.maingroupname as name',
                    'A.group_code',
                    'A.maingroupcode',
                    DB::raw('SUM(L.amtdr) - SUM(L.amtcr) AS balance'),
                    'A.undergroup'
                )
                ->whereIn('A.propertyid', $allproperties)
                ->whereIn('L.propertyid', $allproperties)
                ->whereBetween('L.vdate', [$fromdate, $todate])
                ->groupBy('A.maingroupname')
                ->orderBy('A.maingroupname')
                ->get();
        } else {
            // WITH opening balance
            // $mainrows = Ledger::select(
            //     'acgroup.maingroupname as name',
            //     'acgroup.group_code',
            //     'acgroup.maingroupcode',
            //     DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
            //     'acgroup.undergroup'
            // )
            //     ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
            //     ->leftJoin('acgroup', function ($join) {
            //         $join->on('acgroup.group_code', '=', 'subgroup.group_code')
            //             ->where('acgroup.sys_group', 'Y');
            //     })
            //     ->whereIn('ledger.propertyid', $allproperties)
            //     ->where('ledger.vdate', '<=', $todate)
            //     ->groupBy(
            //         // 'acgroup.maingroupcode',
            //         // 'acgroup.maingroupname',
            //         'acgroup.group_code'
            //     )
            //     ->orderBy('acgroup.maingroupname')
            //     ->get();
            $mainrows = DB::table('acgroup as A')
                ->leftJoin('ledger as L', function ($join) {
                    $join->on('A.group_code', '=', 'L.groupcode');
                })
                ->select(
                    'A.maingroupname as name',
                    'A.group_code',
                    'A.maingroupcode',
                    DB::raw('SUM(L.amtdr) - SUM(L.amtcr) AS balance'),
                    'A.undergroup'
                )
                ->whereIn('A.propertyid', $allproperties)
                ->whereIn('L.propertyid', $allproperties)
                ->whereBetween('L.vdate', [$fromdate, $todate])
                ->groupBy('A.maingroupname')
                ->orderBy('A.maingroupname')
                ->get();
        }

        if ($mainrows->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Data Found'
            ]);
        }

        $subgroupDetails = [];

        foreach ($mainrows as $mainrow) {
            $groupByColumn = $mainrow->undergroup == "N"
                ? 'acgroup.group_code'
                : 'ledger.subcode';

            $subgroups = Ledger::select([
                DB::raw('
                        CASE 
                            WHEN acgroup.undergroup = "Y" 
                            THEN subgroup.name 
                            ELSE acgroup.group_name 
                        END AS name
                    '),
                DB::raw('
                    CASE 
                        WHEN acgroup.undergroup = "Y" 
                        THEN 0 
                        ELSE 1
                    END AS groupynvalue
                '),
                DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                'ledger.subcode',
                'acgroup.undergroup',
                'acgroup.group_code as acgroupcode',
            ])
                ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                ->whereIn('ledger.propertyid', $allproperties)
                ->whereBetween('ledger.vdate', [$fromdate, $todate])
                ->where('subgroup.group_code', $mainrow->group_code)
                ->groupBy($groupByColumn)
                ->orderBy('name')
                ->get();

            $subgroupDetails[$mainrow->group_code] = $subgroups;
        }

        return response()->json([
            'mainrows' => $mainrows,
            'subgroupDetails' => $subgroupDetails
        ]);
    }

    public function fetchsubgroupdetails(Request $request)
    {
        $maingroupcode = $request->maingroupcode;
        $group_code = $request->group_code;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $openingbalance = $request->openingbalance;
        $allproperties = $request->allproperties;
        $openingBalanceEnabled = ($openingbalance !== 'not checked');

        $undergroup = ACGroup::whereIn('propertyid', $allproperties)->where('group_code', $group_code)->value('undergroup');
        // $groupByColumn = $undergroup == "N"
        //     ? 'acgroup.group_code'
        //     : 'ledger.subcode';

        $groupByColumn = $undergroup == "N"
            ? 'acgroup.group_code'
            : 'ledger.subcode';

        // Log::info('Group Code: ' . $group_code . ', Undergroup: ' . $undergroup . ', Group By Column: ' . $groupByColumn);

        // $query = Ledger::select(
        //     DB::raw('
        //             CASE 
        //                 WHEN acgroup.undergroup = "Y" 
        //                 THEN subgroup.name 
        //                 ELSE acgroup.group_name 
        //             END AS name
        //         '),
        //     DB::raw('
        //             CASE 
        //                 WHEN acgroup.undergroup = "Y" 
        //                 THEN 0 
        //                 ELSE 1
        //             END AS groupynvalue
        //         '),
        //     'ledger.docid',
        //     'ledger.vtype',
        //     'ledger.vdate',
        //     DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
        //     'ledger.subcode',
        //     'acgroup.group_code as acgroupcode',
        //     'acgroup.maingroupcode',
        // )
        //     ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
        //     ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
        //     ->where('ledger.propertyid', $this->propertyid)
        //     ->where('acgroup.maingroupcode', $maingroupcode)
        //     ->whereBetween('ledger.vdate', [$fromdate, $todate])
        //     ->orderBy('subgroup.name');

        // $subgroups = $query->groupBy($groupByColumn)
        //     ->orderBy('subgroup.name')
        //     ->get();

        $propertyIds = implode(',', $allproperties);
        $dateCondition = $openingBalanceEnabled
            ? "ledger.vdate <= '{$todate}'"
            : "ledger.vdate BETWEEN '{$fromdate}' AND '{$todate}'";
        $subgroups = DB::table(DB::raw("(SELECT
        CASE WHEN acgroup.undergroup = 'Y' THEN subgroup.name ELSE acgroup.group_name END AS name,
        CASE WHEN acgroup.undergroup = 'Y' THEN 0 ELSE 1 END AS groupynvalue,
        SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance,
        acgroup.group_code as acgroupcode,
        ledger.docid,
        ledger.vtype,
        ledger.vdate,
        DATE_FORMAT(ledger.vdate, '%Y') AS year,
        acgroup.maingroupcode,
        acgroup.undergroup,
        ledger.subcode
            FROM ledger
            LEFT JOIN subgroup ON subgroup.sub_code = ledger.subcode
            LEFT JOIN acgroup ON acgroup.group_code = subgroup.group_code
            WHERE ledger.propertyid IN ({$propertyIds})
                AND acgroup.maingroupcode = {$maingroupcode}
                AND acgroup.propertyid IN ({$propertyIds})
                AND {$dateCondition}
            GROUP BY name, groupynvalue
        ) AS x"))
            ->select(
                'name',
                'groupynvalue',
                DB::raw('SUM(balance) AS balance'),
                'acgroupcode',
                'docid',
                'vtype',
                'vdate',
                'year',
                'maingroupcode',
                'subcode',
                'undergroup'
            )
            ->groupBy('name', 'groupynvalue')
            ->orderBy('name', 'asc')
            ->get();

        $data = [
            'subgroups' => $subgroups
        ];
        return response()->json($data);
    }

    public function fetchsubgroupdetails2(Request $request)
    {
        $maingroupcode = $request->maingroupcode;
        $group_code = $request->group_code;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $openingbalance = $request->openingbalance;
        $acgroupcode = $request->acgroupcode;
        $allproperties = $request->allproperties;
        $openingBalanceEnabled = ($openingbalance !== 'not checked');

        // $undergroup = ACGroup::where('propertyid', $this->propertyid)->where('group_code', $group_code)->value('undergroup');
        // $groupByColumn = $undergroup == "N"
        //     ? 'acgroup.group_code'
        //     : 'ledger.subcode';

        // Log::info('Group Code: ' . $group_code . ', Undergroup: ' . $undergroup . ', Group By Column: ' . $groupByColumn);

        $query = Ledger::select(
            'subgroup.name',
            'ledger.docid',
            'ledger.vtype',
            'ledger.vdate',
            DB::raw('DATE_FORMAT(ledger.vdate, "%Y") AS year'),
            DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
            'ledger.subcode',
            'acgroup.group_code as acgroupcode',
            'acgroup.maingroupcode',
        )
            ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
            ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
            ->whereIn('ledger.propertyid', $allproperties)
            ->where('acgroup.group_code', $acgroupcode)
            // ->where('acgroup.maingroupcode', $maingroupcode)
            ->when(!$openingBalanceEnabled, function ($q) use ($fromdate, $todate) {
                $q->whereBetween('ledger.vdate', [$fromdate, $todate]);
            })
            ->when($openingBalanceEnabled, function ($q) use ($todate) {
                $q->where('ledger.vdate', '<=', $todate);
            })
            ->orderBy('subgroup.name');

        $subgroups = $query->groupBy('ledger.subcode')
            ->orderBy('subgroup.name')
            ->get();

        $data = [
            'subgroups' => $subgroups
        ];
        return response()->json($data);
    }

    public function detailedTrialLedger(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $fromdate = $this->datemanage['mtd']['start'];
        $todate = $this->ncurdate;

        $companyName = $this->company->comp_name ?? '';
        $reportDate = $this->ncurdate;

        return view('property.finance.detailedtrialledger', compact('fromdate', 'todate', 'companyName', 'reportDate'));
    }

    public function detailedTrialLedgerQuery(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate' => 'required|date|after_or_equal:fromdate',
        ]);

        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $propertyid = $this->propertyid;

        $data = DB::table('subgroup as s')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 's.propertyid');
            })
            ->leftJoin('ledger as l', function ($join) use ($propertyid) {
                $join->on('s.sub_code', '=', 'l.subcode')
                    ->where('l.propertyid', $propertyid);
            })
            ->where('s.propertyid', $propertyid)
            ->select(
                's.sub_code',
                's.name',
                'a.group_name'
            )
            ->selectRaw('SUM(CASE WHEN l.vdate < ? THEN l.amtdr ELSE 0 END) AS opening_dr', [$fromdate])
            ->selectRaw('SUM(CASE WHEN l.vdate < ? THEN l.amtcr ELSE 0 END) AS opening_cr', [$fromdate])
            ->selectRaw('SUM(CASE WHEN l.vdate BETWEEN ? AND ? THEN l.amtdr ELSE 0 END) AS trans_dr', [$fromdate, $todate])
            ->selectRaw('SUM(CASE WHEN l.vdate BETWEEN ? AND ? THEN l.amtcr ELSE 0 END) AS trans_cr', [$fromdate, $todate])
            ->selectRaw('SUM(CASE WHEN l.vdate <= ? THEN l.amtdr ELSE 0 END) AS closing_dr', [$todate])
            ->selectRaw('SUM(CASE WHEN l.vdate <= ? THEN l.amtcr ELSE 0 END) AS closing_cr', [$todate])
            ->groupBy('s.sub_code', 's.name', 'a.group_name')
            ->orderBy('a.group_name')
            ->orderBy('s.name')
            ->get()
            ->filter(function ($row) {
                // remove rows where all amounts are zero or null
                $vals = [
                    (float) ($row->opening_dr ?? 0),
                    (float) ($row->opening_cr ?? 0),
                    (float) ($row->trans_dr ?? 0),
                    (float) ($row->trans_cr ?? 0),
                    (float) ($row->closing_dr ?? 0),
                    (float) ($row->closing_cr ?? 0),
                ];

                foreach ($vals as $v) {
                    if (abs($v) > 0.00001) {
                        return true;
                    }
                }
                return false;
            })->values();

        return response()->json(['data' => $data]);
    }

    public function printDetailedTrialLedger(Request $request)
    {
        $fromDate = $request->query('fromdate', $this->datemanage['mtd']['start']);
        $toDate = $request->query('todate', $this->ncurdate);

        try {
            $from = Carbon::parse($fromDate)->format('Y-m-d');
            $to = Carbon::parse($toDate)->format('Y-m-d');
        } catch (Exception $e) {
            $from = $this->datemanage['mtd']['start'];
            $to = $this->ncurdate;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $propertyid = $this->propertyid;

        $reportData = DB::table('subgroup as s')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 's.propertyid');
            })
            ->leftJoin('ledger as l', function ($join) use ($propertyid) {
                $join->on('s.sub_code', '=', 'l.subcode')
                    ->where('l.propertyid', $propertyid);
            })
            ->where('s.propertyid', $propertyid)
            ->select(
                's.sub_code',
                's.name',
                'a.group_name'
            )
            ->selectRaw('SUM(CASE WHEN l.vdate < ? THEN l.amtdr ELSE 0 END) AS opening_dr', [$from])
            ->selectRaw('SUM(CASE WHEN l.vdate < ? THEN l.amtcr ELSE 0 END) AS opening_cr', [$from])
            ->selectRaw('SUM(CASE WHEN l.vdate BETWEEN ? AND ? THEN l.amtdr ELSE 0 END) AS trans_dr', [$from, $to])
            ->selectRaw('SUM(CASE WHEN l.vdate BETWEEN ? AND ? THEN l.amtcr ELSE 0 END) AS trans_cr', [$from, $to])
            ->selectRaw('SUM(CASE WHEN l.vdate <= ? THEN l.amtdr ELSE 0 END) AS closing_dr', [$to])
            ->selectRaw('SUM(CASE WHEN l.vdate <= ? THEN l.amtcr ELSE 0 END) AS closing_cr', [$to])
            ->groupBy('s.sub_code', 's.name', 'a.group_name')
            ->orderBy('a.group_name')
            ->orderBy('s.name')
            ->get()
            ->filter(function ($row) {
                $vals = [
                    (float) ($row->opening_dr ?? 0),
                    (float) ($row->opening_cr ?? 0),
                    (float) ($row->trans_dr ?? 0),
                    (float) ($row->trans_cr ?? 0),
                    (float) ($row->closing_dr ?? 0),
                    (float) ($row->closing_cr ?? 0),
                ];
                foreach ($vals as $v) {
                    if (abs($v) > 0.00001) {
                        return true;
                    }
                }
                return false;
            })->values();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'property.print.printdetailedtrialledger',
            [
                'company' => $this->company,
                'reportData' => $reportData,
                'fromDate' => $from,
                'toDate' => $to,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->stream('detailed-trial-ledger.pdf');
    }

    public function exportDetailedTrialLedger(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $companyName = $this->company->comp_name ?? '';

        $export = new \App\Exports\DetailedTrialLedgerExport(
            $request->fromdate,
            $request->todate,
            $this->propertyid,
            $companyName
        );

        return $export->download();
    }

    public function generalLedger(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $fromdate = $this->datemanage['mtd']['start'];
        $todate = $this->ncurdate;

        $companyName = $this->company->comp_name ?? '';
        $reportDate = $this->ncurdate;

        return view('property.finance.generalledger', compact('fromdate', 'todate', 'companyName', 'reportDate'));
    }

    public function generalLedgerAccounts(Request $request)
    {
        $propertyid = $this->propertyid;
        $accounts = DB::table('subgroup as s')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 's.propertyid');
            })
            ->where('s.propertyid', $propertyid)
            ->where(function ($q) {
                $q->where('s.activeyn', 'Y')->orWhereNull('s.activeyn');
            })
            ->select('s.sub_code', 's.name', 'a.group_name')
            ->orderBy('s.name')
            ->get();

        return response()->json(['data' => $accounts]);
    }

    public function generalLedgerQuery(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $propertyid = $this->propertyid;
        $subcodes = $request->subcodes; // optional array of sub_code filters

        // Opening balances: all ledger activity before fromdate, per account
        $openingQuery = DB::table('ledger as l')
            // LEFT join for legacy VIEWLEDGER parity (BUG-QA-010): postings with an
            // empty/missing subcode must not be silently dropped from the ledger.
            ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 'l.propertyid');
            })
            ->where('l.propertyid', $propertyid)
            ->where('l.vdate', '<', $fromdate)
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            });

        if (!empty($subcodes) && is_array($subcodes)) {
            $openingQuery->whereIn('l.subcode', $subcodes);
        }

        $openings = $openingQuery
            ->select('s.sub_code', 's.name', 'a.group_name')
            ->selectRaw('SUM(l.amtdr) AS opening_dr')
            ->selectRaw('SUM(l.amtcr) AS opening_cr')
            ->groupBy('s.sub_code', 's.name', 'a.group_name')
            ->get()
            ->keyBy('sub_code');

        // Transactions in period, per account, ordered by date/docid/vsno
        $txnQuery = DB::table('ledger as l')
            // LEFT join for legacy VIEWLEDGER parity (BUG-QA-010).
            ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 'l.propertyid');
            })
            ->where('l.propertyid', $propertyid)
            ->whereBetween('l.vdate', [$fromdate, $todate])
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            });

        if (!empty($subcodes) && is_array($subcodes)) {
            $txnQuery->whereIn('l.subcode', $subcodes);
        }

        $txns = $txnQuery
            ->select(
                'l.subcode',
                's.name',
                'a.group_name',
                'l.vdate',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vprefix',
                'l.narration',
                'l.contrasub',
                'l.chqno',
                'l.chqdate',
                'l.amtdr',
                'l.amtcr'
            )
            ->orderBy('s.name')
            ->orderBy('l.vdate')
            ->orderBy('l.docid')
            ->orderBy('l.vsno')
            ->get();

        // Compose per-account structure with running balance
        $accounts = [];
        foreach ($txns as $t) {
            $code = $t->subcode;
            if (!isset($accounts[$code])) {
                $op = $openings->get($code);
                $openingDr = (float) ($op->opening_dr ?? 0);
                $openingCr = (float) ($op->opening_cr ?? 0);
                $openingBal = $openingDr - $openingCr;
                $accounts[$code] = [
                    'sub_code' => $code,
                    'name' => $t->name ?? '',
                    'group_name' => $t->group_name ?? '',
                    'opening_dr' => $openingDr,
                    'opening_cr' => $openingCr,
                    'opening_balance' => $openingBal,
                    'transactions' => [],
                ];
            }
            $accounts[$code]['transactions'][] = [
                'vdate' => $t->vdate,
                'docid' => $t->docid,
                'vsno' => $t->vsno,
                'vtype' => $t->vtype,
                'vno' => $t->vno,
                'vprefix' => $t->vprefix,
                'narration' => $t->narration ?? '',
                'contrasub' => $t->contrasub ?? '',
                'chqno' => $t->chqno ?? '',
                'chqdate' => $t->chqdate ?? '',
                'amtdr' => (float) ($t->amtdr ?? 0),
                'amtcr' => (float) ($t->amtcr ?? 0),
            ];
        }

        // Sort accounts by name, compute running/closing balances
        $accounts = array_values($accounts);
        usort($accounts, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        foreach ($accounts as &$acc) {
            $running = $acc['opening_balance'];
            foreach ($acc['transactions'] as &$tx) {
                $running += $tx['amtdr'] - $tx['amtcr'];
                $tx['running_balance'] = $running;
            }
            unset($tx);
            $acc['closing_balance'] = $running;
            $acc['total_dr'] = array_sum(array_column($acc['transactions'], 'amtdr'));
            $acc['total_cr'] = array_sum(array_column($acc['transactions'], 'amtcr'));
        }
        unset($acc);

        return response()->json(['data' => $accounts]);
    }

    public function printGeneralLedger(Request $request)
    {
        $fromDate = $request->query('fromdate', $this->datemanage['mtd']['start']);
        $toDate = $request->query('todate', $this->ncurdate);
        $subcodes = $request->query('subcodes');

        try {
            $from = Carbon::parse($fromDate)->format('Y-m-d');
            $to = Carbon::parse($toDate)->format('Y-m-d');
        } catch (Exception $e) {
            $from = $this->datemanage['mtd']['start'];
            $to = $this->ncurdate;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $subcodes = $subcodes ? explode(',', $subcodes) : null;
        $propertyid = $this->propertyid;

        // Reuse the same composition logic
        $openingQuery = DB::table('ledger as l')
            // LEFT join for legacy VIEWLEDGER parity (BUG-QA-010).
            ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 'l.propertyid');
            })
            ->where('l.propertyid', $propertyid)
            ->where('l.vdate', '<', $from)
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            });

        if (!empty($subcodes)) {
            $openingQuery->whereIn('l.subcode', $subcodes);
        }

        $openings = $openingQuery
            ->select('s.sub_code', 's.name', 'a.group_name')
            ->selectRaw('SUM(l.amtdr) AS opening_dr')
            ->selectRaw('SUM(l.amtcr) AS opening_cr')
            ->groupBy('s.sub_code', 's.name', 'a.group_name')
            ->get()
            ->keyBy('sub_code');

        $txnQuery = DB::table('ledger as l')
            // LEFT join for legacy VIEWLEDGER parity (BUG-QA-010).
            ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 'l.propertyid');
            })
            ->where('l.propertyid', $propertyid)
            ->whereBetween('l.vdate', [$from, $to])
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            });

        if (!empty($subcodes)) {
            $txnQuery->whereIn('l.subcode', $subcodes);
        }

        $txns = $txnQuery
            ->select(
                'l.subcode',
                's.name',
                'a.group_name',
                'l.vdate',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vprefix',
                'l.narration',
                'l.contrasub',
                'l.chqno',
                'l.chqdate',
                'l.amtdr',
                'l.amtcr'
            )
            ->orderBy('s.name')
            ->orderBy('l.vdate')
            ->orderBy('l.docid')
            ->orderBy('l.vsno')
            ->get();

        $accounts = [];
        foreach ($txns as $t) {
            $code = $t->subcode;
            if (!isset($accounts[$code])) {
                $op = $openings->get($code);
                $openingDr = (float) ($op->opening_dr ?? 0);
                $openingCr = (float) ($op->opening_cr ?? 0);
                $accounts[$code] = [
                    'sub_code' => $code,
                    'name' => $t->name ?? '',
                    'group_name' => $t->group_name ?? '',
                    'opening_dr' => $openingDr,
                    'opening_cr' => $openingCr,
                    'opening_balance' => $openingDr - $openingCr,
                    'transactions' => [],
                ];
            }
            $accounts[$code]['transactions'][] = [
                'vdate' => $t->vdate,
                'docid' => $t->docid,
                'vsno' => $t->vsno,
                'vtype' => $t->vtype,
                'vno' => $t->vno,
                'vprefix' => $t->vprefix,
                'narration' => $t->narration ?? '',
                'contrasub' => $t->contrasub ?? '',
                'chqno' => $t->chqno ?? '',
                'chqdate' => $t->chqdate ?? '',
                'amtdr' => (float) ($t->amtdr ?? 0),
                'amtcr' => (float) ($t->amtcr ?? 0),
            ];
        }

        $accounts = array_values($accounts);
        usort($accounts, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        foreach ($accounts as &$acc) {
            $running = $acc['opening_balance'];
            foreach ($acc['transactions'] as &$tx) {
                $running += $tx['amtdr'] - $tx['amtcr'];
                $tx['running_balance'] = $running;
            }
            unset($tx);
            $acc['closing_balance'] = $running;
            $acc['total_dr'] = array_sum(array_column($acc['transactions'], 'amtdr'));
            $acc['total_cr'] = array_sum(array_column($acc['transactions'], 'amtcr'));
        }
        unset($acc);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'property.print.printgeneralledger',
            [
                'company' => $this->company,
                'accounts' => $accounts,
                'fromDate' => $from,
                'toDate' => $to,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->stream('general-ledger.pdf');
    }

    public function exportGeneralLedger(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $companyName = $this->company->comp_name ?? '';

        $export = new \App\Exports\GeneralLedgerExport(
            $request->fromdate,
            $request->todate,
            $this->propertyid,
            $companyName,
            $request->subcodes
        );

        return $export->download();
    }

    /**
     * Day Book (legacy `DayBook`) — chronological register of ALL voucher postings
     * in a date range, with optional vtype filter. Mirrors the legacy report:
     * every ledger row (vdate/vtype/vno/docid/narration/dr/cr) with account name,
     * ordered by date → docid → vsno. Read-only.
     */
    public function dayBook(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $fromdate = $this->datemanage['mtd']['start'];
        $todate = $this->ncurdate;

        $companyName = $this->company->comp_name ?? '';
        $reportDate = $this->ncurdate;

        return view('property.finance.daybook', compact('fromdate', 'todate', 'companyName', 'reportDate'));
    }

    public function dayBookVtypes(Request $request)
    {
        $propertyid = $this->propertyid;

        // Distinct voucher types present in ledger for this property (active rows only)
        $vtypes = DB::table('ledger as l')
            ->where('l.propertyid', $propertyid)
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            })
            ->select('l.vtype')
            ->distinct()
            ->orderBy('l.vtype')
            ->pluck('vtype');

        return response()->json(['data' => $vtypes]);
    }

    /**
     * Shared Day Book query — returns the flat posting list + totals.
     */
    private function dayBookRows(string $fromdate, string $todate, ?string $vtype = null)
    {
        $propertyid = $this->propertyid;

        $query = DB::table('ledger as l')
            // Legacy query was `VIEWLEDGER LEFT JOIN SUBGROUP` — must be LEFT so postings
            // whose subcode is empty/missing (e.g. HPOST advance legs when the property's
            // roomchrgdueac account is unconfigured) are still listed and the Day/Journal
            // Book totals reconcile to the raw ledger (BUG-QA-010: INNER JOIN silently
            // dropped 683 rows / ₹7.02M dr across 41 properties in 2026).
            ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 'l.propertyid');
            })
            ->where('l.propertyid', $propertyid)
            ->whereBetween('l.vdate', [$fromdate, $todate])
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            });

        if (! empty($vtype)) {
            $query->where('l.vtype', $vtype);
        }

        $rows = $query
            ->select(
                'l.vdate',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vprefix',
                'l.narration',
                'l.contrasub',
                'l.chqno',
                'l.chqdate',
                'l.amtdr',
                'l.amtcr',
                'l.subcode',
                's.name as account_name',
                'a.group_name'
            )
            ->orderBy('l.vdate')
            ->orderBy('l.docid')
            ->orderBy('l.vsno')
            ->get();

        $totalDr = 0;
        $totalCr = 0;
        foreach ($rows as $r) {
            $totalDr += (float) $r->amtdr;
            $totalCr += (float) $r->amtcr;
        }

        return [
            'rows' => $rows,
            'total_dr' => $totalDr,
            'total_cr' => $totalCr,
        ];
    }

    public function dayBookQuery(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $vtype = $request->input('vtype', '');

        $result = $this->dayBookRows($fromdate, $todate, $vtype ?: null);

        return response()->json([
            'data' => $result['rows'],
            'total_dr' => $result['total_dr'],
            'total_cr' => $result['total_cr'],
        ]);
    }

    public function printDayBook(Request $request)
    {
        $fromDate = $request->query('fromdate', $this->datemanage['mtd']['start']);
        $toDate = $request->query('todate', $this->ncurdate);
        $vtype = $request->query('vtype');

        try {
            $from = Carbon::parse($fromDate)->format('Y-m-d');
            $to = Carbon::parse($toDate)->format('Y-m-d');
        } catch (Exception $e) {
            $from = $this->datemanage['mtd']['start'];
            $to = $this->ncurdate;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $result = $this->dayBookRows($from, $to, $vtype ?: null);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'property.print.printdaybook',
            [
                'company' => $this->company,
                'rows' => $result['rows'],
                'totalDr' => $result['total_dr'],
                'totalCr' => $result['total_cr'],
                'fromDate' => $from,
                'toDate' => $to,
                'vtype' => $vtype,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->stream('day-book.pdf');
    }

    public function exportDayBook(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $companyName = $this->company->comp_name ?? '';

        $export = new \App\Exports\DayBookExport(
            $request->fromdate,
            $request->todate,
            $this->propertyid,
            $companyName,
            $request->input('vtype', '')
        );

        return $export->download();
    }

    /**
     * Journal Book (legacy `JournalBook`) — ledger postings for a single voucher
     * type in a date range (default 'JV' = Journal). Legacy query:
     *   SELECT V_DATE,credit,debit,v_type,v_no,v_add,CHQ_NO,CHQ_DATE,NARR,NAME,
     *          MNARR,V_SNO,DOCID ... FROM VIEWLEDGER LEFT JOIN SUBGROUP ...
     *   WHERE V_date BETWEEN ... AND V_TYPE='<type>' ... ORDER BY V_DATE,V_TYPE,V_NO,V_ADD,V_SNO
     * Read-only; reuses Trail Balance permission 111211.
     */
    public function journalBook(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $fromdate = $this->datemanage['mtd']['start'];
        $todate = $this->ncurdate;

        $companyName = $this->company->comp_name ?? '';
        $reportDate = $this->ncurdate;

        return view('property.finance.journalbook', compact('fromdate', 'todate', 'companyName', 'reportDate'));
    }

    public function journalBookVtypes(Request $request)
    {
        $propertyid = $this->propertyid;

        // Distinct voucher types present in ledger for this property (active rows only)
        $vtypes = DB::table('ledger as l')
            ->where('l.propertyid', $propertyid)
            ->where(function ($q) {
                $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
            })
            ->select('l.vtype')
            ->distinct()
            ->orderBy('l.vtype')
            ->pluck('vtype');

        return response()->json(['data' => $vtypes]);
    }

    public function journalBookQuery(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $vtype = $request->input('vtype', 'JV');

        $result = $this->dayBookRows($fromdate, $todate, $vtype ?: null);

        return response()->json([
            'data' => $result['rows'],
            'total_dr' => $result['total_dr'],
            'total_cr' => $result['total_cr'],
        ]);
    }

    public function printJournalBook(Request $request)
    {
        $fromDate = $request->query('fromdate', $this->datemanage['mtd']['start']);
        $toDate = $request->query('todate', $this->ncurdate);
        $vtype = $request->query('vtype', 'JV');

        try {
            $from = Carbon::parse($fromDate)->format('Y-m-d');
            $to = Carbon::parse($toDate)->format('Y-m-d');
        } catch (Exception $e) {
            $from = $this->datemanage['mtd']['start'];
            $to = $this->ncurdate;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $result = $this->dayBookRows($from, $to, $vtype ?: null);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'property.print.printjournalbook',
            [
                'company' => $this->company,
                'rows' => $result['rows'],
                'totalDr' => $result['total_dr'],
                'totalCr' => $result['total_cr'],
                'fromDate' => $from,
                'toDate' => $to,
                'vtype' => $vtype,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->stream('journal-book.pdf');
    }

    public function exportJournalBook(Request $request)
    {
        $request->validate([
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $companyName = $this->company->comp_name ?? '';

        $export = new \App\Exports\JournalBookExport(
            $request->fromdate,
            $request->todate,
            $this->propertyid,
            $companyName,
            $request->input('vtype', 'JV')
        );

        return $export->download();
    }

    /**
     * Cash Book / Bank Book (legacy `CashBook`/`BankBook`) — ledger filtered to
     * accounts whose acgroup nature is 'Cash' (CASH-IN-HAND) or 'Bank' (BANK
     * ACCOUNTS / BANK OD-AC). Per-account opening/running/closing balance with
     * optional account filter, PDF print, Excel export. Read-only.
     */
    public function cashBankBook(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $fromdate = $this->datemanage['mtd']['start'];
        $todate = $this->ncurdate;

        $companyName = $this->company->comp_name ?? '';
        $reportDate = $this->ncurdate;

        return view('property.finance.cashbankbook', compact('fromdate', 'todate', 'companyName', 'reportDate'));
    }

    public function cashBankBookAccounts(Request $request)
    {
        $request->validate([
            'book' => 'required|in:Cash,Bank',
        ]);

        $propertyid = $this->propertyid;
        $book = $request->book;

        $accounts = DB::table('subgroup as s')
            ->leftJoin('acgroup as a', function ($join) {
                // group_code is NOT globally unique (shared across properties) — scope to the
                // property's own acgroup row or the join multiplies rows (BUG-044).
                $join->on('s.group_code', '=', 'a.group_code')
                    ->on('a.propertyid', '=', 's.propertyid');
            })
            ->where('s.propertyid', $propertyid)
            ->where('a.nature', $book)
            ->where(function ($q) {
                $q->where('s.activeyn', 'Y')->orWhereNull('s.activeyn');
            })
            ->select('s.sub_code', 's.name', 'a.group_name')
            ->orderBy('s.name')
            ->get();

        return response()->json(['data' => $accounts]);
    }

    /**
     * Shared Cash/Bank Book query — per-account structure with opening/running/closing
     * balance, filtered by acgroup nature ('Cash' or 'Bank').
     */
    private function cashBankBookRows(string $book, string $fromdate, string $todate, ?array $subcodes = null)
    {
        $propertyid = $this->propertyid;

        $base = function () use ($book, $propertyid, $fromdate, $subcodes) {
            $q = DB::table('ledger as l')
                // LEFT join for parity with the legacy VIEWLEDGER query (BUG-QA-010):
                // rows without a subgroup row must not silently disappear from the book.
                ->leftJoin('subgroup as s', 's.sub_code', '=', 'l.subcode')
                ->leftJoin('acgroup as a', function ($join) {
                    // group_code is NOT globally unique (shared across properties) — scope to the
                    // property's own acgroup row or the join multiplies rows (BUG-044).
                    $join->on('s.group_code', '=', 'a.group_code')
                        ->on('a.propertyid', '=', 'l.propertyid');
                })
                ->where('l.propertyid', $propertyid)
                ->where('a.nature', $book)
                ->where(function ($q) {
                    $q->whereNull('l.delflag')->orWhere('l.delflag', '!=', 'Y');
                });

            if (! empty($subcodes) && is_array($subcodes)) {
                $q->whereIn('l.subcode', $subcodes);
            }

            return $q;
        };

        // Opening balances: all activity before fromdate for the book's accounts
        $openings = $base()
            ->where('l.vdate', '<', $fromdate)
            ->select('s.sub_code', 's.name', 'a.group_name')
            ->selectRaw('SUM(l.amtdr) AS opening_dr')
            ->selectRaw('SUM(l.amtcr) AS opening_cr')
            ->groupBy('s.sub_code', 's.name', 'a.group_name')
            ->get()
            ->keyBy('sub_code');

        // Transactions in period
        $txns = $base()
            ->whereBetween('l.vdate', [$fromdate, $todate])
            ->select(
                'l.subcode',
                's.name',
                'a.group_name',
                'l.vdate',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vprefix',
                'l.narration',
                'l.contrasub',
                'l.chqno',
                'l.chqdate',
                'l.amtdr',
                'l.amtcr'
            )
            ->orderBy('s.name')
            ->orderBy('l.vdate')
            ->orderBy('l.docid')
            ->orderBy('l.vsno')
            ->get();

        // Compose per-account structure with running balance
        $accounts = [];
        foreach ($txns as $t) {
            $code = $t->subcode;
            if (! isset($accounts[$code])) {
                $op = $openings->get($code);
                $openingDr = (float) ($op->opening_dr ?? 0);
                $openingCr = (float) ($op->opening_cr ?? 0);
                $accounts[$code] = [
                    'sub_code' => $code,
                    'name' => $t->name ?? '',
                    'group_name' => $t->group_name ?? '',
                    'opening_dr' => $openingDr,
                    'opening_cr' => $openingCr,
                    'opening_balance' => $openingDr - $openingCr,
                    'transactions' => [],
                ];
            }
            $accounts[$code]['transactions'][] = [
                'vdate' => $t->vdate,
                'docid' => $t->docid,
                'vsno' => $t->vsno,
                'vtype' => $t->vtype,
                'vno' => $t->vno,
                'vprefix' => $t->vprefix,
                'narration' => $t->narration ?? '',
                'contrasub' => $t->contrasub ?? '',
                'chqno' => $t->chqno ?? '',
                'chqdate' => $t->chqdate ?? '',
                'amtdr' => (float) ($t->amtdr ?? 0),
                'amtcr' => (float) ($t->amtcr ?? 0),
            ];
        }

        $accounts = array_values($accounts);
        usort($accounts, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        foreach ($accounts as &$acc) {
            $running = $acc['opening_balance'];
            foreach ($acc['transactions'] as &$tx) {
                $running += $tx['amtdr'] - $tx['amtcr'];
                $tx['running_balance'] = $running;
            }
            unset($tx);
            $acc['closing_balance'] = $running;
            $acc['total_dr'] = array_sum(array_column($acc['transactions'], 'amtdr'));
            $acc['total_cr'] = array_sum(array_column($acc['transactions'], 'amtcr'));
        }
        unset($acc);

        return $accounts;
    }

    public function cashBankBookQuery(Request $request)
    {
        $request->validate([
            'book' => 'required|in:Cash,Bank',
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $book = $request->book;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $subcodes = $request->subcodes; // optional array of sub_code filters

        $accounts = $this->cashBankBookRows($book, $fromdate, $todate, $subcodes);

        return response()->json(['data' => $accounts]);
    }

    public function printCashBankBook(Request $request)
    {
        $book = $request->query('book', 'Cash');
        if (! in_array($book, ['Cash', 'Bank'], true)) {
            $book = 'Cash';
        }
        $fromDate = $request->query('fromdate', $this->datemanage['mtd']['start']);
        $toDate = $request->query('todate', $this->ncurdate);
        $subcodes = $request->query('subcodes');

        try {
            $from = Carbon::parse($fromDate)->format('Y-m-d');
            $to = Carbon::parse($toDate)->format('Y-m-d');
        } catch (Exception $e) {
            $from = $this->datemanage['mtd']['start'];
            $to = $this->ncurdate;
        }

        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        $subcodes = $subcodes ? explode(',', $subcodes) : null;
        $accounts = $this->cashBankBookRows($book, $from, $to, $subcodes);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'property.print.printcashbankbook',
            [
                'company' => $this->company,
                'accounts' => $accounts,
                'book' => $book,
                'fromDate' => $from,
                'toDate' => $to,
            ]
        )->setPaper('a4', 'landscape');

        return $pdf->stream(strtolower($book) . '-book.pdf');
    }

    public function exportCashBankBook(Request $request)
    {
        $request->validate([
            'book' => 'required|in:Cash,Bank',
            'fromdate' => 'required|date',
            'todate'   => 'required|date|after_or_equal:fromdate',
        ]);

        $companyName = $this->company->comp_name ?? '';

        $export = new \App\Exports\CashBankBookExport(
            $request->book,
            $request->fromdate,
            $request->todate,
            $this->propertyid,
            $companyName,
            $request->subcodes
        );

        return $export->download();
    }

    public function trailbalance(Request $request)
    {
        $permission = revokeopen(111211);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        return view('property.finance.trailbalance');
    }

    public function trialmainquery(Request $request)
    {
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $openingbalance = $request->openingbalance;
        $allproperties = $request->allproperties;

        if ($openingbalance != 'checked') {
            $data = Ledger::select(
                'subgroup.name',
                'ledger.docid',
                'ledger.vtype',
                'ledger.vdate',
                DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                'ledger.subcode'
            )
                ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                ->whereIn('ledger.propertyid', $allproperties)
                ->whereBetween('ledger.vdate', [$fromdate, $todate])
                ->groupBy('ledger.subcode')
                ->orderBy('subgroup.name')
                ->get();
        } else {
            $data = Ledger::select(
                'subgroup.name',
                'ledger.docid',
                'ledger.vtype',
                'ledger.vdate',
                DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                'ledger.subcode'
            )
                ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                ->whereIn('ledger.propertyid', $allproperties)
                ->where('vdate', '<=', $todate)
                ->groupBy('ledger.subcode')
                ->orderBy('subgroup.name')
                ->get();
        }

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No Data Found'
            ]);
        } else {
            return response()->json($data);
        }
    }

    public function monthwisetrialfetch(Request $request)
    {
        $sub_code = $request->sub_code;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $openingbalanceFlag = $request->openingbalance;
        $allproperties = $request->allproperties;
        $openingBalanceEnabled = ($openingbalanceFlag !== 'not checked');

        $data = Ledger::selectRaw("DATE_FORMAT(vdate, '%M %Y') AS month_year, DATE_FORMAT(vdate, '%m') AS month_number, subcode, vprefix")
            ->selectRaw('COUNT(*) AS total_entries')
            ->selectRaw('SUM(amtdr) AS totalamtdr')
            ->selectRaw('SUM(amtcr) AS totalamtcr')
            ->selectRaw('DATE_FORMAT(vdate, "%Y") AS year')
            ->whereIn('propertyid', $allproperties)
            ->whereBetween('ledger.vdate', [$fromdate, $todate])
            ->where('subcode', $sub_code)
            ->groupByRaw("DATE_FORMAT(vdate, '%Y-%m')")
            ->orderBy('vdate')
            ->get();

        $openingbalance = 0.00;
        if ($openingBalanceEnabled) {
            $openingbalance = Ledger::whereIn('propertyid', $allproperties)
                ->where('vdate', '<', $fromdate)
                ->where('subcode', $sub_code)
                ->selectRaw('SUM(amtdr) - SUM(amtcr) AS balance')
                ->value('balance') ?? 0.00;
        }

        return response()->json([
            'data' => $data,
            'openingbalance' => $openingbalance
        ]);
    }

    public function monthrowfetch(Request $request)
    {
        $sub_code = $request->sub_code;
        $month_number = $request->month_number;
        $vprefix = $request->vprefix;
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $openingbalanceFlag = $request->openingbalance;
        $openingBalanceEnabled = ($openingbalanceFlag !== 'not checked');

        $condition = $request->condition;
        $allproperties = $request->allproperties;

        $datatmp = Ledger::select('vdate', 'docid', 'vno', 'narration', 'amtdr', 'amtcr', 'vtype')
            ->whereIn('propertyid', $allproperties)
            ->where('subcode', $sub_code)
            // ->whereYear('vdate', $vprefix)
            ->where('vprefix', $vprefix)
            // ->where('ledger.vdate', '<=', $todate)
            ->whereBetween('ledger.vdate', [$fromdate, $todate])
            ->orderBy('vdate')
            ->orderBy('sn');

        if ($condition == 1) {
            $data = $datatmp->get();
        } else {
            $data = $datatmp->whereMonth('vdate', $month_number)->get();
        }

        $opening_balance = 0;
        if ($openingBalanceEnabled) {
            $opening_balance = Ledger::whereIn('propertyid', $allproperties)
                ->where('subcode', $sub_code)
                ->where('vdate', '<', $fromdate)
                ->selectRaw('SUM(amtdr) - SUM(amtcr) as balance')
                ->value('balance') ?? 0;
        }

        // return $data;

        return response()->json([
            'data' => $data,
            'opening_balance' => $opening_balance
        ]);
    }

    public function profitloss(Request $request)
    {
        // $permission = revokeopen(111213);
        // if (is_null($permission) || $permission->view == 0) {
        //     return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        // }
        return view('property.finance.profitloss');
    }

    public function profitlossmainquery(Request $request)
    {
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $detailed = $request->detailed ?? false;
        $allproperties = $request->allproperties;

        $saleGroupCodes = array_map(function ($prop) {
            return "25{$prop}";
        }, $allproperties);
        $closingstockGroupCodes = array_map(function ($prop) {
            return "6{$prop}";
        }, $allproperties);
        $openingstockGroupCodes = array_map(function ($prop) {
            return "20{$prop}";
        }, $allproperties);
        $purchaseGroupCodes = array_map(function ($prop) {
            return "23{$prop}";
        }, $allproperties);
        $directexpensesGroupCodes = array_map(function ($prop) {
            return "10{$prop}";
        }, $allproperties);
        $indirectexpensesGroupCodes = array_map(function ($prop) {
            return "14{$prop}";
        }, $allproperties);
        $indirectincomeGroupCodes = array_map(function ($prop) {
            return "15{$prop}";
        }, $allproperties);
        $directincomeGroupCodes = array_map(function ($prop) {
            return "11{$prop}";
        }, $allproperties);

        $sale = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $saleGroupCodes)
            ->first();

        $closingstock = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $closingstockGroupCodes)
            ->first();

        $openingstock = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $openingstockGroupCodes)
            ->first();

        $purchaseaccount = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $purchaseGroupCodes)
            ->first();

        $directexpenses = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $directexpensesGroupCodes)
            ->first();

        $indirectexpenses = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $indirectexpensesGroupCodes)
            ->first();

        $indirectincome = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $indirectincomeGroupCodes)
            ->first();

        $directincome = ACGroup::whereIn('propertyid', $allproperties)
            ->whereIn('group_code', $directincomeGroupCodes)
            ->first();

        $detailedGroups = [
            'sale',
            'closingstock',
            'openingstock',
            'purchaseaccount',
            'directexpenses',
            'directincome',
            'indirectexpenses',
            'indirectincome',
        ];

        $groupCodesMap = [
            'sale' => $saleGroupCodes,
            'closingstock' => $closingstockGroupCodes,
            'openingstock' => $openingstockGroupCodes,
            'purchaseaccount' => $purchaseGroupCodes,
            'directexpenses' => $directexpensesGroupCodes,
            'indirectexpenses' => $indirectexpensesGroupCodes,
            'indirectincome' => $indirectincomeGroupCodes,
            'directincome' => $directincomeGroupCodes,
        ];

        $groupsac = [
            'sale' => $sale,
            'closingstock' => $closingstock,
            'openingstock' => $openingstock,
            'purchaseaccount' => $purchaseaccount,
            'directexpenses' => $directexpenses,
            'indirectexpenses' => $indirectexpenses,
            'indirectincome' => $indirectincome,
            'directincome' => $directincome,
        ];

        Log::info("groupac: " . json_encode($groupsac, JSON_PRETTY_PRINT));
        Log::info("groupCodesMap: " . json_encode($groupCodesMap, JSON_PRETTY_PRINT));

        foreach ($groupsac as $key => $group) {
            $accountrows = Ledger::select(
                'acgroup.maingroupname as name',
                'acgroup.group_code',
                'acgroup.maingroupcode',
                DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                'acgroup.undergroup'
            )
                ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                ->whereIn('ledger.propertyid', $allproperties)
                ->whereBetween('ledger.vdate', [$fromdate, $todate])
                ->whereIn('acgroup.group_code', $groupCodesMap[$key])
                ->groupBy(
                    'acgroup.maingroupcode',
                    'acgroup.maingroupname',
                    'acgroup.group_code'
                )
                ->orderBy('acgroup.maingroupname')
                ->get();

            $groupsac[$key . '_rows'] = $accountrows;

            if ($detailed && in_array($key, $detailedGroups)) {
                $subgroups = Ledger::select([
                    DB::raw('
                        CASE 
                            WHEN acgroup.undergroup = "Y" 
                            THEN subgroup.name 
                            ELSE acgroup.group_name 
                        END AS name
                    '),
                    DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                    'ledger.subcode',
                    'acgroup.undergroup',
                    'acgroup.group_code as acgroupcode',
                    'ledger.vdate',
                ])
                    ->selectRaw('DATE_FORMAT(ledger.vdate, "%Y") AS year')
                    ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                    ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                    ->whereIn('ledger.propertyid', $allproperties)
                    ->whereBetween('ledger.vdate', [$fromdate, $todate])
                    // ->whereIn('subgroup.group_code', $groupCodesMap[$key])
                    ->whereIn('acgroup.maingroupcode', $accountrows->pluck('maingroupcode')->unique()->toArray())
                    ->groupBy(
                        'ledger.subcode',
                        'acgroup.undergroup',
                        'acgroup.group_code',
                        'acgroup.group_name',
                        'subgroup.name'
                    )
                    ->orderBy('name')
                    ->get();

                $groupsac[$key . '_subgroups'] = $subgroups;
            }
        }

        $data = [
            'groupsac' => $groupsac,
            'detailed' => $detailed
        ];

        return response()->json($data);
    }

    public function profitlosssecondqueryhf(Request $request)
    {
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $detailed = $request->detailed ?? false;
        $allproperties = $request->allproperties;
        $groupcode = $request->group_code;

        $subgroups = Ledger::select([
            'subgroup.name',
            DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
            'ledger.subcode',
            'acgroup.undergroup',
            'acgroup.group_code as acgroupcode',
            'ledger.vdate',
        ])
            ->selectRaw('DATE_FORMAT(ledger.vdate, "%Y") AS year')
            ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
            ->join('acgroup', function ($join) {
                $join->on('acgroup.group_code', '=', 'subgroup.group_code')
                    ->on('acgroup.propertyid', '=', 'ledger.propertyid');
            })
            ->whereIn('ledger.propertyid', $allproperties)
            ->whereBetween('ledger.vdate', [$fromdate, $todate])
            ->where('acgroup.group_code', $groupcode)
            ->groupBy(
                'ledger.subcode',
                'acgroup.undergroup',
                'acgroup.group_code',
                'acgroup.group_name',
                'subgroup.name'
            )
            ->orderBy('name')
            ->get();

        return response()->json([
            'subgroups' => $subgroups
        ]);
    }

    public function balancesheet(Request $request)
    {
        return view('property.finance.balancesheet');
    }

    public function balancesheetmainquery(Request $request)
    {
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $detailed = $request->detailed ?? false;
        $allproperties = $request->allproperties;

        // Right Side: Assets (using maingroupcode 50, 60, 40)
        $investments = ACGroup::whereIn('propertyid', $allproperties)
            ->where('maingroupcode', '50')
            ->first();

        $currentassets = ACGroup::whereIn('propertyid', $allproperties)
            ->where('maingroupcode', '60')
            ->first();

        $fixedassets = ACGroup::whereIn('propertyid', $allproperties)
            ->where('maingroupcode', '40')
            ->first();

        // Left Side: All other accounts EXCEPT profit/loss codes and right-side asset codes
        // Build excluded group codes for all properties (same pattern as profitloss)
        $excludedGroupCodes = [];

        // P&L codes to exclude
        $excludedPrefixes = ['25', '23', '10', '14', '15', '11', '6', '20']; // Sale, Purchase, Direct Exp, Indirect Exp, Indirect Inc, Direct Inc, Closing Stock, Opening Stock

        // Asset codes to exclude (using maingroupcode 50, 60, 40)
        // Note: These use maingroupcode, not group_code pattern

        foreach ($allproperties as $prop) {
            foreach ($excludedPrefixes as $prefix) {
                $excludedGroupCodes[] = "{$prefix}{$prop}";
            }
        }

        // Get all left side groups (excluding P&L group_codes)
        $leftSideGroups = ACGroup::whereIn('propertyid', $allproperties)
            ->whereNotIn('group_code', $excludedGroupCodes)
            ->whereNotIn('maingroupcode', ['50', '60', '40']) // Also exclude asset maingroupcodes
            ->get();

        // When "Detailed" is checked, return subgroup totals for all displayed groups
        // (right-side assets + all left-side maingroupcodes).
        $detailedGroups = ['investments', 'currentassets', 'fixedassets'];

        // Right Side groups
        $rightSideGroupsAc = [
            'investments' => $investments,
            'currentassets' => $currentassets,
            'fixedassets' => $fixedassets,
        ];

        $groupsac = [];

        // Process Right Side (Assets) with maingroupcode
        foreach ($rightSideGroupsAc as $key => $group) {
            $groupsac[$key] = $group;

            if (!$group) {
                $groupsac[$key . '_rows'] = collect();
                continue;
            }

            $accountrows = Ledger::select(
                'acgroup.maingroupname as name',
                'acgroup.group_code',
                'acgroup.maingroupcode',
                DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                'acgroup.undergroup'
            )
                ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                ->whereIn('ledger.propertyid', $allproperties)
                ->whereBetween('ledger.vdate', [$fromdate, $todate])
                ->where('acgroup.maingroupcode', $group->maingroupcode)
                ->groupBy('acgroup.maingroupcode', 'acgroup.maingroupname', 'acgroup.group_code')
                ->orderBy('acgroup.maingroupname')
                ->get();

            $groupsac[$key . '_rows'] = $accountrows;

            // Fetch subgroup details if detailed view is enabled
            if ($detailed && in_array($key, $detailedGroups)) {
                $subgroups = Ledger::select([
                    DB::raw('
                        CASE 
                            WHEN acgroup.undergroup = "Y" 
                            THEN subgroup.name 
                            ELSE acgroup.group_name 
                        END AS name
                    '),
                    DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                    'ledger.subcode',
                    'acgroup.undergroup',
                    'acgroup.group_code as acgroupcode',
                ])
                    ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                    ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                    ->whereIn('ledger.propertyid', $allproperties)
                    ->whereBetween('ledger.vdate', [$fromdate, $todate])
                    ->where('acgroup.maingroupcode', $group->maingroupcode)
                    ->groupBy('ledger.subcode', 'acgroup.undergroup', 'acgroup.group_code', 'acgroup.group_name', 'subgroup.name')
                    ->orderBy('name')
                    ->get();

                $groupsac[$key . '_subgroups'] = $subgroups;
            }
        }

        // Process Left Side (Liabilities & Capital) - all groups except excluded ones
        $leftSideRows = [];
        $leftSideGroupsMeta = []; // keyed by maingroupcode
        foreach ($leftSideGroups as $leftGroup) {
            $accountrows = Ledger::select(
                'acgroup.maingroupname as name',
                'acgroup.group_code',
                'acgroup.maingroupcode',
                DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                'acgroup.undergroup'
            )
                ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                ->whereIn('ledger.propertyid', $allproperties)
                ->whereBetween('ledger.vdate', [$fromdate, $todate])
                ->where('acgroup.maingroupcode', $leftGroup->maingroupcode)
                ->groupBy('acgroup.maingroupcode', 'acgroup.maingroupname', 'acgroup.group_code')
                ->orderBy('acgroup.maingroupname')
                ->get();

            if ($accountrows->isNotEmpty()) {
                $leftSideRows = array_merge($leftSideRows, $accountrows->toArray());
            }

            // Capture meta once per maingroupcode (across properties)
            if (!isset($leftSideGroupsMeta[$leftGroup->maingroupcode])) {
                $leftSideGroupsMeta[$leftGroup->maingroupcode] = [
                    'maingroupcode' => $leftGroup->maingroupcode,
                    'name' => $leftGroup->maingroupname ?? $leftGroup->group_name ?? null,
                ];
            }
        }

        $groupsac['leftside_rows'] = collect($leftSideRows);

        if ($detailed) {
            $leftSideSubgroupsByMain = [];

            foreach ($leftSideGroupsMeta as $meta) {
                $mainCode = $meta['maingroupcode'];

                $subgroups = Ledger::select([
                    DB::raw('
                        CASE 
                            WHEN acgroup.undergroup = "Y" 
                            THEN subgroup.name 
                            ELSE acgroup.group_name 
                        END AS name
                    '),
                    DB::raw('SUM(ledger.amtdr) - SUM(ledger.amtcr) AS balance'),
                    'ledger.subcode',
                    'acgroup.undergroup',
                    'acgroup.group_code as acgroupcode',
                ])
                    ->leftJoin('subgroup', 'subgroup.sub_code', '=', 'ledger.subcode')
                    ->leftJoin('acgroup', 'acgroup.group_code', '=', 'subgroup.group_code')
                    ->whereIn('ledger.propertyid', $allproperties)
                    ->whereBetween('ledger.vdate', [$fromdate, $todate])
                    ->where('acgroup.maingroupcode', $mainCode)
                    ->groupBy('ledger.subcode', 'acgroup.undergroup', 'acgroup.group_code', 'acgroup.group_name', 'subgroup.name')
                    ->orderBy('name')
                    ->get();

                $leftSideSubgroupsByMain[$mainCode] = $subgroups;
            }

            $groupsac['leftside_groups_meta'] = array_values($leftSideGroupsMeta);
            $groupsac['leftside_subgroups_by_maingroupcode'] = $leftSideSubgroupsByMain;
        }

        $data = [
            'groupsac' => $groupsac,
            'detailed' => $detailed
        ];

        return response()->json($data);
    }

    public function tdsreport(Request $request)
    {
        $permission = revokeopen(111214);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $company = $this->company;

        // Combine address1 and address2
        $fullAddress = trim(($company->address1 ?? '') . ' ' . ($company->address2 ?? ''));

        $statename = DB::table('states')
            ->where('propertyid', $this->propertyid)
            ->where('state_code', $company->state_code)
            ->value('name');

        // Logo path logic
        $logoPath = null;
        $logoBase64 = null;
        $logoMimeType = null;
        if (!empty($company->logo) && Storage::disk('public')->exists('admin/property_logo/' . $company->logo)) {
            $logoPath = storage_path('app/public/admin/property_logo/' . $company->logo);
            if (file_exists($logoPath)) {
                $imageData = file_get_contents($logoPath);
                $logoBase64 = base64_encode($imageData);
                // Detect MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $logoMimeType = finfo_file($finfo, $logoPath);
                finfo_close($finfo);
            }
        }

        return view('property.finance.tdsreport', [
            'company' => $company,
            'statename' => $statename,
            'fulladdress' => $fullAddress,
            'logoPath' => $logoPath,
            'logoBase64' => $logoBase64,
            'logoMimeType' => $logoMimeType
        ]);
    }

    public function tdsreportdata(Request $request)
    {
        $fromdate = $request->fromdate;
        $todate = $request->todate;
        $allproperties = $request->allproperties ?? [$this->propertyid];

        // Updated query as per sir's requirement - using ledger_tds table
        $tdsData = DB::table('ledger_tds as T')
            ->select([
                'T.docid',
                'T.vdate',
                'T.onamt',
                'T.tds',
                'T.tdsamt',
                'S.name as PartyName'
            ])
            ->join('subgroup as S', 'T.tdsdrcode', '=', 'S.sub_code')
            ->whereIn('T.propertyid', $allproperties)
            ->whereBetween('T.vdate', [$fromdate, $todate])
            ->orderBy('T.vdate')
            ->orderBy('T.docid')
            ->get();

        if ($tdsData->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No TDS Data Found'
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => $tdsData,
            'fromdate' => $fromdate,
            'todate' => $todate
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // MISSING REPORTS — Aging, DueList, GuestPayments (added by AI migration)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * Aging Report — Debtors (summary: party-wise outstanding with aging buckets)
     * Legacy: AgingDr — groups by SubGroup where Nature='Customer', calculates days outstanding
     * Buckets: 0-30, 31-60, 61-90, 91-180, 180+
     */
    public function agingDr(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.agingdr', compact('propertyid', 'todate'));
    }

    public function agingDrFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->input('todate', date('Y-m-d'));
        $groupCode = $request->input('group_code');

        $query = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')
                     ->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('l.propertyid', $propertyid)
            ->where('sg.nature', 'Customer')
            ->where('l.vdate', '<=', $todate)
            ->select(
                'sg.subcode',
                'sg.name',
                'sg.add1',
                'sg.phoneo',
                'sg.email',
                DB::raw('SUM(l.amtdr) as total_dr'),
                DB::raw('SUM(l.amtcr) as total_cr'),
                DB::raw('GREATEST(SUM(l.amtdr) - SUM(l.amtcr), 0) as outstanding'),
                DB::raw('DATEDIFF(?, MAX(l.vdate)) as days_outstanding'),
                DB::raw('MAX(l.vdate) as last_vdate')
            )
            ->addBinding([$todate], 'select')
            ->groupBy('sg.subcode', 'sg.name', 'sg.add1', 'sg.phoneo', 'sg.email');

        if ($groupCode) {
            $query->where('sg.group_code', $groupCode);
        }

        $rows = $query->get()->filter(function ($row) {
            return $row->outstanding > 0;
        })->values();

        $data = $rows->map(function ($row) use ($todate) {
            $days = $row->days_outstanding ?? 0;
            $row->bucket_0_30 = 0;
            $row->bucket_31_60 = 0;
            $row->bucket_61_90 = 0;
            $row->bucket_91_180 = 0;
            $row->bucket_180_plus = 0;

            if ($days <= 30) $row->bucket_0_30 = $row->outstanding;
            elseif ($days <= 60) $row->bucket_31_60 = $row->outstanding;
            elseif ($days <= 90) $row->bucket_61_90 = $row->outstanding;
            elseif ($days <= 180) $row->bucket_91_180 = $row->outstanding;
            else $row->bucket_180_plus = $row->outstanding;

            return $row;
        });

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json([
            'success' => true,
            'data' => $data,
            'comp' => $comp,
            'todate' => $todate,
            'total' => $data->sum('outstanding'),
        ]);
    }

    /**
     * Aging Report — Creditors
     */
    public function agingCr(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.agingcr', compact('propertyid', 'todate'));
    }

    public function agingCrFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->input('todate', date('Y-m-d'));
        $groupCode = $request->input('group_code');

        $query = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')
                     ->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('l.propertyid', $propertyid)
            ->where('sg.nature', 'Supplier')
            ->where('l.vdate', '<=', $todate)
            ->select(
                'sg.subcode',
                'sg.name',
                'sg.add1',
                'sg.phoneo',
                'sg.email',
                DB::raw('SUM(l.amtdr) as total_dr'),
                DB::raw('SUM(l.amtcr) as total_cr'),
                DB::raw('GREATEST(SUM(l.amtcr) - SUM(l.amtdr), 0) as outstanding'),
                DB::raw('DATEDIFF(?, MAX(l.vdate)) as days_outstanding'),
                DB::raw('MAX(l.vdate) as last_vdate')
            )
            ->addBinding([$todate], 'select')
            ->groupBy('sg.subcode', 'sg.name', 'sg.add1', 'sg.phoneo', 'sg.email');

        if ($groupCode) {
            $query->where('sg.group_code', $groupCode);
        }

        $rows = $query->get()->filter(function ($row) {
            return $row->outstanding > 0;
        })->values();

        $data = $rows->map(function ($row) use ($todate) {
            $days = $row->days_outstanding ?? 0;
            $row->bucket_0_30 = 0;
            $row->bucket_31_60 = 0;
            $row->bucket_61_90 = 0;
            $row->bucket_91_180 = 0;
            $row->bucket_180_plus = 0;

            if ($days <= 30) $row->bucket_0_30 = $row->outstanding;
            elseif ($days <= 60) $row->bucket_31_60 = $row->outstanding;
            elseif ($days <= 90) $row->bucket_61_90 = $row->outstanding;
            elseif ($days <= 180) $row->bucket_91_180 = $row->outstanding;
            else $row->bucket_180_plus = $row->outstanding;

            return $row;
        });

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json([
            'success' => true,
            'data' => $data,
            'comp' => $comp,
            'todate' => $todate,
            'total' => $data->sum('outstanding'),
        ]);
    }

    /**
     * Aging Report — Debtors (detailed with per-transaction bucket allocation)
     */
    public function agingRepDr(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.agingrepdr', compact('propertyid', 'todate'));
    }

    public function agingRepDrFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->input('todate', date('Y-m-d'));

        $rows = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')
                     ->where('sg.propertyid', $propertyid);
            })
            ->where('l.propertyid', $propertyid)
            ->where('sg.nature', 'Customer')
            ->where('l.vdate', '<=', $todate)
            ->whereRaw('(l.amtdr - l.amtcr) > 0')
            ->select(
                'sg.subcode',
                'sg.name',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vdate',
                'l.amtdr',
                'l.amtcr',
                'l.mnarr',
                'l.contrasub',
                DB::raw('(l.amtdr - l.amtcr) as net_dr'),
                DB::raw('DATEDIFF(?, l.vdate) as days_outstanding')
            )
            ->addBinding([$todate], 'select')
            ->orderBy('sg.name')
            ->orderBy('l.vdate')
            ->get()
            ->map(function ($row) use ($todate) {
                $days = $row->days_outstanding ?? 0;
                $row->bucket_0_30 = ($days <= 30) ? $row->net_dr : 0;
                $row->bucket_31_60 = ($days > 30 && $days <= 60) ? $row->net_dr : 0;
                $row->bucket_61_90 = ($days > 60 && $days <= 90) ? $row->net_dr : 0;
                $row->bucket_91_180 = ($days > 90 && $days <= 180) ? $row->net_dr : 0;
                $row->bucket_180_plus = ($days > 180) ? $row->net_dr : 0;
                return $row;
            });

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'comp' => $comp,
            'todate' => $todate,
            'total' => $rows->sum('net_dr'),
        ]);
    }

    /**
     * Aging Report — Creditors (detailed)
     */
    public function agingRepCr(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.agingrepcr', compact('propertyid', 'todate'));
    }

    public function agingRepCrFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->input('todate', date('Y-m-d'));

        $rows = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')
                     ->where('sg.propertyid', $propertyid);
            })
            ->where('l.propertyid', $propertyid)
            ->where('sg.nature', 'Supplier')
            ->where('l.vdate', '<=', $todate)
            ->whereRaw('(l.amtcr - l.amtdr) > 0')
            ->select(
                'sg.subcode',
                'sg.name',
                'l.docid',
                'l.vsno',
                'l.vtype',
                'l.vno',
                'l.vdate',
                'l.amtdr',
                'l.amtcr',
                'l.mnarr',
                'l.contrasub',
                DB::raw('(l.amtcr - l.amtdr) as net_cr'),
                DB::raw('DATEDIFF(?, l.vdate) as days_outstanding')
            )
            ->addBinding([$todate], 'select')
            ->orderBy('sg.name')
            ->orderBy('l.vdate')
            ->get()
            ->map(function ($row) use ($todate) {
                $days = $row->days_outstanding ?? 0;
                $row->bucket_0_30 = ($days <= 30) ? $row->net_cr : 0;
                $row->bucket_31_60 = ($days > 30 && $days <= 60) ? $row->net_cr : 0;
                $row->bucket_61_90 = ($days > 60 && $days <= 90) ? $row->net_cr : 0;
                $row->bucket_91_180 = ($days > 90 && $days <= 180) ? $row->net_cr : 0;
                $row->bucket_180_plus = ($days > 180) ? $row->net_cr : 0;
                return $row;
            });

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'comp' => $comp,
            'todate' => $todate,
            'total' => $rows->sum('net_cr'),
        ]);
    }

    /**
     * Due List — Customer/Debtor outstanding with transaction detail
     */
    public function dueList(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.duelist', compact('propertyid', 'todate'));
    }

    public function dueListFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->input('todate', date('Y-m-d'));
        $subcode = $request->input('subcode');

        $query = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')
                     ->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('city as c', 'sg.citycode', '=', 'c.citycode')
            ->where('l.propertyid', $propertyid)
            ->where('sg.nature', 'Customer')
            ->where('l.vdate', '<=', $todate)
            ->select(
                'sg.subcode', 'sg.name', 'sg.add1', 'sg.add2', 'c.cityname',
                'sg.conperson', 'sg.pin', 'sg.email', 'sg.phoneo',
                'l.docid', 'l.vsno', 'l.vtype', 'l.vno', 'l.vdate',
                'l.amtdr', 'l.amtcr', 'l.mnarr', 'l.narr', 'l.contrasub'
            );

        if ($subcode) {
            $query->where('l.subcode', $subcode);
        }

        $rows = $query->orderBy('sg.name')->orderBy('l.vdate')->orderBy('l.docid')->get();
        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json(['success' => true, 'data' => $rows, 'comp' => $comp, 'todate' => $todate]);
    }

    /**
     * Due List — Creditor Overlay
     */
    public function dueListCreditorOverlay(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.duelistcreditoroverlay', compact('propertyid', 'todate'));
    }

    public function dueListCreditorOverlayFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $todate = $request->input('todate', date('Y-m-d'));
        $subcode = $request->input('subcode');

        $query = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')
                     ->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('city as c', 'sg.citycode', '=', 'c.citycode')
            ->where('l.propertyid', $propertyid)
            ->where('sg.nature', 'Supplier')
            ->where('l.vdate', '<=', $todate)
            ->select(
                'sg.subcode', 'sg.name', 'sg.add1', 'sg.add2', 'c.cityname',
                'sg.conperson', 'sg.pin', 'sg.email', 'sg.phoneo',
                'l.docid', 'l.vsno', 'l.vtype', 'l.vno', 'l.vdate',
                'l.amtdr', 'l.amtcr', 'l.mnarr', 'l.narr', 'l.contrasub'
            );

        if ($subcode) {
            $query->where('l.subcode', $subcode);
        }

        $rows = $query->orderBy('sg.name')->orderBy('l.vdate')->orderBy('l.docid')->get();
        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json(['success' => true, 'data' => $rows, 'comp' => $comp, 'todate' => $todate]);
    }

    /**
     * Guest Payments — all payment transactions per guest
     */
    public function guestPayments(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $fromdate = $request->get('fromdate', date('Y-m-d'));
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.guestpayments', compact('propertyid', 'fromdate', 'todate'));
    }

    public function guestPaymentsFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));

        $rows = DB::table('paycharge as pc')
            ->leftJoin('roomocc as ro', function ($join) use ($propertyid) {
                $join->on('pc.foliono', '=', 'ro.foliono')->where('ro.propertyid', $propertyid);
            })
            ->leftJoin('grpbookingdetails as bd', function ($join) use ($propertyid) {
                $join->on('ro.docid', '=', 'bd.docid')->where('bd.propertyid', $propertyid);
            })
            ->leftJoin('guestprofile as gp', 'bd.guestprofid', '=', 'gp.id')
            ->where('pc.propertyid', $propertyid)
            ->where('pc.vdate', '>=', $fromdate)
            ->where('pc.vdate', '<=', $todate)
            ->where('pc.paycode', 'P')
            ->select(
                'pc.docid', 'pc.vsno', 'pc.vdate', 'pc.foliono',
                'pc.amtdr as amount', 'pc.mnarr as narration', 'pc.paymodedetail',
                DB::raw('COALESCE(gp.guestname, bd.guestname, "Walk-in") as guestname'),
                DB::raw('COALESCE(ro.rmcode, bd.rmcode, "") as room')
            )
            ->orderBy('pc.vdate')
            ->get();

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();

        return response()->json([
            'success' => true, 'data' => $rows, 'comp' => $comp,
            'fromdate' => $fromdate, 'todate' => $todate,
            'total' => $rows->sum('amount'),
        ]);
    }

    /**
     * Non-Transferable Accounts
     */
    public function nonTrans(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        return view('property.finance.nontrans', compact('propertyid'));
    }

    public function nonTransFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $rows = DB::table('subgroup as sg')
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('sg.propertyid', $propertyid)
            ->where('sg.nontrans', 'Y')
            ->select('sg.subcode', 'sg.name', 'sg.nature', 'ag.group_name')
            ->orderBy('sg.name')
            ->get();
        $comp = DB::table('company')->where('propertyid', $propertyid)->first();
        return response()->json(['success' => true, 'data' => $rows, 'comp' => $comp]);
    }

    /**
     * Loan Advance Summary
     */
    public function loanAdvSumm(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $fromdate = $request->get('fromdate', date('Y-m-d'));
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.loanadvsumm', compact('propertyid', 'fromdate', 'todate'));
    }

    public function loanAdvSummFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));

        $rows = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('l.propertyid', $propertyid)
            ->where('l.vdate', '>=', $fromdate)
            ->where('l.vdate', '<=', $todate)
            ->whereRaw("(ag.group_name LIKE '%loan%' OR ag.group_name LIKE '%advance%')")
            ->select(
                'sg.subcode', 'sg.name', 'sg.nature',
                DB::raw('SUM(l.amtdr) as total_dr'),
                DB::raw('SUM(l.amtcr) as total_cr'),
                DB::raw('SUM(l.amtdr) - SUM(l.amtcr) as net')
            )
            ->groupBy('sg.subcode', 'sg.name', 'sg.nature')
            ->havingRaw('ABS(SUM(l.amtdr) - SUM(l.amtcr)) > 0')
            ->orderBy('sg.name')
            ->get();

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();
        return response()->json([
            'success' => true, 'data' => $rows, 'comp' => $comp,
            'fromdate' => $fromdate, 'todate' => $todate,
        ]);
    }

    /**
     * Loan Ledger
     */
    public function loanLedg(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $fromdate = $request->get('fromdate', date('Y-m-d'));
        $todate = $request->get('todate', date('Y-m-d'));
        return view('property.finance.loanledger', compact('propertyid', 'fromdate', 'todate'));
    }

    public function loanLedgFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));
        $subcode = $request->input('subcode');

        $query = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('l.propertyid', $propertyid)
            ->where('l.vdate', '>=', $fromdate)
            ->where('l.vdate', '<=', $todate)
            ->whereRaw("(ag.group_name LIKE '%loan%' OR ag.group_name LIKE '%advance%')")
            ->select('sg.subcode', 'sg.name', 'l.docid', 'l.vsno', 'l.vdate', 'l.vtype', 'l.vno', 'l.amtdr', 'l.amtcr', 'l.mnarr', 'l.narr');

        if ($subcode) {
            $query->where('l.subcode', $subcode);
        }

        $rows = $query->orderBy('sg.name')->orderBy('l.vdate')->get();
        $comp = DB::table('company')->where('propertyid', $propertyid)->first();
        return response()->json([
            'success' => true, 'data' => $rows, 'comp' => $comp,
            'fromdate' => $fromdate, 'todate' => $todate,
        ]);
    }

    /**
     * Loan Register
     */
    public function loanReg(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        return view('property.finance.loanregister', compact('propertyid'));
    }

    public function loanRegFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;

        $rows = DB::table('ledger as l')
            ->join('subgroup as sg', function ($join) use ($propertyid) {
                $join->on('l.subcode', '=', 'sg.subcode')->where('sg.propertyid', $propertyid);
            })
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('l.propertyid', $propertyid)
            ->whereRaw("(ag.group_name LIKE '%loan%' OR ag.group_name LIKE '%advance%')")
            ->select(
                'sg.subcode', 'sg.name', 'sg.phoneo', 'sg.email', 'sg.add1',
                DB::raw('SUM(l.amtdr) as total_disbursed'),
                DB::raw('SUM(l.amtcr) as total_repaid'),
                DB::raw('SUM(l.amtdr) - SUM(l.amtcr) as balance'),
                DB::raw('MAX(l.vdate) as last_transaction'),
                DB::raw('MIN(l.vdate) as first_transaction')
            )
            ->groupBy('sg.subcode', 'sg.name', 'sg.phoneo', 'sg.email', 'sg.add1')
            ->havingRaw('SUM(l.amtdr) > SUM(l.amtcr)')
            ->orderBy('sg.name')
            ->get();

        $comp = DB::table('company')->where('propertyid', $propertyid)->first();
        return response()->json([
            'success' => true, 'data' => $rows, 'comp' => $comp,
            'total_balance' => $rows->sum('balance'),
        ]);
    }

    /**
     * Customer Detail
     */
    public function customerDetail(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        return view('property.finance.customerdetail', compact('propertyid'));
    }

    public function customerDetailFetch(Request $request)
    {
        $propertyid = Auth::user()->propertyid;
        $search = $request->input('search');

        $query = DB::table('subgroup as sg')
            ->leftJoin('city as c', 'sg.citycode', '=', 'c.citycode')
            ->leftJoin('acgroup as ag', 'sg.group_code', '=', 'ag.group_code')
            ->where('sg.propertyid', $propertyid)
            ->where('sg.nature', 'Customer')
            ->select('sg.subcode', 'sg.name', 'sg.add1', 'sg.add2', 'c.cityname', 'sg.conperson', 'sg.pin', 'sg.email', 'sg.phoneo', 'sg.phoner', 'sg.gstin', 'sg.pan', 'ag.group_name');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('sg.name', 'LIKE', "%{$search}%")
                  ->orWhere('sg.subcode', 'LIKE', "%{$search}%")
                  ->orWhere('sg.phoneo', 'LIKE', "%{$search}%")
                  ->orWhere('sg.email', 'LIKE', "%{$search}%");
            });
        }

        $rows = $query->orderBy('sg.name')->limit(500)->get();
        $comp = DB::table('company')->where('propertyid', $propertyid)->first();
        return response()->json(['success' => true, 'data' => $rows, 'comp' => $comp]);
    }

}
