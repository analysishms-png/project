<?php

namespace App\Http\Controllers;

use App\Events\RoomStatusChanged;
use App\Events\GuestCheckInOut;
use App\Events\PosActivity;
use App\Events\DashboardRevenueUpdate;
use App\Events\DashboardNotification;
use App\Models\RoomOcc;
use App\Models\Paycharge;
use App\Models\Sale1;
use App\Models\RoomMast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RealtimeController extends Controller
{
    /**
     * Broadcast room status change event.
     * Called after check-in, check-out, room change, housekeeping update.
     */
    public static function broadcastRoomStatus($propertyid, $roomNo, $status, $previousStatus = '', $extra = [])
    {
        // Get current room counts
        $today = Carbon::today()->toDateString();
        $occupied = RoomOcc::where('propertyid', $propertyid)
            ->whereNull('Type')
            ->where('chkindate', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $today);
            })
            ->count();

        $totalRooms = RoomMast::where('propertyid', $propertyid)->count();
        $oooCount = RoomMast::where('propertyid', $propertyid)
            ->where('roomstatus', 'OOO')
            ->count();
        $vacantDirty = DB::table('roomclean')
            ->where('propertyid', $propertyid)
            ->where('cleandate', $today)
            ->where('status', 'D')
            ->count();
        $vacantClean = max(0, $totalRooms - $occupied - $vacantDirty - $oooCount);

        event(new RoomStatusChanged(array_merge([
            'propertyid' => $propertyid,
            'roomno' => $roomNo,
            'status' => $status,
            'previous_status' => $previousStatus,
            'occupied_count' => $occupied,
            'vacant_clean_count' => $vacantClean,
            'vacant_dirty_count' => $vacantDirty,
            'out_of_order_count' => $oooCount,
        ], $extra)));

        // Also update dashboard revenue
        self::broadcastDashboardUpdate($propertyid);
    }

    /**
     * Broadcast check-in/check-out event.
     */
    public static function broadcastCheckInOut($propertyid, $type, $data = [])
    {
        event(new GuestCheckInOut(array_merge([
            'propertyid' => $propertyid,
            'type' => $type,
        ], $data)));
    }

    /**
     * Broadcast POS activity event.
     */
    public static function broadcastPosActivity($propertyid, $type, $data = [])
    {
        event(new PosActivity(array_merge([
            'propertyid' => $propertyid,
            'type' => $type,
        ], $data)));
    }

    /**
     * Broadcast dashboard revenue update.
     */
    public static function broadcastDashboardUpdate($propertyid)
    {
        $today = Carbon::today()->toDateString();

        // Today's room rent (credits from paycharge)
        $todayRoomRent = Paycharge::where('propertyid', $propertyid)
            ->where('vdate', $today)
            ->where('vtype', '!=', 'ADV')
            ->sum('dramt');

        // Today's POS revenue
        $todayPosRevenue = Sale1::where('propertyid', $propertyid)
            ->where('vdate', $today)
            ->sum('netamt');

        // Today's payments
        $todayPayments = Paycharge::where('propertyid', $propertyid)
            ->where('vdate', $today)
            ->where('vtype', '!=', 'ADV')
            ->sum('cramt');

        // Occupied rooms
        $totalRooms = RoomMast::where('propertyid', $propertyid)->count();
        $occupied = RoomOcc::where('propertyid', $propertyid)
            ->whereNull('Type')
            ->where('chkindate', '<=', $today)
            ->where(function ($q) use ($today) {
                $q->whereNull('chkoutdate')->orWhere('chkoutdate', '>=', $today);
            })
            ->count();

        $todayRevenue = $todayRoomRent + $todayPosRevenue;
        $adr = $occupied > 0 ? round($todayRoomRent / max($occupied, 1), 2) : 0;
        $revpar = $totalRooms > 0 ? round($todayRoomRent / max($totalRooms, 1), 2) : 0;
        $occupancyPct = $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 2) : 0;

        event(new DashboardRevenueUpdate([
            'propertyid' => $propertyid,
            'today_revenue' => $todayRevenue,
            'today_room_rent' => $todayRoomRent,
            'today_pos_revenue' => $todayPosRevenue,
            'today_payments' => $todayPayments,
            'total_occupied' => $occupied,
            'total_revenue' => $todayRevenue,
            'adr' => $adr,
            'revpar' => $revpar,
            'occupancy_pct' => $occupancyPct,
        ]));
    }

    /**
     * Broadcast general notification.
     */
    public static function broadcastNotification($propertyid, $title, $message, $type = 'info', $icon = 'ri-information-line')
    {
        event(new DashboardNotification([
            'propertyid' => $propertyid,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'icon' => $icon,
        ]));
    }
}
