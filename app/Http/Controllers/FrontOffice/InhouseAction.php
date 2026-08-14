<?php

namespace App\Http\Controllers\FrontOffice;

use App\Http\Controllers\Controller;
use App\Models\RoomOcc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InhouseAction extends Controller
{
    protected $username;
    protected $email;
    protected $propertyid;
    protected $currenttime;
    protected $ptlngth;
    protected $prpid;
    protected $compcode;
    protected $ncurdate;
    protected $datemanage;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->email = Auth::user()->email;
            $this->propertyid = Auth::user()->propertyid;
            $this->ptlngth = strlen($this->propertyid);
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');
            return $next($request);
        });
    }

    public function checkamountpayable(Request $request)
    {
        $docid = $request->input('docid');
        $sno1 = $request->input('sno1');

        $rocc = RoomOcc::where('propertyid', $this->propertyid)->where('docid', $docid)->where('leaderyn', 'Y')->first();
        if ($rocc) {
            $tbl = DB::table('paycharge')
                ->select(DB::raw('SUM(amtdr) as amtdr'), DB::raw('SUM(amtcr) as amtcr'), DB::raw('(SUM(amtdr) - SUM(amtcr)) as balance'))
                ->where('folionodocid', $docid)
                ->where('msno1', $rocc->sno1)
                ->first();
        } else {
            $tbl = DB::table('paycharge')
                ->select(DB::raw('SUM(amtdr) as amtdr'), DB::raw('SUM(amtcr) as amtcr'), DB::raw('(SUM(amtdr) - SUM(amtcr)) as balance'))
                ->where('folionodocid', $docid)
                ->where('sno1', $sno1)
                ->first();
        }

        return response()->json([
            'tbl' => $tbl
        ]);
    }
}
