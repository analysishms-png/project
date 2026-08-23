<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class SmartRoomController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->propertyid = session('propertyid') ?? Auth::user()->propertyid ?? 0;
            return $next($request);
        });
    }

    public function dashboard(Request $request)
    {
        $prpid = $this->propertyid;
        $rooms = DB::table('room_mast as RM')
            ->leftJoin('roomocc as RO', function ($j) use ($prpid) {
                $j->on('RO.roomno', '=', 'RM.rcode')->where('RO.propertyid', $prpid)->where('RO.activeYN', 'Y');
            })
            ->where('RM.propertyid', $prpid)
            ->select('RM.sno', 'RM.rcode as room_no', 'RM.name as room_name', 'RM.room_cat', 'RM.room_stat', 'RO.name as guest_name', 'RO.chkindate', 'RO.depdate')
            ->orderBy('RM.rcode')->get();

        $deviceSummary = DB::table('smart_devices')->where('propertyid', $prpid)
            ->selectRaw('device_type, COUNT(*) as total, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as active')
            ->groupBy('device_type')->get();

        $energyToday = DB::table('device_logs')->where('propertyid', $prpid)->whereDate('created_at', date('Y-m-d'))
            ->selectRaw('SUM(power_watts * duration_min / 60) / 1000 as kwh')->value('kwh') ?? 0;

        $activeScenes = DB::table('smart_scenes')->where('propertyid', $prpid)->where('is_active', 1)->get();
        $recentActivity = DB::table('device_logs as DL')->leftJoin('smart_devices as SD', 'SD.id', '=', 'DL.device_id')
            ->where('DL.propertyid', $prpid)->orderByDesc('DL.created_at')->limit(20)->get();
        $alerts = DB::table('device_alerts')->where('propertyid', $prpid)->where('is_resolved', 0)
            ->orderByDesc('created_at')->limit(10)->get();

        return view('property.smartroom.dashboard', compact('rooms', 'deviceSummary', 'energyToday', 'activeScenes', 'recentActivity', 'alerts'));
    }

    public function roomControl(Request $request, $roomNo)
    {
        $prpid = $this->propertyid;
        $room = DB::table('room_mast')->where('propertyid', $prpid)->where('rcode', $roomNo)->first();
        if (!$room) return redirect()->back()->with('error', 'Room not found');
        $guest = DB::table('roomocc')->where('propertyid', $prpid)->where('roomno', $roomNo)->where('activeYN', 'Y')
            ->select('name', 'chkindate', 'depdate', 'adult', 'children')->first();
        $devices = DB::table('smart_devices')->where('propertyid', $prpid)->where('room_no', $roomNo)->orderBy('device_type')->get();
        $logs = DB::table('device_logs as DL')->leftJoin('smart_devices as SD', 'SD.id', '=', 'DL.device_id')
            ->where('DL.propertyid', $prpid)->where('SD.room_no', $roomNo)
            ->where('DL.created_at', '>=', date('Y-m-d H:i:s', strtotime('-24 hours')))
            ->orderByDesc('DL.created_at')->limit(50)->get();
        $scenes = DB::table('smart_scenes')->where('propertyid', $prpid)->get();
        return view('property.smartroom.room-control', compact('room', 'guest', 'devices', 'logs', 'scenes', 'roomNo'));
    }

    public function devices(Request $request)
    {
        $prpid = $this->propertyid;
        $devices = DB::table('smart_devices')->where('propertyid', $prpid)->orderBy('room_no')->orderBy('device_type')->get();
        $stats = DB::table('smart_devices')->where('propertyid', $prpid)
            ->selectRaw('COUNT(*) as total, SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as online, SUM(CASE WHEN status = 0 THEN 1 ELSE 0 END) as offline, SUM(CASE WHEN battery_level < 20 THEN 1 ELSE 0 END) as low_battery')
            ->first();
        $rooms = DB::table('room_mast')->where('propertyid', $prpid)->orderBy('rcode')->pluck('rcode')->toArray();
        return view('property.smartroom.devices', compact('devices', 'stats', 'rooms'));
    }

    public function addDevice(Request $request)
    {
        $request->validate([
            'room_no' => 'required', 'device_type' => 'required|in:light,ac,curtain,tv,sensor,lock,thermostat,speaker,camera,doorbell,motion,power',
            'device_name' => 'required|string|max:100', 'protocol' => 'required|in:wifi,zigbee,z-wave,bluetooth,mqtt,http',
        ]);
        DB::table('smart_devices')->insert([
            'propertyid' => $this->propertyid, 'room_no' => $request->room_no, 'device_type' => $request->device_type,
            'device_name' => $request->device_name, 'protocol' => $request->protocol,
            'ip_address' => $request->input('ip_address'), 'power_watts' => $request->input('power_watts', 0),
            'status' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Device added']);
    }

    public function updateDevice(Request $request, $deviceId)
    {
        $updateData = $request->only(['device_name', 'room_no', 'ip_address', 'power_watts']);
        $updateData['updated_at'] = now();
        DB::table('smart_devices')->where('id', $deviceId)->where('propertyid', $this->propertyid)->update($updateData);
        return response()->json(['success' => true, 'message' => 'Device updated']);
    }

    public function deleteDevice(Request $request, $deviceId)
    {
        DB::table('smart_devices')->where('id', $deviceId)->where('propertyid', $this->propertyid)->delete();
        return response()->json(['success' => true, 'message' => 'Device removed']);
    }

    public function toggleDevice(Request $request)
    {
        $request->validate(['device_id' => 'required|integer', 'action' => 'required|in:on,off,dim,lock,unlock,set_temp,set_scene']);
        $device = DB::table('smart_devices')->where('id', $request->device_id)->where('propertyid', $this->propertyid)->first();
        if (!$device) return response()->json(['success' => false, 'message' => 'Device not found']);
        $newStatus = in_array($request->action, ['on', 'unlock', 'set_scene']) ? 1 : 0;
        $updateData = ['status' => $newStatus, 'updated_at' => now()];
        if ($request->action === 'dim' && $request->has('brightness')) $updateData['brightness'] = max(0, min(100, (int) $request->brightness));
        if ($request->action === 'set_temp' && $request->has('temperature')) { $updateData['temperature'] = (float) $request->temperature; $updateData['status'] = 1; }
        DB::table('smart_devices')->where('id', $request->device_id)->update($updateData);
        DB::table('device_logs')->insert([
            'propertyid' => $this->propertyid, 'device_id' => $request->device_id, 'action' => $request->action,
            'value' => $request->input('brightness', $request->input('temperature', null)),
            'performed_by' => Auth::user()->name ?? 'system', 'created_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => ucfirst(str_replace('_', ' ', $request->action)) . ' command sent']);
    }

    public function scenes(Request $request)
    {
        $scenes = DB::table('smart_scenes')->where('propertyid', $this->propertyid)->orderBy('name')->get();
        return view('property.smartroom.scenes', compact('scenes'));
    }

    public function createScene(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100']);
        $sceneId = DB::table('smart_scenes')->insertGetId([
            'propertyid' => $this->propertyid, 'name' => $request->name,
            'description' => $request->input('description', ''), 'icon' => $request->input('icon', 'fa-lightbulb'),
            'color' => $request->input('color', '#667eea'), 'is_active' => 0, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return response()->json(['success' => true, 'scene_id' => $sceneId]);
    }

    public function activateScene(Request $request, $sceneId)
    {
        $scene = DB::table('smart_scenes')->where('id', $sceneId)->where('propertyid', $this->propertyid)->first();
        if (!$scene) return response()->json(['success' => false, 'message' => 'Scene not found']);
        $sceneDevices = DB::table('scene_devices')->where('scene_id', $sceneId)->get();
        $applied = 0;
        foreach ($sceneDevices as $sd) {
            $updateData = ['status' => $sd->target_status, 'updated_at' => now()];
            if ($sd->target_brightness !== null) $updateData['brightness'] = $sd->target_brightness;
            if ($sd->target_temperature !== null) $updateData['temperature'] = $sd->target_temperature;
            DB::table('smart_devices')->where('id', $sd->device_id)->where('propertyid', $this->propertyid)->update($updateData);
            DB::table('device_logs')->insert([
                'propertyid' => $this->propertyid, 'device_id' => $sd->device_id, 'action' => 'scene_' . $scene->name,
                'value' => $sd->target_status, 'performed_by' => 'scene:' . $scene->name, 'created_at' => now(),
            ]);
            $applied++;
        }
        DB::table('smart_scenes')->where('id', $sceneId)->update(['is_active' => 1, 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Scene activated - ' . $applied . ' devices updated']);
    }

    public function deactivateScene(Request $request, $sceneId)
    {
        DB::table('smart_scenes')->where('id', $sceneId)->where('propertyid', $this->propertyid)->update(['is_active' => 0, 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'Scene deactivated']);
    }

    public function addSceneDevice(Request $request)
    {
        $request->validate(['scene_id' => 'required|integer', 'device_id' => 'required|integer', 'target_status' => 'required|integer']);
        DB::table('scene_devices')->insert([
            'scene_id' => $request->scene_id, 'device_id' => $request->device_id,
            'target_status' => $request->target_status, 'target_brightness' => $request->input('target_brightness'),
            'target_temperature' => $request->input('target_temperature'), 'created_at' => now(),
        ]);
        return response()->json(['success' => true, 'message' => 'Device added to scene']);
    }

    public function removeSceneDevice(Request $request, $id)
    {
        DB::table('scene_devices')->where('id', $id)->delete();
        return response()->json(['success' => true]);
    }

    public function energy(Request $request)
    {
        $prpid = $this->propertyid;
        $fromdate = $request->input('fromdate', date('Y-m-d'));
        $todate = $request->input('todate', date('Y-m-d'));
        $totalByType = DB::table('device_logs as DL')->leftJoin('smart_devices as SD', 'SD.id', '=', 'DL.device_id')
            ->where('DL.propertyid', $prpid)->whereBetween('DL.created_at', [$fromdate.' 00:00:00', $todate.' 23:59:59'])
            ->selectRaw('SD.device_type, SUM(SD.power_watts * DL.duration_min / 60) / 1000 as kwh, COUNT(*) as events')
            ->groupBy('SD.device_type')->orderByDesc('kwh')->get();
        $byRoom = DB::table('device_logs as DL')->leftJoin('smart_devices as SD', 'SD.id', '=', 'DL.device_id')
            ->where('DL.propertyid', $prpid)->whereBetween('DL.created_at', [$fromdate.' 00:00:00', $todate.' 23:59:59'])
            ->selectRaw('SD.room_no, SUM(SD.power_watts * DL.duration_min / 60) / 1000 as kwh')
            ->groupBy('SD.room_no')->orderByDesc('kwh')->get();
        $peakHours = DB::table('device_logs as DL')->leftJoin('smart_devices as SD', 'SD.id', '=', 'DL.device_id')
            ->where('DL.propertyid', $prpid)->whereBetween('DL.created_at', [$fromdate.' 00:00:00', $todate.' 23:59:59'])
            ->selectRaw('HOUR(DL.created_at) as hour, SUM(SD.power_watts * DL.duration_min / 60) / 1000 as kwh')
            ->groupBy('hour')->orderBy('hour')->get();
        $totalKwh = $totalByType->sum('kwh');
        $estimatedCost = round($totalKwh * 8, 2);
        $dailyByType = collect();
        return view('property.smartroom.energy', compact('dailyByType', 'totalByType', 'byRoom', 'peakHours', 'totalKwh', 'estimatedCost', 'fromdate', 'todate'));
    }

    public function alerts(Request $request)
    {
        $alerts = DB::table('device_alerts')->where('propertyid', $this->propertyid)->orderByDesc('created_at')->limit(100)->get();
        return view('property.smartroom.alerts', compact('alerts'));
    }

    public function resolveAlert(Request $request, $alertId)
    {
        DB::table('device_alerts')->where('id', $alertId)->where('propertyid', $this->propertyid)
            ->update(['is_resolved' => 1, 'resolved_at' => now(), 'resolved_by' => Auth::user()->name ?? 'system']);
        return response()->json(['success' => true, 'message' => 'Alert resolved']);
    }

    public function guestPortal(Request $request, $roomNo)
    {
        $prpid = $this->propertyid;
        $room = DB::table('room_mast')->where('propertyid', $prpid)->where('rcode', $roomNo)->first();
        if (!$room) return view('property.smartroom.guest-portal-error');
        $guest = DB::table('roomocc')->where('propertyid', $prpid)->where('roomno', $roomNo)->where('activeYN', 'Y')
            ->select('name', 'chkindate', 'depdate')->first();
        $devices = DB::table('smart_devices')->where('propertyid', $prpid)->where('room_no', $roomNo)->where('guest_accessible', 1)->get();
        $scenes = DB::table('smart_scenes')->where('propertyid', $prpid)->where('is_guest_accessible', 1)->get();
        return view('property.smartroom.guest-portal', compact('room', 'guest', 'devices', 'scenes', 'roomNo'));
    }

    public function apiRoomDevices(Request $request, $roomNo)
    {
        $devices = DB::table('smart_devices')->where('propertyid', $this->propertyid)->where('room_no', $roomNo)->get();
        return response()->json(['room' => $roomNo, 'devices' => $devices]);
    }

    public function apiToggle(Request $request) { return $this->toggleDevice($request); }
    public function apiSceneActivate(Request $request, $sceneId) { return $this->activateScene($request, $sceneId); }

    public function apiDeviceStatus(Request $request)
    {
        $device = DB::table('smart_devices')->where('id', $request->input('device_id'))->where('propertyid', $this->propertyid)->first();
        return response()->json($device ?: ['error' => 'Device not found']);
    }

    public function roomAllOff(Request $request, $roomNo)
    {
        DB::table('smart_devices')->where('propertyid', $this->propertyid)->where('room_no', $roomNo)->update(['status' => 0, 'updated_at' => now()]);
        DB::table('device_logs')->insert(['propertyid' => $this->propertyid, 'device_id' => 0, 'action' => 'room_all_off', 'value' => 0, 'performed_by' => Auth::user()->name ?? 'system', 'created_at' => now()]);
        return response()->json(['success' => true, 'message' => 'All devices off in room ' . $roomNo]);
    }

    public function roomAllOn(Request $request, $roomNo)
    {
        DB::table('smart_devices')->where('propertyid', $this->propertyid)->where('room_no', $roomNo)->where('device_type', '!=', 'lock')->update(['status' => 1, 'updated_at' => now()]);
        return response()->json(['success' => true, 'message' => 'All devices on in room ' . $roomNo]);
    }
}
