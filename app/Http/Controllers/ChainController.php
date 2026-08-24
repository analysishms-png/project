<?php

namespace App\Http\Controllers;

use App\Models\Companyreg;
use App\Models\RoomOcc;
use App\Models\Paycharge;
use App\Models\Sale1;
use App\Models\RoomMast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChainController extends Controller
{
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

    // ═══════════════════════════════════════════════════════════════════════════
    // CHAIN DASHBOARD — Centralized view of all properties
    // ═══════════════════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $today = Carbon::today()->toDateString();
        $monthStart = Carbon::now()->startOfMonth()->toDateString();

        // Get all active properties
        $properties = Companyreg::where('status', 1)->orderBy('comp_name')->get();

        // Get metrics for each property
        $propertyData = [];
        foreach ($properties as $prop) {
            $pid = $prop->propertyid;

            // Total rooms
            $totalRooms = RoomMast::where('propertyid', $pid)
                ->where('type', 'RO')
                ->count();

            // Occupied rooms
            $occupied = RoomOcc::where('propertyid', $pid)
                ->where('chkindate', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $today);
                })
                ->whereNull('Type')
                ->count();

            $available = max(0, $totalRooms - $occupied);
            $occupancyPct = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0;

            // Monthly revenue
            $monthRevenue = Paycharge::where('propertyid', $pid)
                ->where('vdate', '>=', $monthStart)
                ->where('vtype', '!=', 'ADV')
                ->sum('amtdr');

            // POS revenue
            $posRevenue = Sale1::where('propertyid', $pid)
                ->where('vdate', '>=', $monthStart)
                ->sum('netamt');

            // ADR
            $adr = $occupied > 0 ? round($monthRevenue / max($occupied, 1), 2) : 0;

            $propertyData[] = [
                'propertyid' => $pid,
                'name' => $prop->comp_name,
                'code' => $prop->comp_code,
                'city' => $prop->city ?? '',
                'state' => $prop->state ?? '',
                'total_rooms' => $totalRooms,
                'occupied' => $occupied,
                'available' => $available,
                'occupancy_pct' => $occupancyPct,
                'month_revenue' => round($monthRevenue, 2),
                'pos_revenue' => round($posRevenue, 2),
                'adr' => $adr,
                'total_revenue' => round($monthRevenue + $posRevenue, 2),
            ];
        }

        // Chain-wide aggregates
        $totalProperties = count($propertyData);
        $totalRoomCount = collect($propertyData)->sum('total_rooms');
        $totalOccupied = collect($propertyData)->sum('occupied');
        $totalRevenue = collect($propertyData)->sum('total_revenue');
        $avgOccupancy = $totalRoomCount > 0 ? round(($totalOccupied / $totalRoomCount) * 100, 1) : 0;
        $avgADR = $totalOccupied > 0 ? round(collect($propertyData)->sum('month_revenue') / max($totalOccupied, 1), 2) : 0;

        // Group by state
        $byState = collect($propertyData)->groupBy('state')->map(function ($props, $state) {
            return [
                'state' => $state,
                'properties' => $props->count(),
                'total_rooms' => $props->sum('total_rooms'),
                'occupied' => $props->sum('occupied'),
                'revenue' => $props->sum('total_revenue'),
                'avg_occupancy' => $props->sum('total_rooms') > 0 ? round(($props->sum('occupied') / $props->sum('total_rooms')) * 100, 1) : 0,
            ];
        });

        // Top performers
        $topByRevenue = collect($propertyData)->sortByDesc('total_revenue')->take(5);
        $topByOccupancy = collect($propertyData)->sortByDesc('occupancy_pct')->take(5);

        return view('property.chaindashboard', compact(
            'propertyData', 'totalProperties', 'totalRoomCount', 'totalOccupied',
            'totalRevenue', 'avgOccupancy', 'avgADR', 'byState', 'topByRevenue',
            'topByOccupancy', 'today'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PROPERTY SWITCHER — Switch between properties
    // ═══════════════════════════════════════════════════════════════════════════

    public function switchProperty(Request $request, $propertyid)
    {
        $user = Auth::user();

        // Check if user has access to this property
        $hasAccess = DB::table('users')
            ->where('id', $user->id)
            ->where('propertyid', $propertyid)
            ->exists();

        if (!$hasAccess && $user->role != '1') {
            return back()->with('error', 'You do not have access to this property');
        }

        // Switch session property
        session(['propertyid' => $propertyid]);

        // Update enviro_general ncurdate for new property
        $ncurdate = DB::table('enviro_general')
            ->where('propertyid', $propertyid)
            ->value('ncur');

        if ($ncurdate) {
            session(['ncurdate' => $ncurdate]);
        }

        return redirect('/')->with('success', 'Switched to property ' . $propertyid);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CROSS-PROPERTY REPORT — Revenue comparison across properties
    // ═══════════════════════════════════════════════════════════════════════════

    public function crossPropertyReport(Request $request)
    {
        $startDate = $request->input('start', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end', Carbon::now()->endOfMonth()->toDateString());

        $reportData = $this->buildReportData($startDate, $endDate);

        return view('property.chainreport', compact('reportData', 'startDate', 'endDate'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // CROSS-PROPERTY REPORT DATA — JSON feed for the live AJAX report
    // ═══════════════════════════════════════════════════════════════════════════

    public function crossPropertyReportData(Request $request)
    {
        $startDate = $request->input('start', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end', Carbon::now()->endOfMonth()->toDateString());

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            return response()->json(['error' => 'Invalid date range'], 422);
        }

        $rows = $this->buildReportData($startDate, $endDate);
        $totals = [
            'revenue' => array_sum(array_column($rows, 'revenue')),
            'pos' => array_sum(array_column($rows, 'pos')),
            'total' => array_sum(array_column($rows, 'total')),
            'checkins' => array_sum(array_column($rows, 'checkins')),
            'room_nights' => array_sum(array_column($rows, 'room_nights')),
        ];

        return response()->json([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    protected function buildReportData($startDate, $endDate)
    {
        $properties = Companyreg::where('status', 1)->orderBy('comp_name')->get();

        $reportData = [];
        foreach ($properties as $prop) {
            $pid = $prop->propertyid;

            // Revenue
            $revenue = Paycharge::where('propertyid', $pid)
                ->whereBetween('vdate', [$startDate, $endDate])
                ->where('vtype', '!=', 'ADV')
                ->sum('amtdr');

            // POS
            $pos = Sale1::where('propertyid', $pid)
                ->whereBetween('vdate', [$startDate, $endDate])
                ->sum('netamt');

            // Check-ins
            $checkins = RoomOcc::where('propertyid', $pid)
                ->whereBetween('chkindate', [$startDate, $endDate])
                ->count();

            // Room nights
            $roomNights = RoomOcc::where('propertyid', $pid)
                ->where('chkindate', '<=', $endDate)
                ->where(function ($q) use ($endDate) {
                    $q->whereNull('chkoutdate')->orWhere('chkoutdate', '<=', $endDate);
                })
                ->count();

            $reportData[] = [
                'propertyid' => $pid,
                'name' => $prop->comp_name,
                'city' => $prop->city ?? '',
                'revenue' => round($revenue, 2),
                'pos' => round($pos, 2),
                'total' => round($revenue + $pos, 2),
                'checkins' => $checkins,
                'room_nights' => $roomNights,
            ];
        }

        // Sort by total revenue
        usort($reportData, fn($a, $b) => $b['total'] <=> $a['total']);

        return $reportData;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PROPERTY COMPARISON — Side-by-side property comparison
    // ═══════════════════════════════════════════════════════════════════════════

    public function propertyComparison(Request $request)
    {
        $today = Carbon::today()->toDateString();
        $properties = Companyreg::where('status', 1)->orderBy('comp_name')->get();

        $comparison = [];
        foreach ($properties as $prop) {
            $pid = $prop->propertyid;

            $totalRooms = RoomMast::where('propertyid', $pid)->where('type', 'RO')->count();
            $occupied = RoomOcc::where('propertyid', $pid)
                ->where('chkindate', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $today);
                })
                ->whereNull('Type')
                ->count();

            $monthRevenue = Paycharge::where('propertyid', $pid)
                ->where('vdate', '>=', Carbon::now()->startOfMonth()->toDateString())
                ->where('vtype', '!=', 'ADV')
                ->sum('amtdr');

            $adr = $occupied > 0 ? round($monthRevenue / max($occupied, 1), 2) : 0;
            $revpar = $totalRooms > 0 ? round($monthRevenue / max($totalRooms, 1), 2) : 0;

            $comparison[] = [
                'propertyid' => $pid,
                'name' => $prop->comp_name,
                'city' => $prop->city ?? '',
                'total_rooms' => $totalRooms,
                'occupied' => $occupied,
                'occupancy_pct' => $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0,
                'revenue' => round($monthRevenue, 2),
                'adr' => $adr,
                'revpar' => $revpar,
            ];
        }

        return view('property.chaincomparison', compact('comparison'));
    }
}
