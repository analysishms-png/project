<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->propertyid = session('propertyid') ?? Auth::user()->propertyid ?? 0;
            return $next($request);
        });
    }

    public function index()
    {
        return view('property.hrpayroll.leave');
    }

    public function showLeave(Request $request)
    {
        $leaves = DB::table('attendance')
            ->where('propertyid', $this->propertyid)
            ->orderByDesc('vdate')
            ->limit(100)
            ->get();

        return response()->json($leaves);
    }

    public function leaveStore(Request $request)
    {
        $request->validate([
            'date_from' => 'required|date',
            'date_to' => 'required|date',
            'empcode' => 'required',
            'leave_type' => 'nullable|string',
        ]);

        $prpid = $this->propertyid;
        $dateFrom = $request->date_from;
        $dateTo = $request->date_to;

        // Insert attendance record for each day
        $current = strtotime($dateFrom);
        $end = strtotime($dateTo);

        while ($current <= $end) {
            $vdate = date('Y-m-d', $current);
            $dayOfWeek = date('w', $current);

            // Skip if already exists
            $exists = DB::table('attendance')
                ->where('propertyid', $prpid)
                ->where('empcode', $request->empcode)
                ->where('vdate', $vdate)
                ->first();

            if (!$exists) {
                DB::table('attendance')->insert([
                    'propertyid' => $prpid,
                    'empcode' => $request->empcode,
                    'vdate' => $vdate,
                    'date_from' => $dateFrom,
                    'date_to' => $dateTo,
                    'firstshift' => 0,
                    'secondshift' => 0,
                    'u_name' => Auth::user()->name ?? 'system',
                    'u_entdt' => now(),
                    'u_ae' => 'a',
                ]);
            }

            $current = strtotime('+1 day', $current);
        }

        return response()->json(['success' => true, 'message' => 'Leave recorded']);
    }

    public function leaveEdit($id)
    {
        $leave = DB::table('attendance')
            ->where('propertyid', $this->propertyid)
            ->where('sn', $id)
            ->first();

        return view('property.hrpayroll.leaveupdate', compact('leave'));
    }

    public function leaveUpdate(Request $request, $id)
    {
        DB::table('attendance')
            ->where('sn', $id)
            ->where('propertyid', $this->propertyid)
            ->update([
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'u_updatedt' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Leave updated']);
    }

    public function leaveDelete($id)
    {
        DB::table('attendance')
            ->where('sn', $id)
            ->where('propertyid', $this->propertyid)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Leave deleted']);
    }
}
