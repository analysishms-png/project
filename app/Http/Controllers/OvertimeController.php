<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
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
        return view('property.hrpayroll.overtime');
    }

    public function showOvertime(Request $request)
    {
        $overtimes = DB::table('overtime')
            ->where('propertyid', $this->propertyid)
            ->orderByDesc('vdate')
            ->limit(100)
            ->get();

        return response()->json($overtimes);
    }

    public function overtimeStore(Request $request)
    {
        $request->validate([
            'vdate' => 'required|date',
            'empcode' => 'required',
            'hours' => 'required|numeric|min:0.5',
        ]);

        DB::table('overtime')->insert([
            'propertyid' => $this->propertyid,
            'vdate' => $request->vdate,
            'empcode' => $request->empcode,
            'hours' => $request->hours,
            'u_name' => Auth::user()->name ?? 'system',
            'u_entdt' => now(),
            'u_ae' => 'a',
        ]);

        return response()->json(['success' => true, 'message' => 'Overtime recorded']);
    }

    public function overtimeEdit($id)
    {
        $overtime = DB::table('overtime')
            ->where('propertyid', $this->propertyid)
            ->where('sn', $id)
            ->first();

        return view('property.hrpayroll.overtimeedit', compact('overtime'));
    }

    public function overtimeUpdate(Request $request, $id)
    {
        DB::table('overtime')
            ->where('sn', $id)
            ->where('propertyid', $this->propertyid)
            ->update([
                'vdate' => $request->input('vdate'),
                'hours' => $request->input('hours'),
                'u_updatedt' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Overtime updated']);
    }

    public function overtimeDelete($id)
    {
        DB::table('overtime')
            ->where('sn', $id)
            ->where('propertyid', $this->propertyid)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Overtime deleted']);
    }
}
