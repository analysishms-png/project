<?php

namespace App\Http\Controllers\MainSetup\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\EnviroFom;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FomParameter extends Controller
{
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }
            $this->propertyid = Auth::user()->propertyid;
            return $next($request);
        });
    }

    public function housekeepingparamstore(Request $request)
    {
        $request->validate([
            'autoroomassign' => 'required|numeric|min:0|max:1',
            'housekeepingroomstatus' => 'required|numeric|min:0|max:1',
            'googlereviewurl' => 'nullable|url'
        ]);

        EnviroFom::where('propertyid', $this->propertyid)
            ->update([
                'autoroomassign' => $request->autoroomassign,
                'housekeepingroomstatus' => $request->housekeepingroomstatus,
                'googlereviewurl' => $request->googlereviewurl
            ]);

        return back()->with('success', 'House Keeping Updated');
    }
}
