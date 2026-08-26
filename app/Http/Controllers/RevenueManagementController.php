<?php

namespace App\Http\Controllers;

use App\Models\RoomMast;
use App\Models\RoomOcc;
use App\Models\RateList;
use App\Models\Paycharge;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RevenueManagementController extends Controller
{
    protected $propertyid;
    protected $ncurdate;

    public function __construct()
    {
        $this->propertyid = Auth::user()->propertyid ?? session('propertyid');
        $this->ncurdate = DB::table('enviro_general')
            ->where('propertyid', $this->propertyid)
            ->value('ncur') ?? date('Y-m-d');
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // REVENUE DASHBOARD — Analytics + AI Pricing Recommendations
    // ═══════════════════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $propertyid = $this->propertyid;
        $today = $this->ncurdate;

        // Room categories
        $categories = DB::table('room_cat')
            ->where('propertyid', $propertyid)
            ->where('type', 'RO')
            ->get();

        // Today's occupancy per category
        $occupancyData = [];
        foreach ($categories as $cat) {
            $totalRooms = DB::table('room_mast')
                ->where('propertyid', $propertyid)
                ->where('room_cat', $cat->cat_code)
                ->where('type', 'RO')
                ->count();

            $occupiedRooms = RoomOcc::where('propertyid', $propertyid)
                ->where('roomcat', $cat->cat_code)
                ->where('chkindate', '<=', $today)
                ->where(function ($q) use ($today) {
                    $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $today);
                })
                ->whereNull('Type')
                ->count();

            $availableRooms = max(0, $totalRooms - $occupiedRooms);
            $occupancyPct = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 1) : 0;

            // Current rate
            $currentRate = DB::table('rate_list')
                ->where('propertyid', $propertyid)
                ->where('room_cat', $cat->cat_code)
                ->where('occtype', 'singleuser')
                ->value('rate2') ?? 0;

            // AI recommended rate
            $recommendedRate = $this->calculateDynamicRate($cat->cat_code, $occupancyPct, $today);

            $occupancyData[] = [
                'cat_code' => $cat->cat_code,
                'name' => $cat->name,
                'shortname' => $cat->shortname,
                'total_rooms' => $totalRooms,
                'occupied' => $occupiedRooms,
                'available' => $availableRooms,
                'occupancy_pct' => $occupancyPct,
                'current_rate' => $currentRate,
                'recommended_rate' => $recommendedRate,
                'rate_diff' => $recommendedRate - $currentRate,
                'rate_change_pct' => $currentRate > 0 ? round((($recommendedRate - $currentRate) / $currentRate) * 100, 1) : 0,
            ];
        }

        // 7-day occupancy trend
        $trend = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $totalOcc = RoomOcc::where('propertyid', $propertyid)
                ->where('chkindate', '<=', $date)
                ->where(function ($q) use ($date) {
                    $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $date);
                })
                ->whereNull('Type')
                ->count();
            $totalRooms = DB::table('room_mast')
                ->where('propertyid', $propertyid)
                ->where('type', 'RO')
                ->count();
            $trend[] = [
                'date' => $date,
                'day' => Carbon::parse($date)->format('D'),
                'occupied' => $totalOcc,
                'total' => $totalRooms,
                'pct' => $totalRooms > 0 ? round(($totalOcc / $totalRooms) * 100, 1) : 0,
            ];
        }

        // Revenue metrics
        $monthStart = Carbon::now()->startOfMonth()->toDateString();
        $monthRevenue = Paycharge::where('propertyid', $propertyid)
            ->where('vdate', '>=', $monthStart)
            ->where('vtype', '!=', 'ADV')
            ->sum('amtdr');

        $monthPayments = Paycharge::where('propertyid', $propertyid)
            ->where('vdate', '>=', $monthStart)
            ->where('vtype', '!=', 'ADV')
            ->sum('amtcr');

        $totalRooms = DB::table('room_mast')
            ->where('propertyid', $propertyid)
            ->where('type', 'RO')
            ->count();

        $monthOccupied = RoomOcc::where('propertyid', $propertyid)
            ->where('chkindate', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $today);
            })
            ->whereNull('Type')
            ->count();

        $adr = $monthOccupied > 0 ? round($monthRevenue / max($monthOccupied, 1), 2) : 0;
        $revpar = $totalRooms > 0 ? round($monthRevenue / max($totalRooms, 1), 2) : 0;
        $occPct = $totalRooms > 0 ? round(($monthOccupied / $totalRooms) * 100, 1) : 0;

        return view('property.revenuedashboard', compact(
            'occupancyData', 'trend', 'monthRevenue', 'monthPayments',
            'adr', 'revpar', 'occPct', 'totalRooms', 'today'
        ));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // AI PRICING ENGINE — Dynamic rate calculation
    // ═══════════════════════════════════════════════════════════════════════════

    protected function calculateDynamicRate($catCode, $occupancyPct, $date, $occtype = 'singleuser')
    {
        $propertyid = $this->propertyid;

        // Base rate from rate_list
        $baseRate = DB::table('rate_list')
            ->where('propertyid', $propertyid)
            ->where('room_cat', $catCode)
            ->where('occtype', $occtype)
            ->value('rate2') ?? 5000;

        if ($baseRate <= 0) return 0;

        // ═══════════════════════════════════════════════════════════════
        // DEMAND SCORING ALGORITHM
        // ═══════════════════════════════════════════════════════════════
        $demandScore = 1.0; // Base multiplier

        // Factor 1: Current Occupancy (0-60% of score)
        if ($occupancyPct >= 90) {
            $demandScore += 0.35; // Very high demand → +35%
        } elseif ($occupancyPct >= 75) {
            $demandScore += 0.20; // High demand → +20%
        } elseif ($occupancyPct >= 60) {
            $demandScore += 0.10; // Moderate demand → +10%
        } elseif ($occupancyPct >= 40) {
            $demandScore += 0.00; // Normal → no change
        } elseif ($occupancyPct >= 20) {
            $demandScore -= 0.10; // Low demand → -10%
        } else {
            $demandScore -= 0.20; // Very low demand → -20%
        }

        // Factor 2: Day of week
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;
        if ($dayOfWeek === Carbon::FRIDAY || $dayOfWeek === Carbon::SATURDAY) {
            $demandScore += 0.15; // Weekend → +15%
        } elseif ($dayOfWeek === Carbon::SUNDAY) {
            $demandScore += 0.05; // Sunday → +5%
        } elseif ($dayOfWeek === Carbon::WEDNESDAY || $dayOfWeek === Carbon::THURSDAY) {
            $demandScore += 0.08; // Mid-week business → +8%
        }

        // Factor 3: Days until date (advance booking premium)
        $daysUntil = Carbon::parse($date)->diffInDays(Carbon::today());
        if ($daysUntil <= 1) {
            $demandScore += 0.10; // Last minute → +10%
        } elseif ($daysUntil >= 30) {
            $demandScore -= 0.05; // Far advance → -5%
        }

        // Factor 4: Historical occupancy for this date (same day last year)
        $lastYearDate = Carbon::parse($date)->subYear()->toDateString();
        $lastYearOcc = RoomOcc::where('propertyid', $propertyid)
            ->where('chkindate', '<=', $lastYearDate)
            ->where(function ($q) use ($lastYearDate) {
                $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $lastYearDate);
            })
            ->whereNull('Type')
            ->count();
        $lastYearPct = $totalRooms = DB::table('room_mast')
            ->where('propertyid', $propertyid)
            ->where('type', 'RO')
            ->count();
        $lastYearPct = $lastYearPct > 0 ? ($lastYearOcc / $lastYearPct) * 100 : 50;

        if ($lastYearPct >= 80) {
            $demandScore += 0.08; // Historically busy → +8%
        } elseif ($lastYearPct <= 30) {
            $demandScore -= 0.05; // Historically quiet → -5%
        }

        // Factor 5: Event/holiday detection (simple month-based)
        $month = Carbon::parse($date)->month;
        if (in_array($month, [10, 11, 12, 3, 4])) {
            $demandScore += 0.12; // Peak season (Oct-Dec, Mar-Apr) → +12%
        } elseif (in_array($month, [6, 7, 8])) {
            $demandScore -= 0.08; // Monsoon/off-season → -8%
        }

        // Clamp multiplier between 0.6 and 1.8
        $demandScore = max(0.6, min(1.8, $demandScore));

        // Calculate recommended rate
        $recommendedRate = round($baseRate * $demandScore, 0);

        // Ensure rate is within reasonable bounds (50%-200% of base)
        $minRate = round($baseRate * 0.5, 0);
        $maxRate = round($baseRate * 2.0, 0);
        $recommendedRate = max($minRate, min($maxRate, $recommendedRate));

        return $recommendedRate;
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // APPLY AI RATE — Update rates based on AI recommendations
    // ═══════════════════════════════════════════════════════════════════════════

    public function applyAIRates(Request $request)
    {
        $propertyid = $this->propertyid;
        $categories = $request->input('categories', []);

        $updated = 0;

        foreach ($categories as $catCode => $newRate) {
            if ($newRate <= 0) continue;

            // Update rate_list for this category
            $affected = DB::table('rate_list')
                ->where('propertyid', $propertyid)
                ->where('room_cat', $catCode)
                ->where('occtype', 'singleuser')
                ->update([
                    'rate2' => $newRate,
                    'u_updatedt' => now(),
                    'u_ae' => 'e',
                ]);

            $updated += $affected;
        }

        // Broadcast real-time update
        if ($updated > 0) {
            RealtimeController::broadcastNotification(
                $propertyid,
                'Rates Updated',
                "AI dynamic rates applied to $updated room categories",
                'success',
                'ri-price-tag-3-line'
            );
        }

        return response()->json([
            'success' => true,
            'message' => "Updated rates for $updated room records",
            'updated' => $updated,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // PRICING HISTORY — Track rate changes over time
    // ═══════════════════════════════════════════════════════════════════════════

    public function pricingHistory(Request $request)
    {
        $propertyid = $this->propertyid;
        $catCode = $request->input('category');
        $days = $request->input('days', 30);

        $history = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i)->toDateString();
            $occupancyPct = $this->getOccupancyForDate($date);
            $recommendedRate = $catCode
                ? $this->calculateDynamicRate($catCode, $occupancyPct, $date)
                : 0;

            $history[] = [
                'date' => $date,
                'occupancy_pct' => $occupancyPct,
                'recommended_rate' => $recommendedRate,
            ];
        }

        $categories = DB::table('room_cat')
            ->where('propertyid', $propertyid)
            ->where('type', 'RO')
            ->get();

        return view('property.revenuehistory', compact('history', 'categories', 'catCode', 'days'));
    }

    protected function getOccupancyForDate($date)
    {
        $totalRooms = DB::table('room_mast')
            ->where('propertyid', $this->propertyid)
            ->where('type', 'RO')
            ->count();

        if ($totalRooms <= 0) return 0;

        $occupied = RoomOcc::where('propertyid', $this->propertyid)
            ->where('chkindate', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $date);
            })
            ->whereNull('Type')
            ->count();

        return round(($occupied / $totalRooms) * 100, 1);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // RATE COMPARISON — Compare current vs AI-recommended vs channel rates
    // ═══════════════════════════════════════════════════════════════════════════

    public function rateComparison(Request $request)
    {
        $propertyid = $this->propertyid;
        $today = $this->ncurdate;
        $occtype = $request->input('occtype', 'singleuser') === 'multiuser' ? 'multiuser' : 'singleuser';

        $comparison = $this->buildRateComparison($today, $occtype);

        return view('property.revenueratecomparison', compact('comparison', 'occtype'));
    }

    // ═══════════════════════════════════════════════════════════════════════════
    // RATE COMPARISON DATA — JSON feed for the live AJAX comparison
    // ═══════════════════════════════════════════════════════════════════════════

    public function rateComparisonData(Request $request)
    {
        $today = $this->ncurdate;
        $occtype = $request->input('occtype', 'singleuser') === 'multiuser' ? 'multiuser' : 'singleuser';

        return response()->json([
            'occtype' => $occtype,
            'rows' => $this->buildRateComparison($today, $occtype),
        ]);
    }

    protected function buildRateComparison($today, $occtype)
    {
        $propertyid = $this->propertyid;

        $categories = DB::table('room_cat')
            ->where('propertyid', $propertyid)
            ->where('type', 'RO')
            ->get();

        $comparison = [];
        foreach ($categories as $cat) {
            $currentRate = DB::table('rate_list')
                ->where('propertyid', $propertyid)
                ->where('room_cat', $cat->cat_code)
                ->where('occtype', $occtype)
                ->value('rate2') ?? 0;

            $occupancyPct = $this->getOccupancyForDate($today);
            $aiRate = $this->calculateDynamicRate($cat->cat_code, $occupancyPct, $today, $occtype);

            // Channel rate (latest derived price pushed to channels)
            $channelRate = DB::table('channelderived')
                ->where('propertyid', $propertyid)
                ->orderByDesc('sn')
                ->value('price') ?? 0;

            $comparison[] = [
                'cat_code' => $cat->cat_code,
                'name' => $cat->name,
                'current_rate' => $currentRate,
                'ai_rate' => $aiRate,
                'channel_rate' => $channelRate,
                'difference' => $aiRate - $currentRate,
            ];
        }

        return $comparison;
    }
}
