<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StaffMobileController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->propertyid = session('propertyid') ?? Auth::user()->propertyid ?? 0;
            return $next($request);
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // STAFF DASHBOARD — Today's tasks, attendance, productivity
    // ═══════════════════════════════════════════════════════════════
    public function dashboard(Request $request)
    {
        $prpid = $this->propertyid;
        $today = date('Y-m-d');
        $staffId = $request->input('staff_id');

        // Staff list for selection
        $staffList = DB::table('housekeeparmast')
            ->where('propertyid', $prpid)
            ->where('status', 'Y')
            ->orderBy('name')
            ->get();

        // Maintenance staff
        $maintStaff = DB::table('assets')
            ->where('propertyid', $prpid)
            ->selectRaw('DISTINCT type as department, COUNT(*) as asset_count')
            ->groupBy('type')
            ->get();

        // Today's cleaning tasks
        $cleaningTasks = [];
        $maintenanceTasks = [];

        if ($staffId) {
            // Cleaning tasks assigned to this staff
            $cleaningTasks = DB::table('hkcleaninghdr as H')
                ->leftJoin('room_mast as RM', 'RM.rcode', '=', 'H.roommo')
                ->where('H.propertyid', $prpid)
                ->where('H.cleaningdate', $today)
                ->where('H.housekeeperid', $staffId)
                ->select('H.*', 'RM.rcdesc as room_name')
                ->orderBy('H.roommo')
                ->get();

            // Maintenance tasks (damage reports / OOO)
            $maintenanceTasks = DB::table('hkdamagereport')
                ->where('propertyid', $prpid)
                ->where('vdate', $today)
                ->where('assignedto', $staffId)
                ->get();
        }

        // All today's tasks summary
        $allCleaning = DB::table('hkcleaninghdr')
            ->where('propertyid', $prpid)
            ->where('cleaningdate', $today)
            ->selectRaw('cleaningstatus, COUNT(*) as cnt')
            ->groupBy('cleaningstatus')
            ->get()
            ->pluck('cnt', 'cleaningstatus')
            ->toArray();

        // Check-in/out log for today
        $checkins = DB::table('staff_checkins')
            ->where('propertyid', $prpid)
            ->where('check_date', $today)
            ->orderByDesc('check_in')
            ->get();

        return view('property.staff.dashboard', compact(
            'staffList', 'maintStaff', 'cleaningTasks', 'maintenanceTasks',
            'allCleaning', 'checkins', 'staffId', 'today'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // TASK LIST — Mobile-optimized task view
    // ═══════════════════════════════════════════════════════════════
    public function taskList(Request $request)
    {
        $prpid = $this->propertyid;
        $today = date('Y-m-d');
        $staffId = $request->input('staff_id');
        $type = $request->input('type', 'all'); // cleaning, maintenance, all

        $staffList = DB::table('housekeeparmast')
            ->where('propertyid', $prpid)
            ->where('status', 'Y')
            ->orderBy('name')
            ->get();

        $tasks = collect();

        if ($staffId) {
            if ($type === 'all' || $type === 'cleaning') {
                $cleaning = DB::table('hkcleaninghdr as H')
                    ->leftJoin('room_mast as RM', 'RM.rcode', '=', 'H.roommo')
                    ->where('H.propertyid', $prpid)
                    ->where('H.cleaningdate', $today)
                    ->where('H.housekeeperid', $staffId)
                    ->select(
                        'H.cleaningid as id',
                        DB::raw("'cleaning' as task_type"),
                        'H.roommo as room_no',
                        'RM.rcdesc as room_name',
                        'H.cleaningstatus as status',
                        'H.starttime',
                        'H.endtime',
                        'H.cleantype as task_subtype',
                        DB::raw('NULL as priority'),
                        DB::raw('NULL as description')
                    )
                    ->get();
                $tasks = $tasks->concat($cleaning);
            }

            if ($type === 'all' || $type === 'maintenance') {
                $maintenance = DB::table('hkdamagereport')
                    ->where('propertyid', $prpid)
                    ->where('vdate', $today)
                    ->where('assignedto', $staffId)
                    ->select(
                        'sn as id',
                        DB::raw("'maintenance' as task_type"),
                        'roomno as room_no',
                        DB::raw('NULL as room_name'),
                        DB::raw("'Pending' as status"),
                        DB::raw('NULL as starttime'),
                        DB::raw('NULL as endtime'),
                        'damage_type as task_subtype',
                        DB::raw("'High' as priority"),
                        'description'
                    )
                    ->get();
                $tasks = $tasks->concat($maintenance);
            }
        }

        return view('property.staff.task-list', compact('tasks', 'staffList', 'staffId', 'type'));
    }

    // ═══════════════════════════════════════════════════════════════
    // TASK DETAIL — Full details for a specific task
    // ═══════════════════════════════════════════════════════════════
    public function taskDetail(Request $request, $taskId, $taskType = 'cleaning')
    {
        $prpid = $this->propertyid;
        $task = null;
        $checklist = [];
        $amenities = [];
        $inspectionItems = [];

        if ($taskType === 'cleaning') {
            $task = DB::table('hkcleaninghdr as H')
                ->leftJoin('room_mast as RM', 'RM.rcode', '=', 'H.roommo')
                ->leftJoin('housekeeparmast as HK', function ($j) use ($prpid) {
                    $j->on('HK.scode', '=', 'H.housekeeperid')
                      ->where('HK.propertyid', $prpid);
                })
                ->where('H.propertyid', $prpid)
                ->where('H.cleaningid', $taskId)
                ->select('H.*', 'RM.rcdesc as room_name', 'HK.name as hk_name')
                ->first();

            $checklist = DB::table('hkchecklistmast')
                ->where('propertyid', $prpid)
                ->orderBy('sno')
                ->get();

            $amenities = DB::table('hkamentiesmaster as A')
                ->leftJoin('itemmast as I', DB::raw('CAST(I.Code AS CHAR)'), '=', DB::raw('CAST(A.item AS CHAR)'))
                ->where('A.propertyid', $prpid)
                ->select('A.*', DB::raw('COALESCE(I.Name, A.item) as itemname'))
                ->orderBy('A.srno')
                ->get();

            // Get existing checklist responses
            $existingChecklist = DB::table('hkchecklistresp')
                ->where('propertyid', $prpid)
                ->where('cleaningid', $taskId)
                ->pluck('checkitem')
                ->toArray();

            $existingAmenities = DB::table('hkamenityresp')
                ->where('propertyid', $prpid)
                ->where('cleaningid', $taskId)
                ->pluck('itemcode')
                ->toArray();

            $inspectionItems = compact('existingChecklist', 'existingAmenities');
        } elseif ($taskType === 'maintenance') {
            $task = DB::table('hkdamagereport')
                ->where('propertyid', $prpid)
                ->where('sn', $taskId)
                ->first();
        }

        if (!$task) {
            return redirect()->back()->with('error', 'Task not found');
        }

        return view('property.staff.task-detail', compact('task', 'taskType', 'checklist', 'amenities', 'inspectionItems'));
    }

    // ═══════════════════════════════════════════════════════════════
    // CHECK-IN / CHECK-OUT — Staff attendance with GPS
    // ═══════════════════════════════════════════════════════════════
    public function staffCheckin(Request $request)
    {
        $prpid = $this->propertyid;
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        $request->validate([
            'staff_id' => 'required',
        ]);

        // Check if already checked in today
        $existing = DB::table('staff_checkins')
            ->where('propertyid', $prpid)
            ->where('staff_id', $request->staff_id)
            ->where('check_date', $today)
            ->whereNull('check_out')
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Already checked in. Please check out first.'
            ]);
        }

        DB::table('staff_checkins')->insert([
            'propertyid' => $prpid,
            'staff_id' => $request->staff_id,
            'staff_name' => $request->input('staff_name', ''),
            'department' => $request->input('department', 'Housekeeping'),
            'check_date' => $today,
            'check_in' => $now,
            'latitude_in' => $request->input('latitude'),
            'longitude_in' => $request->input('longitude'),
            'u_name' => Auth::user()->name ?? 'system',
            'u_entdt' => $now,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Check-in recorded at ' . date('h:i A'),
            'time' => date('h:i A')
        ]);
    }

    public function staffCheckout(Request $request)
    {
        $prpid = $this->propertyid;
        $today = date('Y-m-d');
        $now = date('Y-m-d H:i:s');

        $request->validate([
            'staff_id' => 'required',
        ]);

        $checkin = DB::table('staff_checkins')
            ->where('propertyid', $prpid)
            ->where('staff_id', $request->staff_id)
            ->where('check_date', $today)
            ->whereNull('check_out')
            ->first();

        if (!$checkin) {
            return response()->json([
                'success' => false,
                'message' => 'No active check-in found for today.'
            ]);
        }

        DB::table('staff_checkins')
            ->where('id', $checkin->id)
            ->update([
                'check_out' => $now,
                'latitude_out' => $request->input('latitude'),
                'longitude_out' => $request->input('longitude'),
                'u_updatedt' => $now,
            ]);

        // Calculate hours worked
        $hours = round((strtotime($now) - strtotime($checkin->check_in)) / 3600, 2);

        return response()->json([
            'success' => true,
            'message' => 'Check-out recorded. Hours worked: ' . $hours . 'h',
            'hours' => $hours
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // UPDATE TASK STATUS — Start, Complete, Pause
    // ═══════════════════════════════════════════════════════════════
    public function updateTaskStatus(Request $request)
    {
        $prpid = $this->propertyid;
        $now = date('Y-m-d H:i:s');

        $request->validate([
            'task_id' => 'required',
            'task_type' => 'required|in:cleaning,maintenance',
            'status' => 'required|in:In Progress,Completed,On Hold,Cancelled',
        ]);

        if ($request->task_type === 'cleaning') {
            $updateData = ['cleaningstatus' => $request->status];

            if ($request->status === 'In Progress' && !$request->starttime) {
                $updateData['starttime'] = date('h:i A');
            }
            if ($request->status === 'Completed') {
                $updateData['endtime'] = date('h:i A');
            }

            DB::table('hkcleaninghdr')
                ->where('propertyid', $prpid)
                ->where('cleaningid', $request->task_id)
                ->update($updateData);

            // Update room status if completed
            if ($request->status === 'Completed') {
                $hdr = DB::table('hkcleaninghdr')
                    ->where('cleaningid', $request->task_id)
                    ->first();

                if ($hdr) {
                    // Update room status to clean
                    DB::table('room_mast')
                        ->where('propertyid', $prpid)
                        ->where('rcode', $hdr->roommo)
                        ->update(['rstatus' => 'Clean']);

                    // Update hkroomassigns
                    DB::table('hkroomassigns')
                        ->where('propertyid', $prpid)
                        ->where('roomno', $hdr->roommo)
                        ->where('vdate', $hdr->cleaningdate)
                        ->update(['status' => 'clean', 'cleaningstatus' => 'Completed']);
                }
            }
        } elseif ($request->task_type === 'maintenance') {
            DB::table('hkdamagereport')
                ->where('propertyid', $prpid)
                ->where('sn', $request->task_id)
                ->update([
                    'status' => $request->status,
                    'u_updatedt' => $now,
                ]);
        }

        // Log the status change
        DB::table('staff_task_log')->insert([
            'propertyid' => $prpid,
            'task_id' => $request->task_id,
            'task_type' => $request->task_type,
            'status' => $request->status,
            'staff_id' => $request->input('staff_id'),
            'notes' => $request->input('notes', ''),
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'u_name' => Auth::user()->name ?? 'system',
            'created_at' => $now,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Task status updated to: ' . $request->status
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // SAVE CHECKLIST — Submit checklist items for a cleaning task
    // ═══════════════════════════════════════════════════════════════
    public function saveChecklist(Request $request)
    {
        $prpid = $this->propertyid;
        $now = date('Y-m-d H:i:s');

        $request->validate([
            'cleaning_id' => 'required',
            'items' => 'required|array',
        ]);

        // Delete existing responses for this cleaning
        DB::table('hkchecklistresp')
            ->where('propertyid', $prpid)
            ->where('cleaningid', $request->cleaning_id)
            ->delete();

        // Insert new responses
        foreach ($request->items as $item) {
            DB::table('hkchecklistresp')->insert([
                'propertyid' => $prpid,
                'cleaningid' => $request->cleaning_id,
                'checkitem' => $item['item'] ?? '',
                'status' => $item['status'] ?? 'N',
                'remark' => $item['remark'] ?? '',
                'u_name' => Auth::user()->name ?? 'system',
                'u_entdt' => $now,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Checklist saved (' . count($request->items) . ' items)'
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // TASK LOG — History of task status changes
    // ═══════════════════════════════════════════════════════════════
    public function taskLog(Request $request)
    {
        $prpid = $this->propertyid;

        $logs = DB::table('staff_task_log')
            ->where('propertyid', $prpid)
            ->orderByDesc('created_at')
            ->limit(200)
            ->get();

        return view('property.staff.task-log', compact('logs'));
    }

    // ═══════════════════════════════════════════════════════════════
    // PRODUCTIVITY REPORT — Staff efficiency metrics
    // ═══════════════════════════════════════════════════════════════
    public function productivity(Request $request)
    {
        $prpid = $this->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-01'));
        $todate = $request->input('todate', date('Y-m-d'));

        // Staff productivity from cleaning tasks
        $productivity = DB::table('hkcleaninghdr as H')
            ->leftJoin('housekeeparmast as HK', function ($j) use ($prpid) {
                $j->on('HK.scode', '=', 'H.housekeeperid')
                  ->where('HK.propertyid', $prpid);
            })
            ->where('H.propertyid', $prpid)
            ->whereBetween('H.cleaningdate', [$fromdate, $todate])
            ->select(
                'H.housekeeperid',
                DB::raw('COALESCE(HK.name, H.housekeeperid) as staff_name'),
                DB::raw('COUNT(*) as total_tasks'),
                DB::raw("SUM(CASE WHEN H.cleaningstatus = 'Completed' THEN 1 ELSE 0 END) as completed"),
                DB::raw("SUM(CASE WHEN H.cleaningstatus = 'In Progress' THEN 1 ELSE 0 END) as in_progress"),
                DB::raw("SUM(CASE WHEN H.cleaningstatus = 'Cancelled' THEN 1 ELSE 0 END) as cancelled"),
                DB::raw("ROUND(AVG(TIMESTAMPDIFF(MINUTE, STR_TO_DATE(H.starttime, '%h:%i %p'), STR_TO_DATE(H.endtime, '%h:%i %p'))), 1) as avg_mins")
            )
            ->groupBy('H.housekeeperid', 'staff_name')
            ->orderByDesc('completed')
            ->get();

        // Daily task summary
        $dailySummary = DB::table('hkcleaninghdr')
            ->where('propertyid', $prpid)
            ->whereBetween('cleaningdate', [$fromdate, $todate])
            ->selectRaw('cleaningdate, cleaningstatus, COUNT(*) as cnt')
            ->groupBy('cleaningdate', 'cleaningstatus')
            ->orderBy('cleaningdate')
            ->get();

        // Check-in summary
        $checkinSummary = DB::table('staff_checkins')
            ->where('propertyid', $prpid)
            ->whereBetween('check_date', [$fromdate, $todate])
            ->selectRaw('check_date, COUNT(DISTINCT staff_id) as staff_count, 
                         ROUND(AVG(TIMESTAMPDIFF(MINUTE, check_in, COALESCE(check_out, NOW()))), 1) as avg_hours')
            ->groupBy('check_date')
            ->orderBy('check_date')
            ->get();

        return view('property.staff.productivity', compact(
            'productivity', 'dailySummary', 'checkinSummary', 'fromdate', 'todate'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // API: Get tasks for mobile app (JSON)
    // ═══════════════════════════════════════════════════════════════
    public function apiTasks(Request $request)
    {
        $prpid = $this->propertyid;
        $today = date('Y-m-d');
        $staffId = $request->input('staff_id');

        if (!$staffId) {
            return response()->json(['error' => 'staff_id required'], 400);
        }

        $tasks = DB::table('hkcleaninghdr as H')
            ->leftJoin('room_mast as RM', 'RM.rcode', '=', 'H.roommo')
            ->where('H.propertyid', $prpid)
            ->where('H.cleaningdate', $today)
            ->where('H.housekeeperid', $staffId)
            ->select('H.cleaningid as id', 'H.roommo as room', 'RM.rcdesc as room_name',
                     'H.cleaningstatus as status', 'H.cleantype', 'H.starttime', 'H.endtime')
            ->get();

        return response()->json([
            'staff_id' => $staffId,
            'date' => $today,
            'tasks' => $tasks
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // API: Get room details by QR code
    // ═══════════════════════════════════════════════════════════════
    public function apiQrScan(Request $request)
    {
        $prpid = $this->propertyid;
        $roomNo = $request->input('room_no');

        if (!$roomNo) {
            return response()->json(['error' => 'room_no required'], 400);
        }

        $room = DB::table('room_mast')
            ->where('propertyid', $prpid)
            ->where('rcode', $roomNo)
            ->first();

        $latestCleaning = DB::table('hkcleaninghdr')
            ->where('propertyid', $prpid)
            ->where('roommo', $roomNo)
            ->orderByDesc('cleaningid')
            ->first();

        return response()->json([
            'room' => $room,
            'latest_cleaning' => $latestCleaning,
        ]);
    }
}
