<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
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
        return view('property.hrpayroll.loanadvanceentry');
    }

    public function showLoan(Request $request)
    {
        $loans = DB::table('loan')
            ->where('propertyid', $this->propertyid)
            ->orderByDesc('vdate')
            ->limit(100)
            ->get();

        return response()->json($loans);
    }

    public function loanStore(Request $request)
    {
        $request->validate([
            'vdate' => 'required|date',
            'empcode' => 'required',
            'amount' => 'required|numeric|min:1',
            'loan_type' => 'nullable|in:loan,advance',
        ]);

        DB::table('loan')->insert([
            'propertyid' => $this->propertyid,
            'vdate' => $request->vdate,
            'empcode' => $request->empcode,
            'amount' => $request->amount,
            'loan_type' => $request->input('loan_type', 'loan'),
            'u_name' => Auth::user()->name ?? 'system',
            'u_entdt' => now(),
            'u_ae' => 'a',
        ]);

        return response()->json(['success' => true, 'message' => 'Loan/Advance recorded']);
    }

    public function loanEdit($id)
    {
        $loan = DB::table('loan')
            ->where('propertyid', $this->propertyid)
            ->where('sn', $id)
            ->first();

        return view('property.hrpayroll.loanadvanceentryedit', compact('loan'));
    }

    public function loanUpdate(Request $request, $id)
    {
        DB::table('loan')
            ->where('sn', $id)
            ->where('propertyid', $this->propertyid)
            ->update([
                'vdate' => $request->input('vdate'),
                'amount' => $request->input('amount'),
                'u_updatedt' => now(),
            ]);

        return response()->json(['success' => true, 'message' => 'Loan updated']);
    }

    public function loanDelete($id)
    {
        DB::table('loan')
            ->where('sn', $id)
            ->where('propertyid', $this->propertyid)
            ->delete();

        return response()->json(['success' => true, 'message' => 'Loan deleted']);
    }
}
