<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class AnalyticsController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->propertyid = session('propertyid') ?? $this->propertyid ?? 0;
            return $next($request);
        });
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * BI DASHBOARD — Main analytics overview
     * ═══════════════════════════════════════════════════════════════
     */
    public function biDashboard(Request $request)
    {
        $prpid = $this->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));
        $period = $request->input('period', '30'); // days

        $periodStart = date('Y-m-d', strtotime("-{$period} days", strtotime($fromdate)));

        // ── KPI Summary ──
        $kpi = $this->getKpiSummary($prpid, $periodStart, $todate);

        // ── Revenue Trend ──
        $revenueTrend = $this->getRevenueTrend($prpid, $periodStart, $todate);

        // ── Occupancy Trend ──
        $occupancyTrend = $this->getOccupancyTrend($prpid, $periodStart, $todate);

        // ── Room Type Performance ──
        $roomPerformance = $this->getRoomTypePerformance($prpid, $periodStart, $todate);

        // ── Revenue Sources Breakdown ──
        $revenueSources = $this->getRevenueSources($prpid, $periodStart, $todate);

        // ── Guest Demographics ──
        $guestDemo = $this->getGuestDemographics($prpid, $periodStart, $todate);

        // ── POS Performance ──
        $posPerformance = $this->getPosPerformance($prpid, $periodStart, $todate);

        // ── Top Companies / Sources ──
        $topCompanies = $this->getTopCompanies($prpid, $periodStart, $todate);
        $topSources = $this->getTopSources($prpid, $periodStart, $todate);

        // ── Day of Week Pattern ──
        $dayOfWeekPattern = $this->getDayOfWeekPattern($prpid, $periodStart, $todate);

        return view('property.analytics.bi-dashboard', compact(
            'kpi', 'revenueTrend', 'occupancyTrend', 'roomPerformance',
            'revenueSources', 'guestDemo', 'posPerformance',
            'topCompanies', 'topSources', 'dayOfWeekPattern',
            'fromdate', 'todate', 'period'
        ));
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * API: Real-time BI data (JSON for AJAX refresh)
     * ═══════════════════════════════════════════════════════════════
     */
    public function biApiData(Request $request)
    {
        $prpid = $this->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));
        $period = $request->input('period', '30');
        $periodStart = date('Y-m-d', strtotime("-{$period} days", strtotime($fromdate)));

        return response()->json([
            'kpi' => $this->getKpiSummary($prpid, $periodStart, $todate),
            'revenueTrend' => $this->getRevenueTrend($prpid, $periodStart, $todate),
            'occupancyTrend' => $this->getOccupancyTrend($prpid, $periodStart, $todate),
            'roomPerformance' => $this->getRoomTypePerformance($prpid, $periodStart, $todate),
            'revenueSources' => $this->getRevenueSources($prpid, $periodStart, $todate),
            'posPerformance' => $this->getPosPerformance($prpid, $periodStart, $todate),
            'topCompanies' => $this->getTopCompanies($prpid, $periodStart, $todate),
            'topSources' => $this->getTopSources($prpid, $periodStart, $todate),
        ]);
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * CUSTOM REPORT BUILDER
     * ═══════════════════════════════════════════════════════════════
     */
    public function reportBuilder(Request $request)
    {
        // Available data sources
        $dataSources = [
            'paycharge' => [
                'label' => 'Guest Transactions (PayCharge)',
                'columns' => ['vdate', 'vtype', 'docid', 'roomno', 'accode', 'amtdr', 'amtcr', 'narration', 'guestname', 'foliono'],
                'filters' => ['vdate', 'vtype', 'roomno', 'accode', 'vprefix'],
            ],
            'roomocc' => [
                'label' => 'Room Occupancy (RoomOcc)',
                'columns' => ['chkindate', 'depdate', 'roomno', 'roomcat', 'roomtype', 'ratecode', 'roomrate', 'nodays', 'adult', 'children', 'name'],
                'filters' => ['chkindate', 'depdate', 'roomcat', 'roomtype', 'roomno'],
            ],
            'grpbookingdetails' => [
                'label' => 'Reservations (GrpBookinDetail)',
                'columns' => ['vdate', 'vprefix', 'vno', 'roomcat', 'roomtype', 'adult', 'children', 'rate', 'advamt', 'guestname', 'companycode', 'sourcedetails', 'agentcode', 'agentname', 'arrival', 'departure'],
                'filters' => ['vdate', 'roomcat', 'roomtype', 'companycode', 'sourcedetails'],
            ],
            'guestprof' => [
                'label' => 'Guest Profiles',
                'columns' => ['guestname', 'mobile', 'email', 'idtype', 'idno', 'nationality', 'address', 'city', 'state', 'company'],
                'filters' => ['guestname', 'nationality', 'company', 'city'],
            ],
            'sale1' => [
                'label' => 'POS Bills (Sale1)',
                'columns' => ['vdate', 'restcode', 'table_no', 'guestcount', 'netamt', 'discamt', 'taxamt', 'roundoff', 'servicecharge'],
                'filters' => ['vdate', 'restcode', 'table_no'],
            ],
            'sale2' => [
                'label' => 'POS Items (Sale2)',
                'columns' => ['vdate', 'restcode', 'itemcode', 'itemname', 'qty', 'rate', 'amt', 'taxamt', 'discamt', 'netamt'],
                'filters' => ['vdate', 'restcode', 'itemcode', 'itemname'],
            ],
            'kot1' => [
                'label' => 'KOT Headers',
                'columns' => ['vdate', 'restcode', 'table_no', 'guestcount', 'kot_status', 'kot_type'],
                'filters' => ['vdate', 'restcode', 'kot_status', 'kot_type'],
            ],
            'hallsale1' => [
                'label' => 'Banquet Bills',
                'columns' => ['vdate', 'docid', 'partyname', 'hallname', 'func_name', 'netamt', 'discamt', 'taxamt', 'roundoff'],
                'filters' => ['vdate', 'hallname', 'func_name'],
            ],
            'advance' => [
                'label' => 'Advances',
                'columns' => ['vdate', 'docid', 'guestname', 'roomno', 'amount', 'paymode', 'vtype'],
                'filters' => ['vdate', 'paymode', 'vtype'],
            ],
            'suntran' => [
                'label' => 'Payments (SunTran)',
                'columns' => ['vdate', 'vtype', 'paycode', 'amt', 'narration', 'refdocid'],
                'filters' => ['vdate', 'vtype', 'paycode'],
            ],
        ];

        $reportData = null;
        $reportConfig = null;

        // Execute custom query if form submitted
        if ($request->isMethod('post')) {
            $dataSource = $request->input('datasource');
            $columns = $request->input('columns', []);
            $filters = $request->input('filters', []);
            $filterValues = $request->input('filter_values', []);
            $groupBy = $request->input('groupby', '');
            $orderBy = $request->input('orderby', 'vdate');
            $orderDir = $request->input('orderdir', 'DESC');
            $limit = $request->input('limit', 100);

            if (isset($dataSources[$dataSource]) && !empty($columns)) {
                $reportConfig = compact('dataSource', 'columns', 'filters', 'filterValues', 'groupBy', 'orderBy', 'orderDir', 'limit');
                $reportData = $this->executeCustomReport($dataSources[$dataSource], $columns, $filters, $filterValues, $groupBy, $orderBy, $orderDir, $limit);
            }
        }

        return view('property.analytics.report-builder', compact('dataSources', 'reportData', 'reportConfig'));
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * SAVE / LOAD / DELETE custom reports
     * ═══════════════════════════════════════════════════════════════
     */
    public function savedReports(Request $request)
    {
        $reports = DB::table('analytics_saved_reports')
            ->where('propertyid', $this->propertyid)
            ->orderByDesc('updated_at')
            ->get();

        return view('property.analytics.saved-reports', compact('reports'));
    }

    public function saveReport(Request $request)
    {
        $request->validate([
            'report_name' => 'required|string|max:100',
            'config_json' => 'required|string',
        ]);

        DB::table('analytics_saved_reports')->insert([
            'propertyid' => $this->propertyid,
            'report_name' => $request->report_name,
            'config_json' => $request->config_json,
            'description' => $request->input('description', ''),
            'created_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'message' => 'Report saved successfully']);
    }

    public function deleteReport(Request $request, $id)
    {
        DB::table('analytics_saved_reports')
            ->where('id', $id)
            ->where('propertyid', $this->propertyid)
            ->delete();

        return redirect()->route('analytics.saved-reports')->with('success', 'Report deleted');
    }

    public function loadReport($id)
    {
        $report = DB::table('analytics_saved_reports')
            ->where('id', $id)
            ->where('propertyid', $this->propertyid)
            ->first();

        if (!$report) {
            return redirect()->route('analytics.saved-reports')->with('error', 'Report not found');
        }

        // Redirect to report builder with pre-filled config
        $config = json_decode($report->config_json, true);
        return redirect()->route('analytics.report-builder', $config);
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * SCHEDULED REPORTS
     * ═══════════════════════════════════════════════════════════════
     */
    public function scheduledReports()
    {
        $reports = DB::table('analytics_saved_reports')
            ->where('propertyid', $this->propertyid)
            ->where('is_scheduled', 1)
            ->orderBy('schedule_frequency')
            ->get();

        return view('property.analytics.scheduled-reports', compact('reports'));
    }

    public function scheduleReport(Request $request)
    {
        $request->validate([
            'report_id' => 'required|integer',
            'frequency' => 'required|in:daily,weekly,monthly',
        ]);

        DB::table('analytics_saved_reports')
            ->where('id', $request->report_id)
            ->where('propertyid', $this->propertyid)
            ->update([
                'is_scheduled' => 1,
                'schedule_frequency' => $request->frequency,
                'schedule_email' => $request->input('email', Auth::user()->email ?? ''),
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Report scheduled']);
    }

    public function unscheduleReport(Request $request, $id)
    {
        DB::table('analytics_saved_reports')
            ->where('id', $id)
            ->where('propertyid', $this->propertyid)
            ->update([
                'is_scheduled' => 0,
                'schedule_frequency' => null,
                'updated_at' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Schedule removed']);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE DATA AGGREGATION METHODS
    // ═══════════════════════════════════════════════════════════════

    private function getKpiSummary($prpid, $from, $to)
    {
        // Total rooms
        $totalRooms = DB::table('roommaster')
            ->where('propertyid', $prpid)
            ->count();

        // Currently occupied
        $occupied = DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->where('activeYN', 'Y')
            ->count();

        // Revenue in period
        $roomRevenue = DB::table('paycharge')
            ->where('propertyid', $prpid)
            ->where('vtype', '!=', 'ADV')
            ->whereBetween('vdate', [$from, $to])
            ->sum('amtdr');

        $posRevenue = DB::table('sale1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->sum('netamt');

        $banquetRevenue = DB::table('hallsale1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->sum('netamt');

        $payments = DB::table('paycharge')
            ->where('propertyid', $prpid)
            ->where('vtype', '!=', 'ADV')
            ->whereBetween('vdate', [$from, $to])
            ->sum('amtcr');

        // Check-ins in period
        $checkins = DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->whereBetween('chkindate', [$from, $to])
            ->count();

        // Check-outs in period
        $checkouts = DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->whereBetween('chkoutdate', [$from, $to])
            ->where('activeYN', 'N')
            ->count();

        // Average daily revenue
        $days = max(1, (int) ((strtotime($to) - strtotime($from)) / 86400) + 1);
        $totalRevenue = $roomRevenue + $posRevenue + $banquetRevenue;

        // Occupancy rate
        $avgOccupancy = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0;

        // ADR (Average Daily Rate)
        $adr = $checkins > 0 ? round($roomRevenue / max(1, $checkins), 2) : 0;

        // RevPAR
        $revpar = $totalRooms > 0 ? round($roomRevenue / $totalRooms / max(1, $days), 2) : 0;

        // Outstanding
        $outstanding = DB::table('paycharge')
            ->where('propertyid', $prpid)
            ->selectRaw('SUM(amtdr - amtcr) as balance')
            ->havingRaw('balance > 0')
            ->value('balance') ?? 0;

        return [
            'total_rooms' => $totalRooms,
            'occupied' => $occupied,
            'vacant' => $totalRooms - $occupied,
            'occupancy_pct' => $avgOccupancy,
            'room_revenue' => round($roomRevenue, 2),
            'pos_revenue' => round($posRevenue, 2),
            'banquet_revenue' => round($banquetRevenue, 2),
            'total_revenue' => round($totalRevenue, 2),
            'payments_received' => round($payments, 2),
            'checkins' => $checkins,
            'checkouts' => $checkouts,
            'adr' => $adr,
            'revpar' => $revpar,
            'outstanding' => round(abs($outstanding), 2),
            'days' => $days,
        ];
    }

    private function getRevenueTrend($prpid, $from, $to)
    {
        $daily = DB::table('paycharge')
            ->where('propertyid', $prpid)
            ->where('vtype', '!=', 'ADV')
            ->whereBetween('vdate', [$from, $to])
            ->selectRaw('vdate, SUM(amtdr) as room_rev, SUM(amtcr) as payments')
            ->groupBy('vdate')
            ->orderBy('vdate')
            ->get();

        $posDaily = DB::table('sale1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->selectRaw('vdate, SUM(netamt) as pos_rev')
            ->groupBy('vdate')
            ->orderBy('vdate')
            ->get();

        // Merge
        $posMap = $posDaily->pluck('pos_rev', 'vdate')->toArray();
        $result = [];
        foreach ($daily as $row) {
            $result[] = [
                'date' => $row->vdate,
                'room' => round($row->room_rev, 2),
                'pos' => round($posMap[$row->vdate] ?? 0, 2),
                'total' => round($row->room_rev + ($posMap[$row->vdate] ?? 0), 2),
                'payments' => round($row->payments, 2),
            ];
        }

        return $result;
    }

    private function getOccupancyTrend($prpid, $from, $to)
    {
        $totalRooms = DB::table('roommaster')->where('propertyid', $prpid)->count();
        if ($totalRooms == 0) return [];

        // Get daily occupied count from roomocc
        $periods = [];
        $current = strtotime($from);
        $end = strtotime($to);

        while ($current <= $end) {
            $date = date('Y-m-d', $current);
            $occupied = DB::table('roomocc')
                ->where('propertyid', $prpid)
                ->where('activeYN', 'Y')
                ->where('chkindate', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->where('depdate', '>', $date)
                      ->orWhere('depdate', '=', $date)
                      ->where('chkouttime', '=', '00:00:00');
                })
                ->count();

            $periods[] = [
                'date' => date('M d', $current),
                'occupied' => $occupied,
                'vacant' => $totalRooms - $occupied,
                'pct' => round(($occupied / $totalRooms) * 100, 1),
            ];

            $current = strtotime('+1 day', $current);
        }

        return $periods;
    }

    private function getRoomTypePerformance($prpid, $from, $to)
    {
        return DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->whereBetween('chkindate', [$from, $to])
            ->selectRaw('roomcat, COUNT(*) as stays, AVG(roomrate) as avg_rate, SUM(roomrate * nodays) as total_rev, AVG(nodays) as avg_nights')
            ->groupBy('roomcat')
            ->orderByDesc('total_rev')
            ->get()
            ->map(function ($row) {
                return [
                    'category' => $row->roomcat ?: 'Unknown',
                    'stays' => (int) $row->stays,
                    'avg_rate' => round($row->avg_rate, 2),
                    'total_revenue' => round($row->total_rev, 2),
                    'avg_nights' => round($row->avg_nights, 1),
                ];
            })
            ->toArray();
    }

    private function getRevenueSources($prpid, $from, $to)
    {
        $roomRev = DB::table('paycharge')
            ->where('propertyid', $prpid)
            ->where('vtype', '!=', 'ADV')
            ->whereBetween('vdate', [$from, $to])
            ->sum('amtdr');

        $posRev = DB::table('sale1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->sum('netamt');

        $banquetRev = DB::table('hallsale1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->sum('netamt');

        $total = $roomRev + $posRev + $banquetRev;
        if ($total == 0) $total = 1;

        return [
            ['source' => 'Room Revenue', 'amount' => round($roomRev, 2), 'pct' => round(($roomRev / $total) * 100, 1)],
            ['source' => 'POS / Restaurant', 'amount' => round($posRev, 2), 'pct' => round(($posRev / $total) * 100, 1)],
            ['source' => 'Banquet', 'amount' => round($banquetRev, 2), 'pct' => round(($banquetRev / $total) * 100, 1)],
        ];
    }

    private function getGuestDemographics($prpid, $from, $to)
    {
        // Nationality distribution
        $nationality = DB::table('guestprof')
            ->where('propertyid', $prpid)
            ->selectRaw('COALESCE(NULLIF(nationality, ""), "Indian") as nationality, COUNT(*) as cnt')
            ->groupBy('nationality')
            ->orderByDesc('cnt')
            ->limit(10)
            ->get()
            ->map(fn($r) => ['label' => $r->nationality, 'value' => (int) $r->cnt])
            ->toArray();

        // Adult vs Children
        $guestMix = DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->whereBetween('chkindate', [$from, $to])
            ->selectRaw('SUM(adult) as total_adults, SUM(children) as total_children')
            ->first();

        // Repeat vs New guests
        $guestIds = DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->whereBetween('chkindate', [$from, $to])
            ->pluck('guestprof')
            ->filter()
            ->unique();

        $repeatGuests = 0;
        foreach ($guestIds as $gid) {
            $stayCount = DB::table('roomocc')
                ->where('propertyid', $prpid)
                ->where('guestprof', $gid)
                ->count();
            if ($stayCount > 1) $repeatGuests++;
        }

        return [
            'nationality' => $nationality,
            'total_adults' => (int) ($guestMix->total_adults ?? 0),
            'total_children' => (int) ($guestMix->total_children ?? 0),
            'repeat_guests' => $repeatGuests,
            'total_unique' => $guestIds->count(),
            'repeat_pct' => $guestIds->count() > 0 ? round(($repeatGuests / $guestIds->count()) * 100, 1) : 0,
        ];
    }

    private function getPosPerformance($prpid, $from, $to)
    {
        // By outlet
        $byOutlet = DB::table('sale1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->selectRaw('restcode, COUNT(*) as bills, SUM(guestcount) as guests, SUM(netamt) as revenue, AVG(netamt) as avg_bill')
            ->groupBy('restcode')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn($r) => [
                'outlet' => $r->restcode ?: 'Main',
                'bills' => (int) $r->bills,
                'guests' => (int) $r->guests,
                'revenue' => round($r->revenue, 2),
                'avg_bill' => round($r->avg_bill, 2),
            ])
            ->toArray();

        // KOT count
        $totalKots = DB::table('kot1')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->count();

        return [
            'by_outlet' => $byOutlet,
            'total_kots' => $totalKots,
        ];
    }

    private function getTopCompanies($prpid, $from, $to)
    {
        return DB::table('grpbookingdetails')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->where('companycode', '!=', '')
            ->selectRaw('companycode, COUNT(*) as bookings, SUM(rate) as total_rate')
            ->groupBy('companycode')
            ->orderByDesc('bookings')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getTopSources($prpid, $from, $to)
    {
        return DB::table('grpbookingdetails')
            ->where('propertyid', $prpid)
            ->whereBetween('vdate', [$from, $to])
            ->where('sourcedetails', '!=', '')
            ->selectRaw('sourcedetails, COUNT(*) as bookings')
            ->groupBy('sourcedetails')
            ->orderByDesc('bookings')
            ->limit(10)
            ->get()
            ->toArray();
    }

    private function getDayOfWeekPattern($prpid, $from, $to)
    {
        return DB::table('roomocc')
            ->where('propertyid', $prpid)
            ->whereBetween('chkindate', [$from, $to])
            ->selectRaw('DAYNAME(chkindate) as day_name, DAYOFWEEK(chkindate) as day_num, COUNT(*) as checkins')
            ->groupBy('day_name', 'day_num')
            ->orderBy('day_num')
            ->get()
            ->toArray();
    }

    private function executeCustomReport($source, $columns, $filters, $filterValues, $groupBy, $orderBy, $orderDir, $limit)
    {
        $table = array_key_first(array_filter([
            'paycharge' => 'paycharge',
            'roomocc' => 'roomocc',
            'grpbookingdetails' => 'grpbookingdetails',
            'guestprof' => 'guestprof',
            'sale1' => 'sale1',
            'sale2' => 'sale2',
            'kot1' => 'kot1',
            'hallsale1' => 'hallsale1',
            'advance' => 'advance',
            'suntran' => 'suntran',
        ], fn($v) => $v === $source['label'] || array_search($v, array_column($source, 'label')) !== false)) ?? 'paycharge';

        // Map label to table
        $tableMap = [
            'Guest Transactions (PayCharge)' => 'paycharge',
            'Room Occupancy (RoomOcc)' => 'roomocc',
            'Reservations (GrpBookinDetail)' => 'grpbookingdetails',
            'Guest Profiles' => 'guestprof',
            'POS Bills (Sale1)' => 'sale1',
            'POS Items (Sale2)' => 'sale2',
            'KOT Headers' => 'kot1',
            'Banquet Bills' => 'hallsale1',
            'Advances' => 'advance',
            'Payments (SunTran)' => 'suntran',
        ];

        $table = $tableMap[$source['label']] ?? 'paycharge';
        $selectCols = array_map(fn($c) => "{$table}.{$c}", $columns);

        $query = DB::table($table)
            ->where('propertyid', $this->propertyid)
            ->select($selectCols);

        // Apply filters
        if (!empty($filters) && !empty($filterValues)) {
            foreach ($filters as $f) {
                if (!empty($filterValues[$f])) {
                    if (in_array($f, ['vdate', 'chkindate', 'depdate'])) {
                        $query->whereBetween($f, [$filterValues[$f], $filterValues[$f . '_to'] ?? $filterValues[$f]]);
                    } else {
                        $query->where($f, $filterValues[$f]);
                    }
                }
            }
        }

        // Group by
        if ($groupBy) {
            $query->groupBy($groupBy);
        }

        // Order
        if ($orderBy && in_array($orderBy, $columns)) {
            $query->orderBy($orderBy, $orderDir === 'ASC' ? 'asc' : 'desc');
        }

        // Limit
        $query->limit(min((int) $limit, 10000));

        return $query->get();
    }
}
