<?php

namespace App\Http\Controllers;

use App\Models\HkFloor;
use App\Models\HkSupervisor;
use App\Models\HousekeeperMast;
use App\Models\RoomBlockout;
use App\Models\RoomClean;
use App\Models\RoomMast;
use App\Models\UpdateLog;
use App\Models\Companyreg;
use App\Models\States;
use App\Models\Guestwakeup;
use App\Models\Guestmessage;
use App\Models\RoomOcc;
// created by ananya
// created by ananya
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
//hkQRcode generator
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Illuminate\Support\Arr;



use function App\Helpers\getNcurDate;

class HouseKeeping extends Controller
{
    protected $username;

    protected $email;

    protected $propertyid;

    protected $currenttime;

    protected $ptlngth;

    protected $prpid;

    protected $ncurdate;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->email = Auth::user()->email;
            $this->prpid = Auth::user()->propertyid;
            $propertydata = DB::table('users')->where('propertyid', $this->prpid)->first();
            $this->ncurdate = DB::table('enviro_general')->where('propertyid', $this->propertyid)->value('ncur');
            $this->propertyid = $propertydata->propertyid;
            $this->ptlngth = strlen($this->propertyid);
            date_default_timezone_set('Asia/Kolkata');
            $this->currenttime = date('Y-m-d H:i:s');

            return $next($request);
        });
    }
    // Warning: Abandon hope, all who enter here. 😱

    public function housekeepingscreen(Request $request)
    {
        $permission = revokeopen(151111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $rooms = DB::table('room_mast')
            ->select(
                'room_mast.rcode as roomno',
                'room_mast.room_stat',
                'room_mast.rest_code',
                'roomocc.roomno as roomnoroomocc',
                'roomocc.type',
                DB::raw("'' as status"),
                DB::raw("
                CASE  
                    WHEN roomocc.type = 'O' OR roomocc.roomno IS NULL OR roomocc.type = 'C' THEN 'fa-door-open text-success' 
                    ELSE 'fa-door-closed text-danger' 
                END AS ficon
            "),
                'roomblockout.block'
            )
            ->leftJoin('roomocc', function ($join) {
                $join->on('roomocc.roomno', '=', 'room_mast.rcode')
                    ->where('roomocc.propertyid', '=', $this->propertyid)
                    ->whereRaw('roomocc.sn = (SELECT MAX(sn) FROM roomocc WHERE roomno = room_mast.rcode AND propertyid = ?)', [$this->propertyid]);
            })
            ->leftJoin('roomblockout', function ($join) {
                $join->on('roomblockout.roomcode', '=', 'room_mast.rcode')
                    ->whereNull('roomblockout.cleardate')
                    ->where('roomblockout.propertyid', $this->propertyid);
            })
            ->where('room_mast.propertyid', $this->propertyid)
            ->where('room_mast.type', 'RO')
            ->where('room_mast.inclcount', 'Y')
            ->groupBy('room_mast.rcode')
            ->get();

        $totaloccupied = 0;

        foreach ($rooms as $row) {
            if ($row->type == null) {
                $totaloccupied++;
            }
        }

        $housekeeper = HousekeeperMast::where('propertyid', $this->propertyid)
            ->orderBy('name', 'ASC')
            ->get();

        // exit;

        // return $rooms;

        return view('property.housekeeping', [
            'rooms' => $rooms,
            'totaloccupied' => $totaloccupied,
            'housekeeper' => $housekeeper,
        ]);
    }

    public function housemaster(Request $request)
    {
        // BUG-045: guard with the canonical housekeeping code 151112 first,
        // falling back to the legacy duplicate 121512 (some older props/users
        // only have 121512). Previously the guard ONLY checked 121512, which
        // blocked housemaster on props like 135 that use 151112.
        $permission = revokeopen(151112) ?? revokeopen(121512);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $data = HousekeeperMast::where('propertyid', $this->propertyid)
            ->orderBy('name', 'ASC')
            ->get();

        return view('property.housemaster', ['data' => $data]);
    }

    public function submithousemaster(Request $request)
    {
        // BUG-045: canonical 151112 with legacy 121512 fallback (see housemaster()).
        $permission = revokeopen(151112) ?? revokeopen(121512);
        if (is_null($permission) || $permission->ins == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $data = $request->except('_token');
        $scode = HousekeeperMast::where('propertyid', $this->propertyid)->max('scode');
        if ($scode === null) {
            $scode = 1;
        } else {
            $scode = intval(substr($scode, 0, -3)) + 1;
        }

        $existingName = HousekeeperMast::where('name', $data['name'])
            ->where('propertyid', $this->propertyid)
            ->first();

        if ($existingName) {
            return back()->with('error', 'House Keeping Master Name already exists!');
        }

        try {
            $insertdata = [
                'u_entdt' => $this->currenttime,
                'scode' => $scode . $this->propertyid,
                'u_name' => Auth::user()->u_name,
                'propertyid' => $this->propertyid,
                'u_ae' => 'a',
            ] + $data;

            HousekeeperMast::insert($insertdata);

            return back()->with('success', 'House Keeping Master Inserted successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Unable to Insert House Keeping Master!' . $e->getMessage());
        }
    }

    public function updatehousemaster(Request $request)
    {
        // BUG-045: canonical 151112 with legacy 121512 fallback (see housemaster()).
        $permission = revokeopen(151112) ?? revokeopen(121512);
        if (is_null($permission) || $permission->edit == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $existingName = HousekeeperMast::where('name', $request->input('updatename'))
            ->whereNot('scode', $request->input('updatecode'))
            ->where('propertyid', $this->propertyid)
            ->first();

        if ($existingName) {
            return back()->with('error', 'House Keeping Master Name Already Exists!');
        }

        try {
            DB::beginTransaction();
            $updatedata = [
                'name' => $request->input('updatename'),
                'activeYN' => $request->input('upactiveYN'),
                'u_updatedt' => $this->currenttime,
                'u_name' => Auth::user()->u_name,
                'propertyid' => $this->propertyid,
                'u_ae' => 'e',
            ];
            HousekeeperMast::where('scode', $request->input('updatecode'))
                ->where('propertyid', $this->propertyid)
                ->update($updatedata);

            // ── Employee Sync ─────────────────────────────────────────────────────
            $empid = HousekeeperMast::where('scode', $request->input('updatecode'))
                ->where('propertyid', $this->propertyid)
                ->value('empid');

            if ($empid) {
                DB::table('employee')
                    ->where('code', $empid)
                    ->where('propertyid', $this->propertyid)
                    ->update([
                        'name'       => $request->input('updatename'),
                        'activeyn'   => $request->input('upactiveYN') === 'Y' ? 'Y' : 'N',
                        'u_name'     => Auth::user()->u_name,
                        'u_updatedt' => $this->currenttime,
                        'u_ae'       => 'e',
                    ]);
            }
            // ─────────────────────────────────────────────────────────────────────

            DB::commit();
            return back()->with('success', 'House Keeping Master Updated successfully!');
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function deletehousekeepingmaster(Request $request, $sn, $ucode)
    {
        // BUG-045: canonical 151112 with legacy 121512 fallback (see housemaster()).
        $permission = revokeopen(151112) ?? revokeopen(121512);
        if (is_null($permission) || $permission->del == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        try {
            DB::beginTransaction();
            // ── Employee Sync before delete ───────────────────────────────────────
            $housekeeper = HousekeeperMast::where('propertyid', $this->propertyid)
                ->where('scode', $ucode)
                ->where('sn', $sn)
                ->first();

            if ($housekeeper && $housekeeper->empid) {
                DB::table('employee')
                    ->where('code', $housekeeper->empid)
                    ->where('propertyid', $this->propertyid)
                    ->update([
                        'active_yn'  => 'D',
                        'u_name'     => Auth::user()->u_name,
                        'u_updatedt' => $this->currenttime,
                        'u_ae'       => 'd',
                    ]);
            }
            // ─────────────────────────────────────────────────────────────────────

            $deleted = HousekeeperMast::where('propertyid', $this->propertyid)
                ->where('scode', $ucode)
                ->where('sn', $sn)
                ->delete();
            if ($deleted) {
                DB::commit();
                return back()->with('success', 'House Keeping Master Deleted successfully!');
            } else {
                DB::rollBack();
                return back()->with('error', 'Unable to Delete House Keeping Master!');
            }
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function savehousecleaning(Request $request)
    {
        $permission = revokeopen(151111);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'You have no permission to execute this functionality!',
            ], 403);
        }
        if (empty($request->roomno)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid Roomno',
            ]);
        }

        try {

            DB::beginTransaction();
            if (in_array($request->roomstat, ['C', 'D'])) {
                $roomclean = new RoomClean;
                $roomclean->propertyid = $this->propertyid;
                $roomclean->hosuekeeper = $request->housekeeper ?? '';
                $roomclean->roomno = $request->roomno;
                $roomclean->remarks = $request->remarks ?? '';
                $roomclean->type = $request->roomstat;
                $roomclean->u_entdt = $this->currenttime;
                $roomclean->u_updatedt = null;
                $roomclean->u_ae = 'a';
                $roomclean->save();

                $roommast = RoomMast::where('propertyid', $this->propertyid)->where('inclcount', 'Y')->where('type', 'RO')->where('rcode', $request->roomno)
                    ->first();
                $roommast->room_stat = $request->roomstat;
                $roommast->u_updatedt = $this->currenttime;
                $roommast->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Room Updated successfully Type: ' . $request->roomstat,
                ]);
            } elseif (in_array($request->roomstat, ['O'])) {
                $rblkout = new RoomBlockout;
                $rblkout->propertyid = $this->propertyid;
                $rblkout->roomcode = $request->roomno;
                $rblkout->block = $request->block;
                $rblkout->reasons = $request->reasons;
                $rblkout->fromdate = $request->fromdate;
                $rblkout->todate = $request->todate;
                $rblkout->type = $request->roomstat;
                $rblkout->u_name = Auth::user()?->u_name;
                $rblkout->u_entdt = $this->currenttime;
                $rblkout->u_updatedt = null;
                $rblkout->u_ae = 'a';
                $rblkout->vtime = date('H:i:s');
                $rblkout->guestname = $request->guestname ?? '';
                $rblkout->mobileno = $request->mobileno ?? '';
                $rblkout->save();

                $roommast = RoomMast::where('propertyid', $this->propertyid)->where('inclcount', 'Y')->where('type', 'RO')->where('rcode', $request->roomno)
                    ->first();
                $roommast->room_stat = $request->roomstat;
                $roommast->u_updatedt = $this->currenttime;
                $roommast->save();

                // Audit history: mark Out of Order status change (mirrors C/D audit rows)
                $roomclean = new RoomClean;
                $roomclean->propertyid = $this->propertyid;
                $roomclean->hosuekeeper = $request->housekeeper ?? '';
                $roomclean->roomno = $request->roomno;
                $roomclean->remarks = mb_substr('OOO: ' . ($request->reasons ?? '') . ($request->block ? ' [' . $request->block . ']' : ''), 0, 50);
                $roomclean->type = 'O';
                $roomclean->u_entdt = $this->currenttime;
                $roomclean->u_updatedt = null;
                $roomclean->u_ae = 'a';
                $roomclean->save();

                \App\Helpers\MasterDataCache::flushAvailability($this->propertyid);
                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Room Updated successfully Type: ' . $request->roomstat,
                ]);
            } elseif (in_array($request->roomstat, ['R'])) {
                $rblkout = RoomBlockout::where('propertyid', $this->propertyid)->where('roomcode', $request->roomno)->where('type', 'O')
                    ->whereNull('cleardate')->first();
                if (!$rblkout) {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'No active Out of Order block found for room ' . $request->roomno,
                    ]);
                }
                $rblkout->u_updatedt = $this->currenttime;
                $rblkout->u_ae = 'e';
                $rblkout->type = $request->roomstat;
                $rblkout->cleardate = $this->ncurdate;
                $rblkout->cleartime = date('H:i:s');
                $rblkout->clearuser = Auth::user()->u_name;
                $rblkout->clearremark = $request->clearremark;
                $rblkout->save();
                \App\Helpers\MasterDataCache::flushAvailability($this->propertyid);

                $roommast = RoomMast::where('propertyid', $this->propertyid)->where('inclcount', 'Y')->where('type', 'RO')->where('rcode', $request->roomno)
                    ->first();
                $roommast->room_stat = 'C';
                $roommast->u_updatedt = $this->currenttime;
                $roommast->save();

                // Audit history: mark release-from-OOO status change
                $roomclean = new RoomClean;
                $roomclean->propertyid = $this->propertyid;
                $roomclean->hosuekeeper = $request->housekeeper ?? '';
                $roomclean->roomno = $request->roomno;
                $roomclean->remarks = mb_substr('Released from OOO: ' . ($request->clearremark ?? ''), 0, 50);
                $roomclean->type = 'R';
                $roomclean->u_entdt = $this->currenttime;
                $roomclean->u_updatedt = null;
                $roomclean->u_ae = 'a';
                $roomclean->save();

                DB::commit();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Room Updated successfully Type: ' . $request->roomstat,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid Room Stat: ' . $request->roomstat,
                ]);
            }
        } catch (Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Unknown Error Occured: ' . $e->getMessage() . ' On Line: ' . $e->getLine(),
            ], 500);
        }
    }

    public function updatelogform()
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }
        $mainMenus = DB::table('tbl_usermodule')
            ->where('flag', 'N')
            ->where('opt2', 0)
            ->select('module', 'module_name', 'opt1', 'opt2', 'opt3')
            ->distinct() // Ensure unique entries
            ->get();

        $data = UpdateLog::orderBy('u_entdt', 'DESC')->get();

        return view('admin.updatelogform', compact('data', 'mainMenus'));
    }    public function submitupdatelogform(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }

        try {


            $uplog = new UpdateLog;
            $uplog->mainmenu = $request->mainmenu;
            $uplog->submenu = $request->submenu;
            $uplog->pagename = $request->pagename;
            $uplog->summary = $request->summary;
            $uplog->u_entdt = $this->currenttime;
            $uplog->save();

            return back()->with('success', 'Update Log Inserted successfully!');
        } catch (Exception $e) {
            return back()->with('error', 'Unable to Insert Update Log: ' . $e->getMessage(), 500);
        }
    }

    public function submenufetch(Request $request)
    {
        $opt1 = $request->opt1;
        $opt3 = $request->opt3;

        $submenus = DB::table('tbl_usermodule')
            ->where('opt1', $opt1)
            ->whereNot('opt2', '0')
            ->where('opt3', $opt3)
            ->get();

        return response()->json($submenus);
    }

    public function pagenamefetch(Request $request)
    {
        $opt1 = $request->opt1;
        $opt2 = $request->opt2;

        $pages = DB::table('tbl_usermodule')
            ->where('opt1', $opt1)
            ->where('opt2', $opt2)
            ->whereNot('opt3', '0')
            ->get();

        return response()->json($pages);
    }

    public function deleteupdatelog(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $sn = base64_decode($request->sn);
        if (! $sn) {
            return back()->with('error', 'SN is missing or undefined!');
        }

        Log::info('Trying to delete record with sn: ' . $sn);
        $record = UpdateLog::where('sn', $sn);

        if ($record) {
            $record->delete();

            return back()->with('success', "Record with sn: $sn deleted successfully!");
        }

        return back()->with('error', 'Record not found.');
    }

    // ─── Supervisor Master ────────────────────────────────────────────────────────

    public function hksupervisor()
    {
        $data = HkSupervisor::where('propertyid', $this->propertyid)
            ->orderBy('sn', 'asc')
            ->get();

        return view('property.housekeeping.hksupervisor', compact('data'));
    }

    public function submithksupervisor(Request $request)
    {
        $permission = revokeopen(121514);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        if (HkSupervisor::where('propertyid', $this->propertyid)
            ->where('name', strtoupper($request->name))
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Supervisor name already exists!']);
        }

        $last      = HkSupervisor::where('propertyid', $this->propertyid)->orderBy('sn', 'desc')->value('code');
        $increment = $last ? (intval(substr($last, strlen((string) $this->propertyid))) + 1) : 1;
        $code      = $this->propertyid . $increment;

        try {
            HkSupervisor::create([
                'propertyid' => $this->propertyid,
                'code'       => $code,
                'name'       => strtoupper($request->name),
                'empcode'    => '',
                'activeyn'   => $request->activeyn ?? 1,
                'u_name'     => Auth::user()->u_name,
                'u_entdt'    => $this->currenttime,
                'u_ae'       => 'a',
            ]);

            return response()->json(['success' => true, 'message' => 'Supervisor saved successfully!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to save: ' . $e->getMessage()]);
        }
    }

    public function updatehksupervisor(Request $request)
    {
        $permission = revokeopen(121514);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        if (HkSupervisor::where('propertyid', $this->propertyid)
            ->where('name', strtoupper($request->name))
            ->where('sn', '!=', $request->sn)
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Supervisor name already exists!']);
        }

        try {
            DB::beginTransaction();
            HkSupervisor::where('sn', $request->sn)
                ->where('propertyid', $this->propertyid)
                ->update([
                    'name'     => strtoupper($request->name),
                    'activeyn' => $request->activeyn,
                    'u_name'   => Auth::user()->u_name,
                    'u_ae'     => 'e',
                ]);

            // ── Employee Sync ─────────────────────────────────────────────────────
            $empcode = HkSupervisor::where('sn', $request->sn)
                ->where('propertyid', $this->propertyid)
                ->value('empcode');

            if ($empcode) {
                DB::table('employee')
                    ->where('code', $empcode)
                    ->where('propertyid', $this->propertyid)
                    ->update([
                        'name'      => strtoupper($request->name),
                        'activeyn'  => $request->activeyn == 1 ? 'Y' : 'N',
                        'u_name'    => Auth::user()->u_name,
                        'u_updatedt' => $this->currenttime,
                        'u_ae'      => 'e',
                    ]);
            }
            // ─────────────────────────────────────────────────────────────────────

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Supervisor updated successfully!']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Unable to update: ' . $e->getMessage()]);
        }
    }

    public function deletehksupervisor(Request $request)
    {
        $permission = revokeopen(121514);
        if (is_null($permission) || $permission->del == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            DB::beginTransaction();
            $supervisor = HkSupervisor::where('sn', $request->sn)
                ->where('propertyid', $this->propertyid)
                ->first();

            if ($supervisor && $supervisor->empcode) {
                // ── Employee Sync ─────────────────────────────────────────────────
                DB::table('employee')
                    ->where('code', $supervisor->empcode)
                    ->where('propertyid', $this->propertyid)
                    ->update([
                        'active_yn'  => 'D',
                        'u_name'     => Auth::user()->u_name,
                        'u_updatedt' => $this->currenttime,
                        'u_ae'       => 'd',
                    ]);
                // ─────────────────────────────────────────────────────────────────
            }

            HkSupervisor::where('sn', $request->sn)
                ->where('propertyid', $this->propertyid)
                ->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Supervisor deleted successfully!']);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Unable to delete: ' . $e->getMessage()]);
        }
    }

    // ─── Cleaning Type CRUD ───────────────────────────────────────────────────────

    public function cleaningtype()
    {
        $data = DB::table('hkcleaningtype')
            ->where('propertyid', $this->propertyid)
            ->orderBy('sn', 'asc')
            ->get();

        return view('property.housekeeping.cleaningtype', compact('data'));
    }

    public function submitcleaningtype(Request $request)
    {
        $permission = revokeopen(121513);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        if (DB::table('hkcleaningtype')
            ->where('propertyid', $this->propertyid)
            ->where('name', strtoupper($request->name))
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Cleaning Type name already exists!']);
        }

        $last      = DB::table('hkcleaningtype')->where('propertyid', $this->propertyid)->orderBy('sn', 'desc')->value('code');
        $increment = $last ? (intval(substr($last, strlen((string) $this->propertyid))) + 1) : 1;
        $code      = $this->propertyid . $increment;

        try {
            DB::table('hkcleaningtype')->insert([
                'propertyid' => $this->propertyid,
                'code'       => $code,
                'name'       => strtoupper($request->name),
                'esttime'    => $request->esttime ?: null,
                'u_name'     => Auth::user()->u_name,
                'u_entdt'    => $this->currenttime,
                'u_ae'       => 'a',
            ]);

            return response()->json(['success' => true, 'message' => 'Cleaning Type saved successfully!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to save: ' . $e->getMessage()]);
        }
    }

    public function updatecleaningtype(Request $request)
    {
        $permission = revokeopen(121513);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        if (DB::table('hkcleaningtype')
            ->where('propertyid', $this->propertyid)
            ->where('name', strtoupper($request->name))
            ->where('sn', '!=', $request->sn)
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Cleaning Type name already exists!']);
        }

        try {
            DB::table('hkcleaningtype')
                ->where('sn', $request->sn)
                ->where('propertyid', $this->propertyid)
                ->update([
                    'name'    => strtoupper($request->name),
                    'esttime' => $request->esttime ?: null,
                    'u_name'  => Auth::user()->u_name,
                    'u_ae'    => 'e',
                ]);

            return response()->json(['success' => true, 'message' => 'Cleaning Type updated successfully!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to update: ' . $e->getMessage()]);
        }
    }

    public function deletecleaningtype(Request $request)
    {
        $permission = revokeopen(121513);
        if (is_null($permission) || $permission->del == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            DB::table('hkcleaningtype')
                ->where('sn', $request->sn)
                ->where('propertyid', $this->propertyid)
                ->delete();

            return response()->json(['success' => true, 'message' => 'Cleaning Type deleted successfully!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to delete: ' . $e->getMessage()]);
        }
    }

    // ─── Cleaning Type Load (Inconsistency) ──────────────────────────────────────

    public function cleaningtypeloadup()
    {
        $path = storage_path('app/public/hkcleaning.json');

        if (!file_exists($path)) {
            return response()->json(['message' => "File not found: $path"], 500);
        }

        $jsonData = json_decode(file_get_contents($path), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'JSON parsing error: ' . json_last_error_msg()], 500);
        }

        $propertyid  = $this->propertyid;
        $u_name      = Auth::user()->u_name;
        $insertedCount = 0;
        $skipped       = [];

        foreach ($jsonData as $item) {
            $name = strtoupper(trim($item['Name']));

            $exists = DB::table('hkcleaningtype')
                ->where('propertyid', $propertyid)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                $skipped[] = $name;
                continue;
            }

            // Auto-generate code: propertyid + increment
            $last      = DB::table('hkcleaningtype')->where('propertyid', $propertyid)->orderBy('sn', 'desc')->value('code');
            $increment = $last ? (intval(substr($last, strlen((string) $propertyid))) + 1) : 1;
            $code      = $propertyid . $increment;

            DB::table('hkcleaningtype')->insert([
                'propertyid' => $propertyid,
                'code'       => $code,
                'name'       => $name,
                'u_name'     => $u_name,
                'u_entdt'    => $this->currenttime,
                'u_ae'       => 'a',
            ]);

            $insertedCount++;
        }

        if ($insertedCount > 0) {
            return response()->json([
                'message' => $insertedCount . ' Cleaning Type(s) Inserted Successfully! And ' . count($skipped) . ' Skipped.',
            ]);
        }

        return response()->json(['message' => 'All Cleaning Types already exist! ' . count($skipped) . ' Skipped.'], 500);
    }

    // ─── Floor Master ────────────────────────────────────────────────────────────

    public function floormaster()
    {
        $data = HkFloor::where('propertyid', $this->propertyid)
            ->orderBy('id', 'asc')
            ->get();

        return view('property.housekeeping.floormaster', compact('data'));
    }

    public function submitfloormaster(Request $request)
    {
        $permission = revokeopen(121511);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        if (HkFloor::where('propertyid', $this->propertyid)->where('name', strtoupper($request->name))->exists()) {
            return response()->json(['success' => false, 'message' => 'Floor name already exists!']);
        }

        // Auto-generate code: propertyid + increment  e.g. 1031, 1032, 1033
        $last = HkFloor::where('propertyid', $this->propertyid)->orderBy('id', 'desc')->value('code');
        $increment = $last ? (intval(substr($last, strlen((string) $this->propertyid))) + 1) : 1;
        $code = $this->propertyid . $increment;

        try {
            $floor = HkFloor::create([
                'propertyid' => $this->propertyid,
                'code'       => $code,
                'name'       => strtoupper($request->name),
                'superviser' => strtoupper($request->superviser ?? ''),
                'isactive'   => 1,
                'u_name'     => Auth::user()->u_name,
            ]);

            return response()->json(['success' => true, 'message' => 'Floor Master saved successfully!', 'data' => $floor]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to save: ' . $e->getMessage()]);
        }
    }

    public function updatefloormaster(Request $request)
    {
        $permission = revokeopen(121511);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        if (HkFloor::where('propertyid', $this->propertyid)
            ->where('name', strtoupper($request->name))
            ->where('id', '!=', $request->id)
            ->exists()
        ) {
            return response()->json(['success' => false, 'message' => 'Floor name already exists!']);
        }

        try {
            $floor = HkFloor::where('id', $request->id)->where('propertyid', $this->propertyid)->firstOrFail();
            $floor->update([
                'name'       => strtoupper($request->name),
                'superviser' => strtoupper($request->superviser ?? ''),
                'u_name'     => Auth::user()->u_name,
            ]);

            return response()->json(['success' => true, 'message' => 'Floor Master updated successfully!', 'data' => $floor]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to update: ' . $e->getMessage()]);
        }
    }

    public function deletefloormaster(Request $request)
    {
        $permission = revokeopen(121511);
        if (is_null($permission) || $permission->del == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $floor = HkFloor::where('id', $request->id)->where('propertyid', $this->propertyid)->firstOrFail();
            $floor->delete();

            return response()->json(['success' => true, 'message' => 'Floor Master deleted successfully!']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Unable to delete: ' . $e->getMessage()]);
        }
    }

    // ─── Floor Load Up (Inconsistency Import) ────────────────────────────────────

    public function floorloadup()
    {
        $path = storage_path('app/public/hkfloors.json');

        if (!file_exists($path)) {
            return response()->json(['message' => "File not found: $path"], 500);
        }

        // Strip UTF-8 BOM if present, then parse
        $raw      = ltrim(file_get_contents($path), "\xEF\xBB\xBF");
        $jsonData = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'JSON parsing error: ' . json_last_error_msg()], 500);
        }

        $propertyid    = $this->propertyid;
        $u_name        = Auth::user()->u_name;
        $insertedCount = 0;
        $skipped       = [];

        foreach ($jsonData as $item) {
            $name = strtoupper(trim($item['Name']));

            $exists = HkFloor::where('propertyid', $propertyid)
                ->where('name', $name)
                ->exists();

            if ($exists) {
                $skipped[] = $name;
                continue;
            }

            // Auto-generate code: propertyid + increment
            $last      = HkFloor::where('propertyid', $propertyid)->orderBy('id', 'desc')->value('code');
            $increment = $last ? (intval(substr($last, strlen((string) $propertyid))) + 1) : 1;
            $code      = $propertyid . $increment;

            HkFloor::create([
                'propertyid' => $propertyid,
                'code'       => $code,
                'name'       => $name,
                'u_name'     => $u_name,
            ]);

            $insertedCount++;
        }

        if ($insertedCount > 0) {
            return response()->json([
                'message' => $insertedCount . ' Floor(s) Inserted Successfully! And ' . count($skipped) . ' Skipped.',
            ]);
        }

        return response()->json(['message' => 'All Floors already exist! ' . count($skipped) . ' Skipped.'], 500);
    }

    // ─── Checklist Load Up (Inconsistency Import) ────────────────────────────────

    public function checklistloadup()
    {
        $path = storage_path('app/public/hkchecklist.json');

        if (!file_exists($path)) {
            return response()->json(['message' => "File not found: $path"], 500);
        }

        $raw      = ltrim(file_get_contents($path), "\xEF\xBB\xBF");
        $jsonData = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'JSON parsing error: ' . json_last_error_msg()], 500);
        }

        $propertyid    = $this->propertyid;
        $u_name        = Auth::user()->u_name;
        $insertedCount = 0;
        $skipped       = [];

        // sno = simple row count increment (1, 2, 3 ...)
        $lastSno = \App\Models\HkChecklistMast::where('propertyid', $propertyid)->max('sno') ?? 0;

        // code = propertyid + increment (e.g. 1031, 1032 ...)
        $lastCodeRaw = \App\Models\HkChecklistMast::where('propertyid', $propertyid)->max('code') ?? 0;
        $prefixLen   = strlen((string) $propertyid);
        $lastCode    = $lastCodeRaw ? intval(substr((string) $lastCodeRaw, $prefixLen)) : 0;

        foreach ($jsonData as $item) {
            $name = strtoupper(trim($item['Name']));

            if (\App\Models\HkChecklistMast::where('propertyid', $propertyid)->where('name', $name)->exists()) {
                $skipped[] = $name;
                continue;
            }

            $lastSno++;
            $lastCode++;
            $code = intval($propertyid . $lastCode); // e.g. 1031, 1032 ...

            \App\Models\HkChecklistMast::create([
                'propertyid' => $propertyid,
                'code'       => $code,
                'sno'        => $lastSno,
                'name'       => $name,
                'u_name'     => $u_name,
                'u_entdt'    => $this->currenttime,
                'u_ae'       => 'a',
            ]);

            $insertedCount++;
        }

        if ($insertedCount > 0) {
            return response()->json([
                'message' => $insertedCount . ' Checklist Item(s) Inserted Successfully! And ' . count($skipped) . ' Skipped.',
            ]);
        }

        return response()->json(['message' => 'All Checklist Items already exist! ' . count($skipped) . ' Skipped.'], 500);
    }

    // ─────────────────────────────────────────────────────────────────────────────


    // ─── Feedback Master Load Up (Inconsistency Import) ──────────────────────────

    public function feedbackloadup()
    {
        $path = storage_path('app/public/hkfeedback.json');

        if (!file_exists($path)) {
            return response()->json(['message' => "File not found: $path"], 500);
        }

        $raw      = ltrim(file_get_contents($path), "\xEF\xBB\xBF");
        $jsonData = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'JSON parsing error: ' . json_last_error_msg()], 500);
        }

        $propertyid    = $this->propertyid;
        $insertedCount = 0;
        $skipped       = [];

        // questioncode = propertyid + increment (e.g. 1031, 1032 ...)
        $lastCodeRaw = \App\Models\FeedbackMaster::where('propertyid', $propertyid)->max('questioncode') ?? 0;
        $prefixLen   = strlen((string) $propertyid);
        $lastCode    = $lastCodeRaw ? intval(substr((string) $lastCodeRaw, $prefixLen)) : 0;

        $lastOrder = \App\Models\FeedbackMaster::where('propertyid', $propertyid)->max('displayorder') ?? 0;

        foreach ($jsonData as $item) {
            $question = trim($item['Question']);
            $order    = intval($item['DisplayOrder'] ?? 0);

            if (\App\Models\FeedbackMaster::where('propertyid', $propertyid)
                ->where('question', $question)
                ->exists()
            ) {
                $skipped[] = $question;
                continue;
            }

            $lastCode++;
            $lastOrder++;
            $questionCode = (string) ($propertyid . $lastCode);

            \App\Models\FeedbackMaster::create([
                'propertyid'   => $propertyid,
                'questioncode' => $questionCode,
                'question'     => $question,
                'displayorder' => $order ?: $lastOrder,
                'isactive'     => 1,
            ]);

            $insertedCount++;
        }

        if ($insertedCount > 0) {
            return response()->json([
                'message' => $insertedCount . ' Feedback Question(s) Inserted Successfully! And ' . count($skipped) . ' Skipped.',
            ]);
        }

        return response()->json(['message' => 'All Feedback Questions already exist! ' . count($skipped) . ' Skipped.'], 500);
    }
    // ─── Lost & Found ────────────────────────────────────────────────────────────

    public function lostfoundform(Request $request)
    {
        $propertyId = $this->propertyid;
        date_default_timezone_set('Asia/Kolkata');

        // Tag No generated from vno — no separate tagno column needed
        $lastVno = DB::table('lostfound')->where('propertyid', $propertyId)->max('vno') ?? 0;
        $nextVno = $lastVno + 1;
        $tagNo   = 'LF-' . str_pad($nextVno, 2, '0', STR_PAD_LEFT);

        // Employees (housekeepers) for Found By dropdown
        $employees = DB::table('housekeeparmast')
            ->where('propertyid', $propertyId)
            ->where('activeYN', 'Y')
            ->orderBy('name')
            ->get(['sn', 'name']);

        // Paginated items for the table below the form (full list merged)
        $items = DB::table('lostfound')
            ->where('propertyid', $propertyId)
            ->orderByDesc('sn')
            ->paginate(20);

        // Pre-fill roomno if coming from startcleaning page via ?roomno= param
        $preRoomno = $request->query('roomno', '');

        return view('property.housekeeping.lostfound', [
            'tagNo'       => $tagNo,
            'asOnDate'    => $this->ncurdate,
            'currentTime' => date('h:i A'),
            'employees'   => $employees,
            'items'       => $items,
            'preRoomno'   => $preRoomno,
        ]);
    }

    public function storelostfound(Request $request)
    {
        $permission = revokeopen(151117);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            date_default_timezone_set('Asia/Kolkata');

            // Parse found date DD-MM-YYYY → Y-m-d
            $foundDate = $this->ncurdate;
            if ($request->filled('founddate')) {
                try {
                    $foundDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->founddate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $foundDate = $this->ncurdate;
                }
            }

            // vno: next sequential per property
            $lastVno = DB::table('lostfound')->where('propertyid', $propertyId)->max('vno') ?? 0;
            $vno     = $lastVno + 1;

            // itemcondition: production enum only allows Excellent,Good,Fair,Damaged
            $validConditions = ['Excellent', 'Good', 'Fair', 'Damaged'];
            $condition       = $request->input('itemcondition');
            $itemcondition   = in_array($condition, $validConditions) ? $condition : null;

            // foundlocation: varchar(15) — truncate to 15 chars
            $foundlocation = mb_substr($request->input('foundlocation', ''), 0, 15) ?: null;

            // storagelocation: varchar(15)
            $storagelocation = mb_substr($request->input('storagelocation', ''), 0, 15) ?: null;

            // itemcategory: varchar(20)
            $itemcategory = mb_substr($request->input('itemcategory', ''), 0, 20) ?: null;

            DB::table('lostfound')->insert([
                'propertyid'      => $propertyId,
                'vno'             => $vno,
                'vdate'           => $this->ncurdate,
                'founddate'       => $foundDate,
                'foundtime'       => $request->input('foundtime')      ?: null,
                'roomno'          => mb_substr($request->input('roomno', ''), 0, 20) ?: null,
                'itemcategory'    => $itemcategory,
                'itemname'        => mb_substr($request->input('itemname', ''), 0, 25) ?: null,
                'brandname'       => mb_substr($request->input('brandname', ''), 0, 25) ?: null,
                'color'           => mb_substr($request->input('color', ''), 0, 15) ?: null,
                'quantity'        => $request->input('quantity', 1) ?: 1,
                'perishable'      => mb_substr($request->input('Perishable', 'No'), 0, 3),
                'itemcondition'   => $itemcondition,
                'foundlocation'   => $foundlocation,
                'description'     => mb_substr($request->input('description', ''), 0, 30) ?: null,
                'foundby'         => $request->input('foundby')        ?: null,
                'storagelocation' => $storagelocation,
                'estimatedvalue'  => $request->input('estimatedvalue') ?: null,
                'status'          => 'Found',
                'remarks'         => mb_substr($request->input('remarks', ''), 0, 30) ?: null,
                'u_name'          => Auth::id(),
                'u_entdt'         => $this->currenttime,
                'u_ae'            => 'a',
                'claimby'         => '',
                'claimmoblieno'   => '',
                'claimemail'      => '',
                'handoverto'      => '',
                'handoverdate'    => '',
                'receivedby'      => '',
                'claimremark'     => '',
                'couiername'      => '',
                'couierdocno'     => '',
            ]);

            // Tag no for response (display only — no DB column)
            $tagNo    = 'LF-' . str_pad($vno, 2, '0', STR_PAD_LEFT);
            $newTagNo = 'LF-' . str_pad($vno + 1, 2, '0', STR_PAD_LEFT);

            return response()->json([
                'success'   => true,
                'message'   => 'Lost & Found entry saved successfully!',
                'tagno'     => $tagNo,
                'new_tagno' => $newTagNo,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to save: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function lostfoundlist(Request $request)
    {
        // Merged into lostfound page — redirect
        return redirect()->route('lostfoundform');
    }

    public function editlostfound($id)
    {
        $propertyId = $this->propertyid;
        $item = DB::table('lostfound')
            ->where('propertyid', $propertyId)
            ->where('sn', $id)
            ->first();

        if (!$item) {
            return response()->json(['success' => false, 'message' => 'Record not found.']);
        }

        return response()->json([
            'success' => true,
            'data'    => $item,
        ]);
    }

    public function updatelostfound(Request $request)
    {
        $permission = revokeopen(151117);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            $id = $request->input('id');

            $item = DB::table('lostfound')
                ->where('propertyid', $propertyId)
                ->where('sn', $id)
                ->first();

            if (!$item) {
                return response()->json(['success' => false, 'message' => 'Record not found.']);
            }

            // status: production enum values.
            // If the user explicitly changed the status, honor it;
            // otherwise keep the old auto behavior (Found + handoverto → HandedOver).
            $validStatuses = ['Found', 'Stored', 'Claimed', 'HandedOver', 'Courier', 'Disposed'];
            $status        = $request->input('status');
            if (in_array($status, $validStatuses) && $status !== $item->status) {
                $newStatus = $status;
            } elseif ($item->status === 'Found' && $request->input('handoverto')) {
                $newStatus = 'HandedOver';
            } else {
                $newStatus = $item->status;
            }

            DB::table('lostfound')
                ->where('propertyid', $propertyId)
                ->where('sn', $id)
                ->update([
                    // Status (dropdown in edit modal)
                    'status'        => $newStatus,
                    // Claim info
                    'claimby'       => mb_substr($request->input('guestname', ''), 0, 50) ?: '',
                    'claimmoblieno' => mb_substr($request->input('mobileno', ''), 0, 15) ?: '',
                    'claimemail'    => mb_substr($request->input('email', ''), 0, 60) ?: '',
                    'claimremark'   => mb_substr($request->input('claim_remarks', ''), 0, 100) ?: '',
                    'handoverto'    => mb_substr($request->input('handoverto', ''), 0, 20) ?: null,
                    'handoverdate'  => $this->parseHandoverDate($request->input('handoverdt')),
                    'receivedby'    => mb_substr($request->input('receivedby', ''), 0, 50) ?: null,
                    'u_name'        => Auth::id(),
                    'u_ae'          => 'e',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Lost & Found record updated successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Parse handover date from blade (DD-MM-YYYY or DD-MM-YYYY HH:MM)
     * into MySQL datetime format (YYYY-MM-DD HH:MM:SS)
     */
    private function parseHandoverDate(?string $raw): ?string
    {
        if (empty($raw)) return null;
        $raw = trim($raw);
        // Try DD-MM-YYYY HH:MM AM/PM  e.g. "05-08-2026 10:30 AM"
        try {
            return \Carbon\Carbon::createFromFormat('d-m-Y h:i A', $raw)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {}
        // Try DD-MM-YYYY HH:MM  e.g. "05-08-2026 10:30"
        try {
            return \Carbon\Carbon::createFromFormat('d-m-Y H:i', $raw)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {}
        // Try DD-MM-YYYY only  e.g. "05-08-2026"
        try {
            return \Carbon\Carbon::createFromFormat('d-m-Y', $raw)->format('Y-m-d') . ' 00:00:00';
        } catch (\Exception $e) {}
        // Try YYYY-MM-DD (already correct format)
        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d', substr($raw, 0, 10))->format('Y-m-d') . ' 00:00:00';
        } catch (\Exception $e) {}
        return null;
    }

    public function printlostfound($id)
    {
        $propertyId = $this->propertyid;
        $company = \App\Models\Companyreg::where('propertyid', $propertyId)->first();

        $item = DB::table('lostfound')
            ->where('propertyid', $propertyId)
            ->where('sn', $id)
            ->first();

        if (!$item) {
            return redirect()->back()->with('error', 'Record not found.');
        }

        $foundByName = '';
        if ($item->foundby) {
            $foundByEmp = DB::table('housekeeparmast')
                ->where('propertyid', $propertyId)
                ->where('sn', $item->foundby)
                ->value('name');
            // Text-mode Found By (non-room locations) ho toh raw text dikhao
            $foundByName = $foundByEmp ?: $item->foundby;
        }

        $tagNo = 'LF-' . str_pad($item->vno, 2, '0', STR_PAD_LEFT);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('property.print.lostfoundprint', [
            'item'        => $item,
            'company'     => $company,
            'tagNo'       => $tagNo,
            'foundByName' => $foundByName,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('lost-found-' . $tagNo . '.pdf');
    }

    // ─── Lost & Found Register (menu link fix) ────────────────────────────────
    // Menu items point to /lostfoundregister which had no route → 404.
    // The register (form + full list) lives on the lostfound page, so we reuse it.
    public function lostfoundregister(Request $request)
    {
        return $this->lostfoundform($request);
    }

    // ─── Laundry Send ─────────────────────────────────────────────────────────
    public function laundrysend(Request $request)
    {
        $propertyId = $this->propertyid;
        date_default_timezone_set('Asia/Kolkata');

        // Voucher no generated from vno — LS-01, LS-02 ...
        $lastVno   = DB::table('laundrysend')->where('propertyid', $propertyId)->max('vno') ?? 0;
        $nextVno   = $lastVno + 1;
        $voucherNo = 'LS-' . str_pad($nextVno, 2, '0', STR_PAD_LEFT);

        // Rooms for dropdown
        $rooms = DB::table('room_mast')
            ->where('propertyid', $propertyId)
            ->where('type', 'RO')
            ->orderBy('rcode')
            ->pluck('rcode');

        // Paginated entries for the table below the form
        $items = DB::table('laundrysend')
            ->where('propertyid', $propertyId)
            ->orderByDesc('sn')
            ->paginate(20);

        return view('property.housekeeping.laundrysend', [
            'voucherNo'  => $voucherNo,
            'asOnDate'   => $this->ncurdate,
            'currentTime'=> date('h:i A'),
            'rooms'      => $rooms,
            'items'      => $items,
            'preRoomno'  => $request->query('roomno', ''),
        ]);
    }

    public function storelaundrysend(Request $request)
    {
        $permission = revokeopen(151414);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            date_default_timezone_set('Asia/Kolkata');

            // Parse send date DD-MM-YYYY → Y-m-d
            $sendDate = $this->ncurdate;
            if ($request->filled('senddate')) {
                try {
                    $sendDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->senddate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $sendDate = $this->ncurdate;
                }
            }

            // vno: next sequential per property
            $lastVno = DB::table('laundrysend')->where('propertyid', $propertyId)->max('vno') ?? 0;
            $vno     = $lastVno + 1;

            $qty    = (float) ($request->input('quantity', 1) ?: 1);
            $rate   = (float) ($request->input('rate', 0) ?: 0);
            $amount = round($qty * $rate, 2);

            DB::table('laundrysend')->insert([
                'propertyid'  => $propertyId,
                'vno'         => $vno,
                'vdate'       => $this->ncurdate,
                'senddate'    => $sendDate,
                'roomno'      => mb_substr($request->input('roomno', ''), 0, 20) ?: null,
                'guestname'   => mb_substr($request->input('guestname', ''), 0, 50) ?: null,
                'itemname'    => mb_substr($request->input('itemname', ''), 0, 50) ?: null,
                'quantity'    => $qty,
                'rate'        => $rate,
                'amount'      => $amount,
                'laundrytype' => mb_substr($request->input('laundrytype', 'Guest'), 0, 10),
                'status'      => 'Sent',
                'remarks'     => mb_substr($request->input('remarks', ''), 0, 100) ?: null,
                'u_name'      => Auth::id(),
                'u_entdt'     => $this->currenttime,
                'u_ae'        => 'a',
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Laundry Send entry saved successfully!',
                'voucherno' => 'LS-' . str_pad($vno, 2, '0', STR_PAD_LEFT),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to save: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Laundry Receive ──────────────────────────────────────────────────────
    public function laundryreceive(Request $request)
    {
        $propertyId = $this->propertyid;
        date_default_timezone_set('Asia/Kolkata');

        $lastVno   = DB::table('laundryreceive')->where('propertyid', $propertyId)->max('vno') ?? 0;
        $nextVno   = $lastVno + 1;
        $voucherNo = 'LR-' . str_pad($nextVno, 2, '0', STR_PAD_LEFT);

        $rooms = DB::table('room_mast')
            ->where('propertyid', $propertyId)
            ->where('type', 'RO')
            ->orderBy('rcode')
            ->pluck('rcode');

        $items = DB::table('laundryreceive')
            ->where('propertyid', $propertyId)
            ->orderByDesc('sn')
            ->paginate(20);

        return view('property.housekeeping.laundryreceive', [
            'voucherNo'  => $voucherNo,
            'asOnDate'   => $this->ncurdate,
            'currentTime'=> date('h:i A'),
            'rooms'      => $rooms,
            'items'      => $items,
            'preRoomno'  => $request->query('roomno', ''),
        ]);
    }

    public function storelaundryreceive(Request $request)
    {
        $permission = revokeopen(151415);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            date_default_timezone_set('Asia/Kolkata');

            $receiveDate = $this->ncurdate;
            if ($request->filled('receivedate')) {
                try {
                    $receiveDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->receivedate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $receiveDate = $this->ncurdate;
                }
            }

            $lastVno = DB::table('laundryreceive')->where('propertyid', $propertyId)->max('vno') ?? 0;
            $vno     = $lastVno + 1;

            DB::table('laundryreceive')->insert([
                'propertyid'  => $propertyId,
                'vno'         => $vno,
                'vdate'       => $this->ncurdate,
                'receivedate' => $receiveDate,
                'roomno'      => mb_substr($request->input('roomno', ''), 0, 20) ?: null,
                'itemname'    => mb_substr($request->input('itemname', ''), 0, 50) ?: null,
                'quantity'    => (float) ($request->input('quantity', 0) ?: 0),
                'damagedqty'  => (float) ($request->input('damagedqty', 0) ?: 0),
                'missingqty'  => (float) ($request->input('missingqty', 0) ?: 0),
                'receivedby'  => mb_substr($request->input('receivedby', ''), 0, 50) ?: null,
                'remarks'     => mb_substr($request->input('remarks', ''), 0, 100) ?: null,
                'u_name'      => Auth::id(),
                'u_entdt'     => $this->currenttime,
                'u_ae'        => 'a',
            ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Laundry Receive entry saved successfully!',
                'voucherno' => 'LR-' . str_pad($vno, 2, '0', STR_PAD_LEFT),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to save: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Laundry Send: Edit / Update ───────────────────────────────────────────
    public function laundrysendedit($id)
    {
        $propertyId = $this->propertyid;
        $item = DB::table('laundrysend')
            ->where('propertyid', $propertyId)
            ->where('sn', $id)
            ->first();

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Record not found.']);
        }

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function updatelaundrysend(Request $request)
    {
        $permission = revokeopen(151414);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            date_default_timezone_set('Asia/Kolkata');

            $id = $request->input('id');
            if (! $id) {
                return response()->json(['success' => false, 'message' => 'Record id is missing.'], 422);
            }

            $sendDate = $this->ncurdate;
            if ($request->filled('senddate')) {
                try {
                    $sendDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->senddate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $sendDate = $this->ncurdate;
                }
            }

            $qty    = (float) ($request->input('quantity', 1) ?: 1);
            $rate   = (float) ($request->input('rate', 0) ?: 0);
            $amount = round($qty * $rate, 2);

            DB::table('laundrysend')
                ->where('propertyid', $propertyId)
                ->where('sn', $id)
                ->update([
                    'senddate'    => $sendDate,
                    'roomno'      => mb_substr($request->input('roomno', ''), 0, 20) ?: null,
                    'guestname'   => mb_substr($request->input('guestname', ''), 0, 50) ?: null,
                    'itemname'    => mb_substr($request->input('itemname', ''), 0, 50) ?: null,
                    'quantity'    => $qty,
                    'rate'        => $rate,
                    'amount'      => $amount,
                    'laundrytype' => mb_substr($request->input('laundrytype', 'Guest'), 0, 10),
                    'remarks'     => mb_substr($request->input('remarks', ''), 0, 100) ?: null,
                    'u_entdt'     => $this->currenttime,
                    'u_ae'        => 'u',
                ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Laundry Send entry updated successfully!',
                'voucherno' => 'LS-' . str_pad((int) DB::table('laundrysend')->where('propertyid', $propertyId)->where('sn', $id)->value('vno'), 2, '0', STR_PAD_LEFT),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ─── Laundry Receive: Edit / Update ───────────────────────────────────────
    public function laundryreceiveedit($id)
    {
        $propertyId = $this->propertyid;
        $item = DB::table('laundryreceive')
            ->where('propertyid', $propertyId)
            ->where('sn', $id)
            ->first();

        if (! $item) {
            return response()->json(['success' => false, 'message' => 'Record not found.']);
        }

        return response()->json(['success' => true, 'data' => $item]);
    }

    public function updatelaundryreceive(Request $request)
    {
        $permission = revokeopen(151415);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            date_default_timezone_set('Asia/Kolkata');

            $id = $request->input('id');
            if (! $id) {
                return response()->json(['success' => false, 'message' => 'Record id is missing.'], 422);
            }

            $receiveDate = $this->ncurdate;
            if ($request->filled('receivedate')) {
                try {
                    $receiveDate = \Carbon\Carbon::createFromFormat('d-m-Y', $request->receivedate)->format('Y-m-d');
                } catch (\Exception $e) {
                    $receiveDate = $this->ncurdate;
                }
            }

            DB::table('laundryreceive')
                ->where('propertyid', $propertyId)
                ->where('sn', $id)
                ->update([
                    'receivedate' => $receiveDate,
                    'roomno'      => mb_substr($request->input('roomno', ''), 0, 20) ?: null,
                    'itemname'    => mb_substr($request->input('itemname', ''), 0, 50) ?: null,
                    'quantity'    => (float) ($request->input('quantity', 0) ?: 0),
                    'damagedqty'  => (float) ($request->input('damagedqty', 0) ?: 0),
                    'missingqty'  => (float) ($request->input('missingqty', 0) ?: 0),
                    'receivedby'  => mb_substr($request->input('receivedby', ''), 0, 50) ?: null,
                    'remarks'     => mb_substr($request->input('remarks', ''), 0, 100) ?: null,
                    'u_entdt'     => $this->currenttime,
                    'u_ae'        => 'u',
                ]);

            return response()->json([
                'success'   => true,
                'message'   => 'Laundry Receive entry updated successfully!',
                'voucherno' => 'LR-' . str_pad((int) DB::table('laundryreceive')->where('propertyid', $propertyId)->where('sn', $id)->value('vno'), 2, '0', STR_PAD_LEFT),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unable to update: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function housekeepingreport()
    {
        $company = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)->where('state_code', $company->state_code)->value('name');

        return view('property.housekeepingreport', [
            'ncurdate' => $this->ncurdate,
            'company' => $company,
            'statename' => $statename,

        ]);
    }

    // ─── Room Wise Amenities Report ───────────────────────────────────────────────

    public function roomwiseamenitiesreport()
    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        return view('property.roomwiseamenitiesreport', [
            'ncurdate'  => $this->ncurdate,
            'company'   => $company,
            'statename' => $statename,
        ]);
    }

    public function roomwiseamenitiesreportfetch(Request $request)
    {
        $fromdate = $request->input('fromdate');
        $todate   = $request->input('todate');

        $data = DB::table('hkcleaningftr as HF')
            ->select(
                'HD.roommo',
                'I.Name as Item',
                DB::raw('SUM(HF.qty) as Qty')
            )
            ->join('itemmast as I', 'HF.item', '=', 'I.Code')
            ->leftJoin('hkcleaninghdr as HD', 'HF.assid', '=', 'HD.assno')
            ->where('HF.propertyid', $this->propertyid)
            ->where('I.Property_ID', $this->propertyid)
            ->where('HF.Ctype', 'Amenities')
            ->whereBetween('HD.cleaningdate', [$fromdate, $todate])
            ->groupBy('HF.item', 'HD.roommo', 'I.Name')
            ->orderBy('HD.roommo')
            ->get();

        return response()->json($data);
    }

    // ─── Amenities Report (Item Wise Usage, Cost, Rooms, Pax) ────────────────────

    public function amenitiesreport()
    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        return view('property.amenitiesreport', [
            'ncurdate'  => $this->ncurdate,
            'company'   => $company,
            'statename' => $statename,
        ]);
    }

    public function amenitiesreportfetch(Request $request)
    {
        $fromdate = $request->input('fromdate');
        $todate   = $request->input('todate');

        // Total Rooms in property
        $totalRooms = DB::table('room_mast')
            ->where('propertyid', $this->propertyid)
            ->where('type', 'RO')
            ->where('inclcount', 'Y')
            ->count();

        // Total Pax (inhouse guests in date range)
        $totalPax = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->whereBetween('chkindate', [$fromdate, $todate])
            ->sum(DB::raw('adult + children'));

        // Item wise amenities data
        $data = DB::table('hkamentiesmaster as AM')
            ->select(
                'AM.item',
                'I.Name as Item',
                DB::raw('SUM(S.issqty) as QtyUsed'),
                DB::raw('SUM(S.amount) as Cost'),
                DB::raw('COUNT(S.roomno) as TotalRooms')
            )
            ->join('itemmast as I', 'AM.item', '=', 'I.Code')
            ->leftJoin('stock as S', function ($join) use ($fromdate, $todate) {
                $join->on('AM.item', '=', 'S.item')
                     ->where('S.propertyid', '=', $this->propertyid)
                     ->where('S.vtype', '=', 'HKISS')
                     ->whereBetween('S.vdate', [$fromdate, $todate]);
            })
            ->where('AM.propertyid', $this->propertyid)
            ->where('I.Property_ID', $this->propertyid)
            ->where('AM.type', 'Amenities')
            ->groupBy('AM.item', 'I.Name')
            ->orderBy('I.Name')
            ->get();

        return response()->json([
            'data'       => $data,
            'totalRooms' => $totalRooms,
            'totalPax'   => $totalPax,
        ]);
    }

    // ─── Cleaning Register Report ─────────────────────────────────────────────────

    public function cleaningregister(Request $request)    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        return view('property.cleaningregister', [
            'company'   => $company,
            'statename' => $statename,
            'fromdate'  => $this->ncurdate,
        ]);
    }

    public function fetchcleaningregister(Request $request)
    {
        $fromdate = $request->input('fromdate', $this->ncurdate);
        $todate   = $request->input('todate',   $this->ncurdate);

        $data = DB::table('hkcleaninghdr as HH')
            ->leftJoin('housekeeparmast as M', 'HH.housekeeperid', '=', 'M.scode')
            ->leftJoin('hksupervisor as S',    'HH.supervisorid',  '=', 'S.code')
            ->where('HH.propertyid', $this->propertyid)
            ->whereBetween('HH.cleaningdate', [$fromdate, $todate])
            ->select(
                'HH.cleaningdate',
                'HH.roommo',
                'HH.assno',
                'HH.cleaningno',
                'HH.starttime',
                'HH.endtime',
                DB::raw('HH.totalminutes as duration'),
                DB::raw('M.name as cleandby'),
                DB::raw('S.name as supervisor'),
                DB::raw('HH.cleaningstatus as cleanstatus'),
                'HH.inspectionstatus'
            )
            ->orderBy('HH.cleaningdate', 'ASC')
            ->orderBy('HH.roommo', 'ASC')
            ->get();

        return response()->json(['data' => $data]);
    }

    // ─── HK Stock Report ──────────────────────────────────────────────────────────

    public function hkstockreport(Request $request)
    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        $godowns = collect(); // Godown dropdown removed

        $itemgroups = DB::table('itemmast')
            ->where('Property_ID', $this->propertyid)
            ->where('ItemType', 'Store')
            ->where('ActiveYN', 'Y')
            ->select('Code as code', 'Name as name')
            ->orderBy('Name')
            ->get();

        return view('property.housekeeping.hkstockreport', [
            'company'    => $company,
            'statename'  => $statename,
            'godowns'    => $godowns,
            'itemgroups' => $itemgroups,
            'asofdate'   => $this->ncurdate,
        ]);
    }

    public function fetchhkstockreport(Request $request)
    {
        try {
            $fromdate      = $request->input('fromdate',   $this->ncurdate);
            $todate        = $request->input('todate',     $this->ncurdate);
            $propertyId    = $this->propertyid;
            $godownCode    = 'HK' . $propertyId;
            $itemgroupArr  = $request->input('itemgroups', []);
            $filterByGroup = is_array($itemgroupArr) && count($itemgroupArr) > 0;

            $recNcat = ['PBC','PBR','MRE','RQI','STOP','BKREC','KSREC','KMREC'];
            $issNcat = ['PRR','PRC','RQR','BKISS','KSISS','KMISS','HKISS'];
            $allNcat = array_merge($recNcat, $issNcat);

            // ── Step 1: Item list — NO voucher_type join, NO NCAT filter ──────
            $itemsQuery = DB::table('stock as S')
                ->join('itemmast as I', function ($j) {
                    $j->on('S.Item', '=', 'I.Code')
                      ->where('I.ItemType', 'Store');
                })
                ->where('S.propertyid', $propertyId)
                ->where('S.GodownCode', $godownCode)
                ->where('S.delflag', 'N')
                ->when($filterByGroup, fn($q) => $q->whereIn('S.Item', $itemgroupArr))
                ->select('S.Item', 'I.Name as ItemName', 'I.Unit', 'I.IssueUnit')
                ->distinct()
                ->orderBy('I.Name')
                ->get()
                ->keyBy('Item');

            $itemCodes = $itemsQuery->keys()->toArray();

            if (empty($itemCodes)) {
                return response()->json(['success' => true, 'data' => []]);
            }

            // ── Step 2: Opening stock (before fromdate, with NCAT filter) ─────
            $opening = DB::table('stock as S')
                ->leftJoin('voucher_type as VT', function ($j) {
                    $j->on('S.VType', '=', 'VT.V_Type')
                      ->on('S.propertyid', '=', 'VT.propertyid');
                })
                ->where('S.propertyid', $propertyId)
                ->where('S.GodownCode', $godownCode)
                ->where('S.delflag', 'N')
                ->where('S.VDate', '<', $fromdate)
                ->whereIn('S.Item', $itemCodes)
                ->whereIn('VT.NCAT', $allNcat)
                ->select(
                    'S.Item',
                    DB::raw("SUM(CASE WHEN VT.NCAT IN ('PBC','PBR','MRE','RQI','STOP','BKREC','KSREC','KMREC') THEN S.RecdQty ELSE 0 END)
                           - SUM(CASE WHEN VT.NCAT IN ('PRR','PRC','RQR','BKISS','KSISS','KMISS','HKISS') THEN S.IssQty ELSE 0 END)
                           as openingqty")
                )
                ->groupBy('S.Item')
                ->get()
                ->keyBy('Item');

            // ── Step 3: Period totals ─────────────────────────────────────────
            $period = DB::table('stock as S')
                ->leftJoin('voucher_type as VT', function ($j) {
                    $j->on('S.VType', '=', 'VT.V_Type')
                      ->on('S.propertyid', '=', 'VT.propertyid');
                })
                ->where('S.propertyid', $propertyId)
                ->where('S.GodownCode', $godownCode)
                ->where('S.delflag', 'N')
                ->whereBetween('S.VDate', [$fromdate, $todate])
                ->whereIn('S.Item', $itemCodes)
                ->whereIn('VT.NCAT', $allNcat)
                ->select(
                    'S.Item',
                    DB::raw("SUM(CASE WHEN VT.NCAT IN ('PBC','PBR','MRE','RQI','STOP','BKREC','KSREC','KMREC') THEN S.RecdQty ELSE 0 END) as totalrec"),
                    DB::raw("SUM(CASE WHEN VT.NCAT IN ('PRR','PRC','RQR','BKISS','KSISS','KMISS','HKISS') THEN S.IssQty ELSE 0 END) as totaliss")
                )
                ->groupBy('S.Item')
                ->get()
                ->keyBy('Item');

            // ── Step 4: Transaction rows ──────────────────────────────────────
            $txnRows = DB::table('stock as S')
                ->leftJoin('itemmast as I', function ($j) {
                    $j->on('S.Item', '=', 'I.Code')->where('I.ItemType', 'Store');
                })
                ->leftJoin('voucher_type as VT', function ($j) {
                    $j->on('S.VType', '=', 'VT.V_Type')
                      ->on('S.propertyid', '=', 'VT.propertyid');
                })
                ->leftJoin('stock as S1', function ($j) {
                    $j->on('S.ContraDocId', '=', 'S1.DocId')
                      ->on('S.ContraSno', '=', 'S1.Sno');
                })
                ->leftJoin('godown_mast as D', 'D.scode', '=', 'S1.GodownCode')
                ->where('S.propertyid', $propertyId)
                ->whereBetween('S.VDate', [$fromdate, $todate])
                ->where('S.GodownCode', $godownCode)
                ->where('S.delflag', 'N')
                ->whereIn('S.Item', $itemCodes)
                ->whereIn('VT.NCAT', $allNcat)
                ->select(
                    'S.Item',
                    'S.VDate as vdate',
                    'S.VType as vtype',
                    'S.VNo as vno',
                    'S.Amount as amount',
                    DB::raw("CASE WHEN VT.NCAT IN ('PBC','PBR','MRE','RQI','STOP','BKREC','KSREC','KMREC') THEN S.RecdQty ELSE 0 END AS qtyrec"),
                    DB::raw("CASE WHEN VT.NCAT IN ('PRR','PRC','RQR','BKISS','KSISS','KMISS','HKISS') THEN S.IssQty ELSE 0 END AS qtyiss"),
                    DB::raw("CASE WHEN VT.NCAT = 'HKISS' THEN S.roomno ELSE D.Name END AS particulars"),
                    DB::raw("CASE WHEN VT.NCAT IN ('PBC','PBR','MRE','RQI','STOP','BKREC','KSREC','KMREC') THEN 'A'
                                  WHEN VT.NCAT IN ('PRR','PRC','RQR','BKISS','KSISS','KMISS','HKISS') THEN 'B'
                                  ELSE 'C' END AS seqno")
                )
                ->orderBy('S.Item')
                ->orderBy('S.VDate')
                ->orderByRaw("CASE WHEN VT.NCAT IN ('PBC','PBR','MRE','RQI','STOP','BKREC','KSREC','KMREC') THEN 'A'
                                   WHEN VT.NCAT IN ('PRR','PRC','RQR','BKISS','KSISS','KMISS','HKISS') THEN 'B'
                                   ELSE 'C' END")
                ->orderBy('S.VType')
                ->orderBy('S.VNo')
                ->get()
                ->groupBy('Item');

            // ── Step 5: Build response ────────────────────────────────────────
            $data = [];
            foreach ($itemsQuery as $code => $item) {
                $openQty  = (float) ($opening->get($code)->openingqty ?? 0);
                $recQty   = (float) ($period->get($code)->totalrec    ?? 0);
                $issQty   = (float) ($period->get($code)->totaliss    ?? 0);
                $closeQty = $openQty + $recQty - $issQty;

                $bal     = $openQty;
                $txnList = [];
                foreach ($txnRows->get($code, collect()) as $t) {
                    $rec  = (float) $t->qtyrec;
                    $iss  = (float) $t->qtyiss;
                    $bal += $rec - $iss;
                    $txnList[] = [
                        'vdate'       => substr($t->vdate, 0, 10),
                        'vtype'       => $t->vtype,
                        'vno'         => $t->vno,
                        'amount'      => (float) $t->amount,
                        'particulars' => $t->particulars,
                        'qtyrec'      => $rec,
                        'qtyiss'      => $iss,
                        'balance'     => $bal,
                    ];
                }

                $data[] = [
                    'itemcode'     => $code,
                    'itemname'     => $item->ItemName  ?? $code,
                    'unit'         => $item->Unit      ?? '',
                    'issueunit'    => $item->IssueUnit ?? '',
                    'openingqty'   => $openQty,
                    'totalrec'     => $recQty,
                    'totaliss'     => $issQty,
                    'closingqty'   => $closeQty,
                    'transactions' => $txnList,
                ];
            }

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Amenities Usage Report ───────────────────────────────────────────────────

    public function assignmentreport(Request $request)
    {
        $company   = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
                           ->where('state_code', $company->state_code)
                           ->value('name');

        return view('property.assignmentreport', [
            'ncurdate'  => $this->ncurdate,
            'company'   => $company,
            'statename' => $statename,
        ]);
    }

    public function assignmentreportfetch(Request $request)
    {
        $fromdate = $request->input('fromdate');
        $todate   = $request->input('todate');

        $data = DB::table('hkroomassigns as HA')
            ->select(
                'HA.vdate as Date',
                'HM.name as Housekeeper',
                DB::raw('COUNT(HA.assno) as TotalRooms'),
                DB::raw("SUM(CASE WHEN HA.cleaningstatus = 'Completed'   THEN 1 ELSE 0 END) AS CompletedRooms"),
                DB::raw("SUM(CASE WHEN HA.cleaningstatus = 'In Progress' THEN 1 ELSE 0 END) AS InProgressRooms"),
                DB::raw("SUM(CASE WHEN HA.cleaningstatus = ''            THEN 1 ELSE 0 END) AS PendingRooms"),
                DB::raw('ROUND(AVG(HA.esttime), 0)      AS AvgEst'),
                DB::raw('ROUND(AVG(HC.totalminutes), 0) AS AvgActual')
            )
            ->leftJoin('housekeeparmast as HM', 'HA.code', '=', 'HM.scode')
            ->leftJoin('hkcleaninghdr as HC',   'HA.assno', '=', 'HC.assno')
            ->where('HA.propertyid', $this->propertyid)
            ->whereBetween('HA.vdate', [$fromdate, $todate])
            ->groupBy('HA.vdate', 'HA.code', 'HM.name')
            ->orderBy('HA.vdate')
            ->orderBy('HM.name')
            ->get();

        return response()->json($data);
    }

    public function fetchassignmentreport(Request $request)
    {
        $fromdate = $request->input('fromdate', $this->ncurdate);
        $todate   = $request->input('todate',   $this->ncurdate);

        $data = DB::table('hkcleaningftr as HF')
            ->join('itemmast as I',           'HF.item',  '=', 'I.Code')
            ->leftJoin('hkcleaninghdr as HD', 'HF.assid', '=', 'HD.assno')
            ->where('HF.propertyid',  $this->propertyid)
            ->where('I.Property_ID',  $this->propertyid)
            ->where('HF.Ctype',       'Amenities')
            ->whereBetween('HD.cleaningdate', [$fromdate, $todate])
            ->groupBy('HF.item', 'I.Name', 'HD.roommo')
            ->select(
                'HD.roommo',
                DB::raw('I.Name as Item'),
                DB::raw('SUM(HF.qty) as Qty')
            )
            ->orderBy('HD.roommo')
            ->get();

        return response()->json(['data' => $data]);
    }

    // ─── Housekeeper Assignment Screen ───────────────────────────────────────────

    public function assignmentreportprint(Request $request)
    {
        $company    = Companyreg::where('propertyid', $this->propertyid)->first();
        $propertyId = $this->propertyid;
        $asOnDate      = getNcurDate();
        $effectiveDate = $asOnDate;

        // ── Room Status Counts (same source as inhoseroomstatus page) ─────────
        $roomStatus = (new \App\Http\Controllers\FrontOffice\RoomStatus)->getRoomStatusCounts($propertyId, $asOnDate);

        // VD + OD = Total Dirty
        $vacantDirty       = $roomStatus['VD'];
        $occupiedDirty     = $roomStatus['OD'];
        $totalDirty        = $vacantDirty + $occupiedDirty;

        // ── VIP Rooms (in-house guests with vipStatus = Y) ────────────────────
        $vipRooms = DB::table('roomocc as ro')
            ->join('guestprof as gp', function ($j) use ($propertyId) {
                $j->on('gp.guestcode', '=', 'ro.guestprof')
                    ->where('gp.propertyid', $propertyId);
            })
            ->where('ro.propertyid', $propertyId)
            ->whereNull('ro.type')
            ->where('gp.vipStatus', 'Y')
            ->count();

        // ── Early Check-in (today's arrivals not yet checked in) ─────────────
        $earlyCheckin = DB::table('grpbookingdetails as g')
            ->where('g.Property_ID', $propertyId)
            ->where('g.ArrDate', $asOnDate)
            ->where('g.Cancel', 'N')
            ->where('g.ContraDocId', '')
            ->count();

        // ── Inspection Pending (rooms with room_stat = 'I') ───────────────────
        $inspectionPending = DB::table('room_mast')
            ->where('propertyid', $propertyId)
            ->where('type', 'RO')
            ->where('inclcount', 'Y')
            ->where('room_stat', 'I')
            ->count();

        // ── Housekeepers Available / Total ────────────────────────────────────
        // housekeeparmast.activeYN stores 'Y' string (varchar)
        $hkTotal     = HousekeeperMast::where('propertyid', $propertyId)->count();
        $hkAvailable = HousekeeperMast::where('propertyid', $propertyId)
            ->where('activeYN', 'Y')
            ->count();

        // ── Supervisors ───────────────────────────────────────────────────────
        $supervisors = HkSupervisor::where('propertyid', $propertyId)
            ->where('activeyn', 1)
            ->orderBy('name')
            ->get();

        // ── Unassigned Dirty Rooms ────────────────────────────────────────────
        // Rooms where room_stat = 'D' (dirty), joined with floor, room category,
        // and roomocc to determine Occupied Dirty vs Vacant Dirty and Priority.
        $unassignedRooms = DB::table('room_mast as RM')
            ->distinct()
            ->select(
                'RM.rcode as roomno',
                'FL.name as floor',
                'C.name as type',
                DB::raw("
                    CASE
                        WHEN RC.folioNo > 0 AND RM.room_stat = 'D' THEN 'Occupied Dirty'
                        ELSE 'Vacant Dirty'
                    END AS roomstatus
                "),
                DB::raw("
                    CASE
                        WHEN RC.folioNo > 0 AND RM.room_stat = 'D' THEN 'Normal'
                        ELSE 'High'
                    END AS priority
                ")
            )
            ->leftJoin('hkfloors as FL', function ($j) {
                $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('roomocc as RC', function ($j) use ($propertyId) {
                $j->on('RM.rcode', '=', 'RC.roomno')
                    ->whereNull('RC.chkoutdate')
                    ->where('RC.propertyid', $propertyId);
            })
            ->leftJoin('grpbookingdetails as B', function ($j) use ($effectiveDate) {
                $j->on('RM.rcode', '=', 'B.RoomNo')
                    ->where('B.Cancel', 'N')
                    ->where('B.ContraDocId', '')
                    ->where('B.ArrDate', $effectiveDate);
            })
            ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
            ->where('RM.type', 'RO')
            ->where('RM.propertyid', $propertyId)
            ->where('RM.room_stat', 'D')
            ->whereNotIn('RM.rcode', function ($query) use ($propertyId, $effectiveDate) {
                $query->select('roomno')
                    ->from('hkroomassigns')
                    ->where('propertyid', $propertyId)
                    ->where('vdate', $effectiveDate)
                    ->where('status', 'dirty');
            })
            ->orderBy('RM.rcode')
            ->get();

        // ── Rooms where cleaning has been started (cleaningstatus = 'In Progress') ──
        $cleaningStartedRooms = DB::table('hkroomassigns as HA')
            ->select(
                'HA.id',
                'HA.roomno',
                'HA.code',
                'HA.assno',
                'HA.supervisor',
                'HA.ctype',
                'HA.cleaningstatus',
                'FL.name as floor',
                'C.name as type',
                'HK.name as hkname',
                'SUP.name as supname',
                'CT.name as ctypename',
                DB::raw("CASE WHEN RC.folioNo > 0 AND RM.room_stat = 'D' THEN 'Occupied Dirty' ELSE 'Vacant Dirty' END AS roomstatus")
            )
            ->join('room_mast as RM', function ($j) use ($propertyId) {
                $j->on('HA.roomno', '=', 'RM.rcode')
                    ->where('RM.propertyid', $propertyId)
                    ->where('RM.type', 'RO');
            })
            ->leftJoin('hkfloors as FL', function ($j) {
                $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('roomocc as RC', function ($j) use ($propertyId) {
                $j->on('RM.rcode', '=', 'RC.roomno')
                    ->whereNull('RC.chkoutdate')
                    ->where('RC.propertyid', $propertyId);
            })
            ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
            ->leftJoin('housekeeparmast as HK', function ($j) use ($propertyId) {
                $j->on('HA.code', '=', 'HK.scode')
                    ->where('HK.propertyid', $propertyId);
            })
            ->leftJoin('hksupervisor as SUP', function ($j) use ($propertyId) {
                $j->on('HA.supervisor', '=', 'SUP.code')
                    ->where('SUP.propertyid', $propertyId);
            })
            ->leftJoin('hkcleaningtype as CT', function ($j) use ($propertyId) {
                $j->on('HA.ctype', '=', 'CT.code')
                    ->where('CT.propertyid', $propertyId);
            })
            ->where('HA.propertyid', $propertyId)
            ->where('HA.vdate', $effectiveDate)
            ->where('HA.status', 'dirty')
            ->where('HA.cleaningstatus', 'In Progress')
            ->orderBy('HA.roomno')
            ->get();

        // ── Housekeepers List for Assignment Dropdown ────────────────────────
        $housekeepers = HousekeeperMast::where('propertyid', $propertyId)
            ->where('activeYN', 'Y')
            ->orderBy('name')
            ->get();

        $cleaningTypes = DB::table('hkcleaningtype')
            ->where('propertyid', $propertyId)
            ->orderBy('sn')
            ->get();

        $assignedRoomsRaw = DB::table('hkroomassigns as HA')
            ->distinct()
            ->select(
                'HA.code',
                'HA.supervisor',
                'HA.roomno',
                'HA.ctype',
                'HA.esttime',
                'HA.assno',
                'FL.name as floor',
                'C.name as type',
                DB::raw("CASE WHEN RC.folioNo > 0 AND RM.room_stat = 'D' THEN 'Occupied Dirty' ELSE 'Vacant Dirty' END AS roomstatus")
            )
            ->join('room_mast as RM', function ($j) use ($propertyId) {
                $j->on('HA.roomno', '=', 'RM.rcode')
                    ->where('RM.propertyid', $propertyId)
                    ->where('RM.type', 'RO');
            })
            ->leftJoin('hkfloors as FL', function ($j) {
                $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('roomocc as RC', function ($j) use ($propertyId) {
                $j->on('RM.rcode', '=', 'RC.roomno')
                    ->whereNull('RC.chkoutdate')
                    ->where('RC.propertyid', $propertyId);
            })
            ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
            ->where('HA.propertyid', $propertyId)
            ->where('HA.vdate', $effectiveDate)
            ->where('HA.status', 'dirty')
            ->where(function ($q) {
                $q->where('HA.cleaningstatus', '')
                  ->orWhereNull('HA.cleaningstatus');
            })
            ->orderBy('HA.code')
            ->orderBy('HA.roomno')
            ->get();

        $assignedRoomsByHk = [];
        foreach ($assignedRoomsRaw as $row) {
            if (!isset($assignedRoomsByHk[$row->code])) {
                $assignedRoomsByHk[$row->code] = [
                    'supervisor' => $row->supervisor,
                    'assno'      => $row->assno,
                    'rows'       => [],
                ];
            }
            $assignedRoomsByHk[$row->code]['rows'][] = $row;
        }

        return view('property.housekeeping.assignmentreportprint', [
            'company'             => $company,
            'asOnDate'            => $effectiveDate,
            'totalDirty'          => $totalDirty,
            'vacantDirty'         => $vacantDirty,
            'occupiedDirty'       => $occupiedDirty,
            'vipRooms'            => $vipRooms,
            'earlyCheckin'        => $earlyCheckin,
            'inspectionPending'   => $inspectionPending,
            'hkAvailable'         => $hkAvailable,
            'hkTotal'             => $hkTotal,
            'supervisors'         => $supervisors,
            'unassignedRooms'     => $unassignedRooms,
            'housekeepers'        => $housekeepers,
            'cleaningTypes'       => $cleaningTypes,
            'assignedRoomsByHk'   => $assignedRoomsByHk,
            'cleaningStartedRooms' => $cleaningStartedRooms,
        ]);
    }


     public function printAssignment(Request $request) 
{
    $propertyId    = $this->propertyid;
    $effectiveDate = $this->ncurdate;
    $hkCode        = $request->input('hk_code', '');
    $company = \App\Models\Companyreg::where('propertyid', $propertyId)->first();

    $rows = DB::table('hkroomassigns as HA')
        ->distinct()
        ->select(
            'HA.code',
            'HA.supervisor',
            'HA.roomno',
            'HA.ctype',
            'HA.esttime',
            'HA.assno',
            'FL.name as floor',
            'C.name as type',
            'HK.name as hkname',
            'SUP.name as supname',
            DB::raw("CASE WHEN RC.folioNo > 0 AND RM.room_stat = 'D' THEN 'Occupied Dirty' ELSE 'Vacant Dirty' END AS roomstatus"),
            DB::raw("CASE WHEN RC.folioNo > 0 AND RM.room_stat = 'D' THEN 'Normal' ELSE 'High' END AS priority"),
            'CT.name as ctypename'
        )
        ->join('room_mast as RM', function ($j) use ($propertyId) {
            $j->on('HA.roomno', '=', 'RM.rcode')
                ->where('RM.propertyid', $propertyId)
                ->where('RM.type', 'RO');
        })
        ->leftJoin('hkfloors as FL', function ($j) {
            $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
        })
        ->leftJoin('roomocc as RC', function ($j) use ($propertyId) {
            $j->on('RM.rcode', '=', 'RC.roomno')
                ->whereNull('RC.chkoutdate')
                ->where('RC.propertyid', $propertyId);
        })
        ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
        ->leftJoin('housekeeparmast as HK', function ($j) use ($propertyId) {
            $j->on('HA.code', '=', 'HK.scode')
                ->where('HK.propertyid', $propertyId);
        })
        ->leftJoin('hksupervisor as SUP', function ($j) use ($propertyId) {
            $j->on('HA.supervisor', '=', 'SUP.code')
                ->where('SUP.propertyid', $propertyId);
        })
        ->leftJoin('hkcleaningtype as CT', function ($j) use ($propertyId) {
            $j->on('HA.ctype', '=', 'CT.code')
                ->where('CT.propertyid', $propertyId);
        })
        ->where('HA.propertyid', $propertyId)
        ->where('HA.vdate', $effectiveDate)
        ->where('HA.status', 'dirty')
        ->where(function ($q) {
            $q->where('HA.cleaningstatus', '')
              ->orWhereNull('HA.cleaningstatus');
        })
         ->when($hkCode, function ($q) use ($hkCode) {     // ← NEW BLOCK
                return $q->where('HA.code', $hkCode);
            })
        ->orderBy('HA.code')
        ->orderBy('HA.roomno')
        ->get();

    $groupedByHk = [];
    foreach ($rows as $row) {
        $code = $row->code;
        if (!isset($groupedByHk[$code])) {
            $groupedByHk[$code] = [
                'hkname'  => $row->hkname ?? $code,
                'supname' => $row->supname ?? '',
                'assno'   => $row->assno ?? '',
                'rooms'   => [],
            ];
        }
        $groupedByHk[$code]['rooms'][] = $row;
    }

    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('property.print.hkassignmentprint', [
        'company'       => $company,
        'groupedByHk'   => $groupedByHk,
        'asOnDate'      => $effectiveDate,
        'totalAssigned' => $rows->count(),
        'totalHk'       => count($groupedByHk),
    ])->setPaper('a4', 'portrait');

    return $pdf->stream('hk-assignment-' . $effectiveDate . '.pdf');
}

// NEW METHOD assignmentViewPage()

 public function assignmentViewPage(Request $request)
    {
        $propertyId = $this->propertyid;
        $company = Companyreg::where('propertyid', $propertyId)->first();
        $housekeepers = HousekeeperMast::where('propertyid', $propertyId)
            ->where('activeYN', 'Y')
            ->orderBy('name')
            ->get();

        return view('property.housekeeping.assignmentview', [
            'company'      => $company,
            'housekeepers' => $housekeepers,
            'fromDate'     => $request->input('from_date', $this->ncurdate),
            'toDate'       => $request->input('to_date', $this->ncurdate),
        ]);
    }

    //assignmentViewReport()

     public function assignmentViewReport(Request $request)
    {
        $propertyId = $this->propertyid;
        $fromDate = $request->input('from_date', $this->ncurdate);
        $toDate = $request->input('to_date', $this->ncurdate);
        $hkCode = $request->input('hk_code', '');

        $data = DB::table('hkroomassigns as HA')
            ->select(
                'HA.vdate', 'HA.code', 'HA.roomno', 'HA.ctype',
                'HA.supervisor', 'HA.assno',
                'HK.name as hkname', 'SUP.name as supname',
                'FL.name as floor', 'C.name as type', 'CT.name as ctypename'
            )
            ->join('room_mast as RM', function ($j) use ($propertyId) {
                $j->on('HA.roomno', '=', 'RM.rcode')
                    ->where('RM.propertyid', $propertyId)
                    ->where('RM.type', 'RO');
            })
            ->leftJoin('hkfloors as FL', function ($j) {
                $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
            ->leftJoin('housekeeparmast as HK', function ($j) use ($propertyId) {
                $j->on('HA.code', '=', 'HK.scode')
                    ->where('HK.propertyid', $propertyId);
            })
            ->leftJoin('hksupervisor as SUP', function ($j) use ($propertyId) {
                $j->on('HA.supervisor', '=', 'SUP.code')
                    ->where('SUP.propertyid', $propertyId);
            })
            ->leftJoin('hkcleaningtype as CT', function ($j) use ($propertyId) {
                $j->on('HA.ctype', '=', 'CT.code')
                    ->where('CT.propertyid', $propertyId);
            })
            ->where('HA.propertyid', $propertyId)
            ->where('HA.status', 'dirty')
            ->whereBetween('HA.vdate', [$fromDate, $toDate])
            ->when($hkCode, function ($q) use ($hkCode) {
                return $q->where('HA.code', $hkCode);
            })
            ->orderBy('HA.vdate')->orderBy('HA.code')->orderBy('HA.roomno')
            ->get();

        // Group by date → HK
        $grouped = [];
        foreach ($data as $row) {
            $date = $row->vdate; $code = $row->code;
            if (!isset($grouped[$date])) $grouped[$date] = [];
            if (!isset($grouped[$date][$code])) {
                $grouped[$date][$code] = [
                    'hkname' => $row->hkname ?? $code,
                    'supname' => $row->supname ?? '',
                    'assno' => $row->assno,
                    'rooms' => [],
                ];
            }
            $grouped[$date][$code]['rooms'][] = $row;
        }

        // Build HTML table + summary
        $html = '';
        if (empty($grouped)) {
            $html = '<div class="text-center text-muted py-5">No assignments found.</div>';
        } else {
            foreach ($grouped as $date => $hks) {
                $dateFormatted = \Carbon\Carbon::parse($date)->format('d-M-Y');
                $dateTotal = collect($hks)->sum(fn($h) => count($h['rooms']));
                $html .= '<div class="hk-block open mb-3">';
                $html .= '<div class="hk-header px-3 py-2">📅 ' . $dateFormatted . ' — <span class="text-primary">' . $dateTotal . ' Rooms</span></div>';
                $html .= '<div class="hk-body-scroll p-0">';
                $html .= '<table class="table table-sm table-hover mb-0 align-middle">';
                $html .= '<thead class="table-light"><tr><th>HK Name</th><th>Supervisor</th><th>AssNo</th><th>Room No.</th><th>Floor</th><th>Type</th><th>CType</th></tr></thead><tbody>';
                foreach ($hks as $code => $hk) {
                    $rowspan = count($hk['rooms']); $first = true;
                    foreach ($hk['rooms'] as $room) {
                        $html .= '<tr>';
                        if ($first) {
                            $html .= '<td rowspan="' . $rowspan . '" class="fw-semibold">' . e($hk['hkname']) . '</td>';
                            $html .= '<td rowspan="' . $rowspan . '">' . e($hk['supname']) . '</td>';
                            $html .= '<td rowspan="' . $rowspan . '"><span class="badge bg-info text-dark">#' . e($hk['assno'] ?? '-') . '</span></td>';
                            $first = false;
                        }
                        $html .= '<td>' . e($room->roomno) . '</td><td>' . e($room->floor ?? '—') . '</td><td>' . e($room->type ?? '—') . '</td><td>' . e($room->ctypename ?? '—') . '</td>';
                        $html .= '</tr>';
                    }
                }
                $html .= '</tbody></table></div></div>';
            }
        }
        return response()->json(['success' => true, 'html' => $html, 'total' => $data->count()]);
    }




//  public function saveAssignmentReport(Request $request)
//     {
//         $propertyId = $this->propertyid;
//         $asOnDate = getNcurDate();
//         $assignments = json_decode($request->input('assignments', '[]'), true);
//         $userName = Auth::user()->name ?? '';
//         $vtime = date('H:i:s');

//         DB::transaction(function () use ($propertyId, $asOnDate, $assignments, $userName, $vtime) {

//             // ── Step 1: Global max assno fetch karo (property level) ──────────
//             $globalMax = DB::table('hkroomassigns')
//                 ->where('propertyid', $propertyId)
//                 ->max('assno') ?? 0;

//             // ── Step 2: Existing records fetch karo (aaj ke dirty) ────────────
//             $existingRecords = DB::table('hkroomassigns')
//                 ->where('propertyid', $propertyId)
//                 ->where('vdate', $asOnDate)
//                 ->where('status', 'dirty')
//                 ->get(['id', 'code', 'roomno', 'supervisor', 'assno'])
//                 ->keyBy(function($item) {
//                     return $item->code . '_' . $item->roomno;  // Unique key: housekeeper_room
//                 });

//             // ── Step 3: Build incoming data map ───────────────────────────────
//             $incomingMap = [];
//             $supervisorAssnoMap = [];

//             // Har supervisor ko unique assno assign (agar naya ho)
//             foreach ($assignments as $hk) {
//                 $supervisor = $hk['supervisor'] ?? '';
//                 if (empty($supervisor) || isset($supervisorAssnoMap[$supervisor])) continue;
//                 $globalMax++;
//                 $supervisorAssnoMap[$supervisor] = $globalMax;
//             }

//             foreach ($assignments as $hk) {
//                 if (empty($hk['scode']) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;
//                 $code = $hk['scode'];
//                 $supervisor = $hk['supervisor'] ?? '';
//                 $assno = $supervisorAssnoMap[$supervisor] ?? null;

//                 foreach ($hk['rooms'] as $room) {
//                     if (empty($room['roomno'])) continue;
//                     $key = $code . '_' . $room['roomno'];
//                     $incomingMap[$key] = [
//                         'code'       => $code,
//                         'roomno'     => $room['roomno'],
//                         'supervisor' => $supervisor,
//                         'ctype'      => $room['ctype'] ?? null,
//                         'esttime'    => $room['esttime'] ?? null,
//                         'assno'      => $assno,
//                     ];
//                 }
//             }

//             // ── Step 4: UPDATE existing records, INSERT naye records ─────────
//             // DELETE kabhi nahi hoga — sirf modify aur add
//             $toInsert = [];

//             foreach ($incomingMap as $key => $data) {
//                 if ($existingRecords->has($key)) {
//                     // Record already DB mein hai → sirf UPDATE karo (supervisor/ctype/assno)
//                     $existing = $existingRecords->get($key);
//                     DB::table('hkroomassigns')
//                         ->where('id', $existing->id)
//                         ->update([
//                             'supervisor' => $data['supervisor'],
//                             'ctype'      => $data['ctype'],
//                             'esttime'    => $data['esttime'],
//                             'assno'      => $data['assno'],
//                             'u_name'     => $userName,
//                             'vtime'      => $vtime,
//                         ]);
//                 } else {
//                     // Naya room assign kiya → INSERT karo
//                     $toInsert[] = [
//                         'propertyid' => $propertyId,
//                         'code'       => $data['code'],
//                         'vdate'      => $asOnDate,
//                         'vtime'      => $vtime,
//                         'roomno'     => $data['roomno'],
//                         'status'     => 'dirty',
//                         'supervisor' => $data['supervisor'],
//                         'ctype'      => $data['ctype'],
//                         'esttime'    => $data['esttime'],
//                         'u_name'     => $userName,
//                         'assno'      => $data['assno'],
//                     ];
//                 }
//             }

//             // ── Step 5: Batch INSERT naye records ────────────────────────────
//             if (!empty($toInsert)) {
//                 DB::table('hkroomassigns')->insert($toInsert);
//             }

//             // NOTE: DELETE query intentionally removed.
//             // Rooms sirf UPDATE ya INSERT hoti hain — kabhi delete nahi hoti.
//         });

//         return response()->json(['success' => true, 'message' => 'Assignment saved successfully.']);
//     }

 public function saveAssignmentReport(Request $request)
    {
        $permission = revokeopen(151113) ?? revokeopen(151114);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        $propertyId = $this->propertyid;
        $asOnDate = getNcurDate();
        $assignments = json_decode($request->input('assignments', '[]'), true);
        $userName = Auth::user()->name ?? '';
        $vtime = date('H:i:s');

        DB::transaction(function () use ($propertyId, $asOnDate, $assignments, $userName, $vtime) {

            // ── Step 1: Global max assno fetch karo (property level) ──────────
            $globalMax = DB::table('hkroomassigns')
                ->where('propertyid', $propertyId)
                ->max('assno') ?? 0;

            // ── Step 2: Existing rooms fetch karo (aaj ke dirty) ─────────────
            // Sirf roomno chahiye taaki naye rooms pehchaan sakein
            $existingRoomKeys = DB::table('hkroomassigns')
                ->where('propertyid', $propertyId)
                ->where('vdate', $asOnDate)
                ->where('status', 'dirty')
                ->get(['code', 'roomno'])
                ->keyBy(function ($item) {
                    return $item->code . '_' . $item->roomno;
                });

            // ── Step 3: Har HK ko assno assign karo ──────────────────────────
            // Existing HK ka assno preserve karo, naye ko increment karo
            $existingAssnoByCode = DB::table('hkroomassigns')
                ->where('propertyid', $propertyId)
                ->where('vdate', $asOnDate)
                ->where('status', 'dirty')
                ->whereNotNull('assno')
                ->where('assno', '>', 0)
                ->get(['code', 'assno'])
                ->keyBy('code');

            $hkAssnoMap = [];
            foreach ($assignments as $hk) {
                $code = $hk['scode'] ?? '';
                $supervisor = $hk['supervisor'] ?? '';
                if (empty($code) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;
                if (isset($hkAssnoMap[$code])) continue;

                // Sirf supervisor wale HK ko assno assign karo
                if (empty($supervisor)) continue;

                if (isset($existingAssnoByCode[$code])) {
                    // Purana valid assno preserve karo
                    $hkAssnoMap[$code] = $existingAssnoByCode[$code]->assno;
                } else {
                    // Naya HK with supervisor → naya assno
                    $globalMax++;
                    $hkAssnoMap[$code] = $globalMax;
                }
            }

            // ── Step 4: Naye rooms INSERT karo (jo DB mein nahi hain) ────────
            $toInsert = [];
            foreach ($assignments as $hk) {
                $code = $hk['scode'] ?? '';
                if (empty($code) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;

                foreach ($hk['rooms'] as $room) {
                    if (empty($room['roomno'])) continue;
                    $key = $code . '_' . $room['roomno'];
                    if ($existingRoomKeys->has($key)) continue; // Already hai → skip INSERT

                    $toInsert[] = [
                        'propertyid' => $propertyId,
                        'code'       => $code,
                        'vdate'      => $asOnDate,
                        'vtime'      => $vtime,
                        'roomno'     => $room['roomno'],
                        'status'     => 'dirty',
                        'ctype'      => $room['ctype'] ?? null,
                        'esttime'    => $room['esttime'] ?? null,
                        'u_name'     => $userName,
                        'supervisor' => $hk['supervisor'] ?? '',
                        'assno'      => $hkAssnoMap[$code] ?? null,
                    ];
                }
            }

            if (!empty($toInsert)) {
                DB::table('hkroomassigns')->insert($toInsert);
            }

            // ── Step 5: Senior ki query — per HK ek single UPDATE ────────────
            // UPDATE hkroomassigns
            //   SET assno = ?, supervisor = ?
            // WHERE propertyid = ? AND code = ? AND vdate = ? AND roomno IN (...)
            foreach ($assignments as $hk) {
                $code = $hk['scode'] ?? '';
                $supervisor = $hk['supervisor'] ?? '';
                if (empty($code) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;

                // Supervisor nahi diya → assno/supervisor update mat karo
                if (empty($supervisor)) continue;

                $roomNos = collect($hk['rooms'])->pluck('roomno')->filter()->values()->toArray();
                if (empty($roomNos)) continue;

                $assno = $hkAssnoMap[$code] ?? null;
                if (empty($assno)) continue;

                DB::table('hkroomassigns')
                    ->where('propertyid', $propertyId)
                    ->where('code', $code)
                    ->where('vdate', $asOnDate)
                    ->whereIn('roomno', $roomNos)
                    ->update([
                        'assno'      => $assno,
                        'supervisor' => $supervisor,
                        'u_name'     => $userName,
                        'vtime'      => $vtime,
                    ]);
            }

            // NOTE: DELETE intentionally nahi hai.
            // Rooms sirf INSERT ya UPDATE hoti hain, kabhi delete nahi hoti.
        });

        return response()->json(['success' => true, 'message' => 'Assignment saved successfully.']);
    }

    // ─── Unassign Rooms ───────────────────────────────────────────────────────────
    // Senior ki query:
    // UPDATE hkroomassigns
    //   SET assno = 0, supervisor = ''
    // WHERE propertyid = ? AND code = ? AND vdate = ncurdate AND roomno IN (...)

    public function unassignRooms(Request $request)
    {
        $permission = revokeopen(151113) ?? revokeopen(151114);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        $propertyId = $this->propertyid;
        $asOnDate   = getNcurDate();
        $rooms      = json_decode($request->input('rooms', '[]'), true);  // array of {roomno, code}
        $userName   = Auth::user()->name ?? '';

        if (empty($rooms) || !is_array($rooms)) {
            return response()->json(['success' => false, 'message' => 'No rooms provided.']);
        }

        try {
            DB::transaction(function () use ($propertyId, $asOnDate, $rooms, $userName) {

                // Group rooms by HK code — ek HK ke saare rooms ek saath UPDATE honge
                $grouped = [];
                foreach ($rooms as $r) {
                    $code   = $r['code']   ?? '';
                    $roomno = $r['roomno'] ?? '';
                    if (empty($code) || empty($roomno)) continue;
                    $grouped[$code][] = $roomno;
                }

                // Per HK — senior ki exact query
                // UPDATE hkroomassigns SET assno=0, supervisor=''
                // WHERE propertyid=? AND code=? AND vdate=? AND roomno IN (...)
                foreach ($grouped as $code => $roomNos) {
                    DB::table('hkroomassigns')
                        ->where('propertyid', $propertyId)
                        ->where('code', $code)
                        ->where('vdate', $asOnDate)
                        ->whereIn('roomno', $roomNos)
                        ->update([
                            'assno'      => 0,
                            'supervisor' => '',
                            'code'       => 0,
                            'u_name'     => $userName,
                        ]);
                }
            });

            return response()->json(['success' => true, 'message' => 'Rooms unassigned successfully.']);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    // ─── Room Status Board ────────────────────────────────────────────────────────


    private function getRoomsWithStatus($propertyId)
    {
        $rooms = DB::table('room_mast as RM')
            ->select(
                'RM.rcode as roomno',
                'FL.name as floor',
                'RM.room_cat as room_cat_code',
                'C.name as roomcatname',
                'RM.room_stat',
                'RC.folioNo'
            )
            ->leftJoin('hkfloors as FL', function ($j) {
                $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
            })
            ->leftJoin('roomocc as RC', function ($j) use ($propertyId) {
                $j->on('RM.rcode', '=', 'RC.roomno')
                    ->whereNull('RC.chkoutdate')
                    ->where('RC.propertyid', $propertyId);
            })
            ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
            ->where('RM.propertyid', $propertyId)
            ->where('RM.type', 'RO')
            ->where('RM.inclcount', 'Y')
            ->orderBy('FL.name')
            ->orderBy('RM.rcode')
            ->get();

        return $rooms->map(function ($room) {
            $room->status = $this->deriveRoomStatus($room);
            return $room;
        });
    }

    public function roomstatusboard(Request $request)
    {
        $company    = Companyreg::where('propertyid', $this->propertyid)->first();
        $propertyId = $this->propertyid;

        // 1. Fetch Master Rooms
        $allRooms = $this->getRoomsWithStatus($propertyId);

        // 1a. Overlay active blockouts (OOO / Maintenance) from roomblockout table
        // Active = cleardate still NULL (not cleared yet)
        $activeBlockouts = DB::table('roomblockout')
            ->where('propertyid', $propertyId)
            ->whereNull('cleardate')
            ->get()
            ->keyBy('roomcode');

        $allRooms = $allRooms->map(function ($room) use ($activeBlockouts) {
            $blockout = $activeBlockouts->get($room->roomno);
            if ($blockout) {
                $blockValue = strtolower(trim($blockout->block ?? ''));
                $room->status = str_contains($blockValue, 'maint') ? 'MAINT' : 'OOO';
                $room->block_reason = $blockout->reasons;
            }
            return $room;
        });

        // 2. Fetch Unique Room Types for dropdown filter
        $roomTypes = $allRooms->unique('room_cat_code')
            ->filter(fn($room) => !empty($room->room_cat_code))
            ->map(fn($room) => [
                'room_cat_code' => $room->room_cat_code,
                'roomcatname'   => $room->roomcatname ?? 'Unknown Type',
            ]);

        // 3. Dynamic Filtering
        $filteredRooms = clone $allRooms;
        if ($request->filled('floor')) {
            $filteredRooms = $filteredRooms->where('floor', $request->floor);
        }
        if ($request->filled('status')) {
            $filteredRooms = $filteredRooms->where('status', $request->status);
        }
        if ($request->filled('room_type')) {
            $filteredRooms = $filteredRooms->where('room_cat_code', $request->room_type);
        }

        // 4. TOP STAT CARDS
        $totalRooms        = $allRooms->count();
        $occupiedRooms      = $allRooms->where('status', 'OCC')->count();
        $occupiedDirty      = $allRooms->where('status', 'OD')->count();
        $vacantDirty        = $allRooms->where('status', 'VD')->count();
        $vacantClean        = $allRooms->where('status', 'VC')->count();
        $outOfOrder         = $allRooms->where('status', 'OOO')->count();
        $maintenanceRooms   = $allRooms->where('status', 'MAINT')->count();
        $inspectionPending  = $allRooms->where('status', 'INSPECT')->count();

        $totalActualOcc = $allRooms->whereIn('status', ['OCC', 'OD'])->count();
        $occupancyRate  = $totalRooms > 0 ? round(($totalActualOcc / $totalRooms) * 100, 2) : 0;

        // 5. GROUP BY FLOOR GRID
        $roomsByFloor = $filteredRooms->groupBy('floor')->map(function ($floorRooms, $floor) {
            return [
                'floor'  => $floor,
                'total'  => $floorRooms->count(),
                'occ'    => $floorRooms->where('status', 'OCC')->count(),
                'od'     => $floorRooms->where('status', 'OD')->count(),
                'vc'     => $floorRooms->where('status', 'VC')->count(),
                'vd'     => $floorRooms->where('status', 'VD')->count(),
                'maint'  => $floorRooms->where('status', 'MAINT')->count(),
                'rooms'  => $floorRooms,
            ];
        });

        // UPDATED: system current date instead of hardcoded
        $housekeeperWorkloads = DB::table('hkroomassigns as HA')
            ->leftJoin('housekeeparmast as H', 'HA.code', '=', 'H.scode')
            ->select(
                DB::raw('COUNT(HA.roomno) as total_assigned'),
                DB::raw("SUM(CASE WHEN HA.status = 'cleaned' THEN 1 ELSE 0 END) as done_count"),
                'H.name as HouseKeeper'
            )
            ->where('HA.propertyid', $this->propertyid)
            ->where('HA.vdate', ncurdate())
            ->groupBy('H.name')
            ->get();

        $statusMap    = $this->roomStatusLabelMap();
        $uniqueFloors = $allRooms->pluck('floor')->unique()->filter();

        return view('property.housekeeping.roomstatusboard', compact(
            'totalRooms',
            'occupiedRooms',
            'occupiedDirty',
            'vacantDirty',
            'vacantClean',
            'outOfOrder',
            'maintenanceRooms',
            'inspectionPending',
            'roomsByFloor',
            'housekeeperWorkloads',
            'statusMap',
            'roomTypes',
            'uniqueFloors',
            'company',
            'occupancyRate'
        ));
    }
    /**
     * Group an already-fetched room collection by floor, with per-floor counts,
     * for the Room Status Board grid.
     */
    private function getRoomGridByFloor($rooms)
    {
        return $rooms->groupBy('floor')->map(function ($floorRooms, $floor) {
            return [
                'floor'  => $floor,
                'total'  => $floorRooms->count(),
                'occ'    => $floorRooms->whereIn('status', ['OCC', 'OD'])->count(),
                'vclean' => $floorRooms->where('status', 'VC')->count(),
                'rooms'  => $floorRooms,
            ];
        });
    }

    /**
     * Derive a single display status code for one room, combining
     * occupancy (roomocc/folioNo) with housekeeping state (room_stat).
     * room_stat: C = Clean, D = Dirty, O = Out of Order, I = Inspection pending
     */
    private function deriveRoomStatus($room)
    {
        $isOccupied = ($room->folioNo ?? 0) > 0;

        if ($room->room_stat === 'I') {
            return 'INSPECT';
        }

        if ($isOccupied) {
            return $room->room_stat === 'D' ? 'OD' : 'OCC';
        }

        return $room->room_stat === 'D' ? 'VD' : 'VC';
    }

    /**
     * Label + CSS class map for each room status code, used by the blade grid.
     */
    private function roomStatusLabelMap()
    {
        return [
            'OCC'     => ['label' => 'Occupied',          'class' => 'rsb-b-occupied'],
            'OD'      => ['label' => 'Occupied Dirty',     'class' => 'rsb-b-odirty'],
            'VC'      => ['label' => 'Vacant Clean',       'class' => 'rsb-b-vclean'],
            'VD'      => ['label' => 'Vacant Dirty',       'class' => 'rsb-b-vdirty'],
            'OOO'     => ['label' => 'Out of Order',       'class' => 'rsb-b-ooo'],
            'MAINT'   => ['label' => 'Maintenance',        'class' => 'rsb-b-maint'],
            'INSPECT' => ['label' => 'Inspection Pending', 'class' => 'rsb-b-inspect'],
        ];
    }

    // ─── Start Room Cleaning ─────────────────────────────────────────────────────

    public function startcleaning(Request $request)
    {
        $permission = revokeopen(151114);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $propertyId = $this->propertyid;
        $asOnDate   = $this->ncurdate;
        date_default_timezone_set('Asia/Kolkata');
        $startTime = date('h:i A');

        $cleaningTypes = DB::table('hkcleaningtype')
            ->where('propertyid', $propertyId)
            ->orderBy('sn')
            ->get();

        // Sirf aaj (ncurdate) ki dirty rooms jisme supervisor assign ho
        // $assignedRooms = DB::table('hkroomassigns')
        //     ->select('id', 'roomno', 'code', 'supervisor', 'ctype', 'esttime')
        //     ->where('propertyid', $propertyId)
        //     ->where('vdate', $asOnDate)
        //     ->where('status', 'dirty')
        //     ->where('supervisor', '!=', '')
        //     ->whereNotNull('supervisor')
        //     ->orderBy('roomno')
        //     ->get();

         $assignedRooms = DB::table('hkroomassigns')
            ->select('id', 'roomno', 'code', 'supervisor', 'ctype', 'esttime')
            ->where('propertyid', $propertyId)
            ->where('vdate', $asOnDate)
            ->where('status', 'dirty')
            ->where(function ($q) {
                $q->where('cleaningstatus', '')
                  ->orWhereNull('cleaningstatus');
            })
            ->where('supervisor', '!=', '')
            ->whereNotNull('supervisor')
            ->orderBy('roomno')
            ->get();



        $assignId = $request->query('id');
        $fromQr   = !empty($assignId);   // QR scan se aaya hai to id hoga
        $assign = $room = $guest = $nextArrival = $housekeeper = $supervisor = null;
        $currentStatusLabel = $priority = '';

        if ($assignId) {
            $assign = DB::table('hkroomassigns')
                ->where('id', $assignId)
                ->where('propertyid', $propertyId)
                ->first();

            if ($assign) {
                $roomno = $assign->roomno;

                $room = DB::table('room_mast as RM')
                    ->select('RM.rcode as roomno', 'RM.room_stat', 'FL.name as floorname', 'C.name as roomtype')
                    ->leftJoin('hkfloors as FL', function ($j) {
                        $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
                    })
                    ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
                    ->where('RM.rcode', $roomno)
                    ->where('RM.propertyid', $propertyId)
                    ->first();

                $guest = DB::table('roomocc as RO')
                    ->join('guestprof as GP', function ($j) use ($propertyId) {
                        $j->on('GP.guestcode', '=', 'RO.guestprof')
                            ->where('GP.propertyid', $propertyId);
                    })
                    ->select('GP.name as guestname', 'GP.vipStatus', 'RO.chkouttime', 'RO.folioNo')
                    ->where('RO.roomno', $roomno)
                    ->where('RO.propertyid', $propertyId)
                    ->whereNull('RO.chkoutdate')
                    ->orderByDesc('RO.sn')
                    ->first();

                $nextArrival = DB::table('grpbookingdetails')
                    ->where('RoomNo', $roomno)
                    ->where('Property_ID', $propertyId)
                    ->where('Cancel', 'N')
                    ->where('ContraDocId', '')
                    ->where('ArrDate', '>=', $asOnDate)
                    ->orderBy('ArrDate')
                    ->value('ArrTime');

                $housekeeper = DB::table('housekeeparmast')
                    ->where('propertyid', $propertyId)
                    ->where('scode', $assign->code)
                    ->first();

                $supervisor = DB::table('hksupervisor')
                    ->where('propertyid', $propertyId)
                    ->where('code', $assign->supervisor)
                    ->value('name');

                $isOccupied = $guest && ($guest->folioNo ?? 0) > 0;
                $statusMap  = ['C' => 'Vacant Clean', 'D' => 'Vacant Dirty', 'O' => 'Out of Order', 'I' => 'Inspection Pending'];
                $currentStatusLabel = $isOccupied
                    ? ($room->room_stat === 'D' ? 'Occupied Dirty' : 'Occupied Clean')
                    : ($statusMap[$room->room_stat ?? 'D'] ?? 'Vacant Dirty');
                $priority = $isOccupied ? 'Normal' : 'High';
            }
        }

        return view('property.housekeeping.startcleaning', compact(
            'cleaningTypes',
            'assignedRooms',
            'assignId',
            'assign',
            'room',
            'guest',
            'nextArrival',
            'housekeeper',
            'supervisor',
            'currentStatusLabel',
            'priority',
            'startTime',
            'asOnDate',
            'fromQr'
        ));
    }

    public function startcleaningfetch(Request $request)
    {
        try {
            $propertyId = $this->propertyid;
            $assignId   = $request->input('assign_id');

            if (!$assignId) {
                return response()->json(['success' => false, 'message' => 'assign_id required']);
            }

            $assign = DB::table('hkroomassigns')
                ->where('id', $assignId)
                ->where('propertyid', $propertyId)
                ->first();

            if (!$assign) {
                return response()->json(['success' => false, 'message' => 'Assignment not found']);
            }

            $asOnDate = $this->ncurdate;
            $roomno   = $assign->roomno;

            $room = DB::table('room_mast as RM')
                ->select('RM.rcode as roomno', 'RM.room_stat', 'FL.name as floorname', 'C.name as roomtype')
                ->leftJoin('hkfloors as FL', function ($j) {
                    $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
                })
                ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
                ->where('RM.rcode', $roomno)
                ->where('RM.propertyid', $propertyId)
                ->first();

            $guest = DB::table('roomocc as RO')
                ->join('guestprof as GP', function ($j) use ($propertyId) {
                    $j->on('GP.guestcode', '=', 'RO.guestprof')
                        ->where('GP.propertyid', $propertyId);
                })
                ->select('GP.name as guestname', 'GP.vipStatus', 'RO.chkouttime', 'RO.folioNo')
                ->where('RO.roomno', $roomno)
                ->where('RO.propertyid', $propertyId)
                ->whereNull('RO.chkoutdate')
                ->orderByDesc('RO.sn')
                ->first();

            $nextArrival = DB::table('grpbookingdetails')
                ->where('RoomNo', $roomno)
                ->where('Property_ID', $propertyId)
                ->where('Cancel', 'N')
                ->where('ContraDocId', '')
                ->where('ArrDate', '>=', $asOnDate)
                ->orderBy('ArrDate')
                ->value('ArrTime');

            $housekeeper = DB::table('housekeeparmast')
                ->where('propertyid', $propertyId)
                ->where('scode', $assign->code)
                ->first();

            $supervisor = DB::table('hksupervisor')
                ->where('propertyid', $propertyId)
                ->where('code', $assign->supervisor)
                ->value('name');

            $isOccupied = $guest && ($guest->folioNo ?? 0) > 0;
            $statusMap  = ['C' => 'Vacant Clean', 'D' => 'Vacant Dirty', 'O' => 'Out of Order', 'I' => 'Inspection Pending'];
            $statusLabel = $isOccupied
                ? ($room->room_stat === 'D' ? 'Occupied Dirty' : 'Occupied Clean')
                : ($statusMap[$room->room_stat ?? 'D'] ?? 'Vacant Dirty');

            date_default_timezone_set('Asia/Kolkata');

            return response()->json([
                'success'      => true,
                'assign_id'    => $assign->id,
                'assign_no'    => 'HKA/' . $assign->propertyid . $assign->id,
                'roomno'       => $room->roomno        ?? '',
                'floorname'    => $room->floorname      ?? '',
                'roomtype'     => $room->roomtype       ?? '',
                'status_label' => $statusLabel,
                'priority'     => $isOccupied ? 'Normal' : 'High',
                'esttime'      => $assign->esttime      ?? '',
                'ctype'        => $assign->ctype        ?? '',
                'guestname'    => $guest->guestname     ?? '',
                'vipStatus'    => $guest->vipStatus     ?? 'N',
                'checkouttime' => ($guest && $guest->chkouttime) ? date('h:i A', strtotime($guest->chkouttime)) : '',
                'nextarrival'  => $nextArrival ? date('h:i A', strtotime($nextArrival)) : '',
                'hkname'       => $housekeeper->name    ?? '',
                'hkcode'       => $assign->code         ?? '',
                'supervisor'   => $supervisor           ?? '',
                'starttime'    => date('h:i A'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function submitstartcleaning(Request $request)
    {
        $permission = revokeopen(151114) ?? revokeopen(151115);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;

            $assign = DB::table('hkroomassigns')
                ->where('id', $request->assign_id)
                ->where('propertyid', $propertyId)
                ->first();

            if (!$assign) {
                return response()->json(['success' => false, 'message' => 'Room select karein!'], 422);
            }

            // ── Duplicate In Progress guard ─────────────────────────────────────
            // Room ka LATEST cleaning record dekho (status ignore karke):
            // - latest 'In Progress' hai → block karo (duplicate na bane)
            // - latest 'Completed' hai → purane stale 'In Progress' records cleanup
            //   karke nayi cleaning start hone do (warna dropdown me purana dikhega)
            $latestCleaning = DB::table('hkcleaninghdr')
                ->where('propertyid', $propertyId)
                ->where('roommo', $assign->roomno)
                ->where('cleaningdate', $this->ncurdate)
                ->orderByDesc('cleaningid')
                ->first();

            if ($latestCleaning && $latestCleaning->cleaningstatus === 'In Progress') {
                return response()->json([
                    'success' => false,
                    'message' => 'Is room ki cleaning already start ho chuki hai! Cleaning No: ' . $latestCleaning->cleaningno . ' — Room Cleaning Entry se complete karein.',
                ], 422);
            }

            // Stale 'In Progress' records (latest Completed hone ke bawajood bache hue) cleanup
            if ($latestCleaning && $latestCleaning->cleaningstatus === 'Completed') {
                DB::table('hkcleaninghdr')
                    ->where('propertyid', $propertyId)
                    ->where('roommo', $assign->roomno)
                    ->where('cleaningdate', $this->ncurdate)
                    ->where('cleaningstatus', 'In Progress')
                    ->update(['cleaningstatus' => 'Completed']);
            }

            DB::beginTransaction();

            $lastNo     = DB::table('hkcleaninghdr')
                ->where('propertyid', $propertyId)
                ->max(DB::raw('CAST(cleaningno AS UNSIGNED)'));
            $cleaningno = str_pad(($lastNo ? $lastNo + 1 : 1), 6, '0', STR_PAD_LEFT);

            $lastCleaningId = DB::table('hkcleaninghdr')
                ->where('propertyid', $propertyId)
                ->max('cleaningid');
            $cleaningid = ($lastCleaningId ? $lastCleaningId + 1 : 1);

            date_default_timezone_set('Asia/Kolkata');
            $now = date('Y-m-d H:i:s');

            $beforePhotoPath = null;
            if ($request->hasFile('before_photo')) {
                $file      = $request->file('before_photo');
                $filename  = 'hkbefore_' . $propertyId . '_' . time() . '.' . $file->getClientOriginalExtension();
                $uploadDir = public_path('uploads/hkphotos');
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                $file->move($uploadDir, $filename);
                $beforePhotoPath = 'uploads/hkphotos/' . $filename;
            }

            $roomStat = DB::table('room_mast')
                ->where('rcode', $assign->roomno)
                ->where('propertyid', $propertyId)
                ->value('room_stat');

            $folioNo = DB::table('roomocc')
                ->where('roomno', $assign->roomno)
                ->where('propertyid', $propertyId)
                ->whereNull('chkoutdate')
                ->orderByDesc('sn')
                ->value('folioNo');

            DB::table('hkcleaninghdr')->insert([
                'cleaningid'         => $cleaningid,
                'propertyid'         => $propertyId,
                'cleaningno'         => $cleaningno,
                'cleaningdate'       => $this->ncurdate,
                'roommo'             => $assign->roomno,
                'roomstatusbefore'   => $roomStat ?? 'D',
                'roomstatusafter'    => null,
                'cleaningtype'       => $request->cleaning_type ?? null,
                'housekeeperid'      => $assign->code,
                'supervisorid'       => $assign->supervisor ?? null,
                'starttime'          => $now,
                'endtime'            => null,
                'priority'           => ($folioNo > 0) ? 'Normal' : 'High',
                'cleaningstatus'     => 'In Progress',
                'inspectionrequired' => 'Y',
                'beforephoto'        => $beforePhotoPath,
                'assno'              => $assign->assno ?? null,
                'u_name'             => Auth::user()->u_name,
                'u_entdt'            => $now,
                'u_ae'               => 'a',
            ]);

            // ── hkroomassigns me cleaningstatus update karo ───────────────────
            DB::table('hkroomassigns')
                ->where('id', $assign->id)
                ->where('propertyid', $propertyId)
                ->update(['cleaningstatus' => 'In Progress']);

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Cleaning started! No: ' . $cleaningno, 'cleaningid' => $cleaningid]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // --- Room Cleaning Entry ---

    public function roomcleaningentry(Request $request)
    {
        $permission = revokeopen(151115);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $propertyId        = $this->propertyid;
        $asOnDate          = $this->ncurdate;   // ncur date
        $selectedCleaningId = $request->query('cleaningid');
        $fromQr            = !empty($selectedCleaningId);  // QR scan se aaya

        // Sirf aaj (ncur) ki In Progress cleaning dikhao — har room ek baar.
        // IMPORTANT: subquery me status filter NAHI lagana — room ka OVERALL latest
        // cleaningid hona chahiye. Agar room ka latest record Completed hai to woh
        // dropdown se hat jayega, chahe purana stale 'In Progress' record ho hi kyun na.
        $allRooms = DB::table('hkcleaninghdr as H')
            ->select('H.cleaningid', 'H.roommo as rcode', 'H.cleaningno', 'H.cleaningdate', 'H.cleaningstatus')
            ->where('H.propertyid', $propertyId)
            ->where('H.cleaningdate', $asOnDate)
            ->where('H.cleaningstatus', 'In Progress')
            ->whereRaw('H.cleaningid = (
                SELECT MAX(H2.cleaningid)
                FROM hkcleaninghdr H2
                WHERE H2.propertyid   = H.propertyid
                  AND H2.roommo       = H.roommo
                  AND H2.cleaningdate = H.cleaningdate
            )')
            ->orderBy('H.roommo')
            ->get();

        $checklistItems = DB::table('hkchecklistmast')
            ->where('propertyid', $propertyId)
            ->orderBy('sno')
            ->get();

        // amenities: itemmast join for item names
        $amenities = DB::table('hkamentiesmaster as A')
            ->select(
                'A.sn',
                'A.item as itemcode',
                'A.type',
                'A.srno',
                DB::raw("COALESCE(I.Name, A.item) as itemname")
            )
            ->leftJoin('itemmast as I', DB::raw('CAST(I.Code AS CHAR)'), '=', DB::raw('CAST(A.item AS CHAR)'))
            ->where('A.propertyid', $propertyId)
            ->orderBy('A.srno')
            ->get()
            ->groupBy('type');

        $cleaningTypes = DB::table('hkcleaningtype')
            ->where('propertyid', $propertyId)
            ->orderBy('sn')
            ->get();

        return view('property.housekeeping.roomcleaningentry', compact(
            'allRooms',
            'checklistItems',
            'amenities',
            'cleaningTypes',
            'asOnDate',
            'fromQr',
            'selectedCleaningId'
        ));
    }

    public function fetchcleaningentry(Request $request)
    {
        try {
            $propertyId = $this->propertyid;
            $cleaningId = $request->input('cleaningid');

            if (!$cleaningId) {
                return response()->json(['success' => false, 'message' => 'cleaningid required']);
            }

            $hdr = DB::table('hkcleaninghdr')
                ->where('cleaningid', $cleaningId)
                ->where('propertyid', $propertyId)
                ->first();

            if (!$hdr) {
                return response()->json(['success' => false, 'message' => 'Record not found']);
            }

            $roomno = $hdr->roommo;

            $room = DB::table('room_mast as RM')
                ->select(
                    'RM.rcode',
                    'RM.room_stat',
                    DB::raw("COALESCE(FL.name, '--') as floorname"),
                    DB::raw("COALESCE(C.name,  '--') as roomtype")
                )
                ->leftJoin('hkfloors as FL', function ($j) {
                    $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
                })
                ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
                ->where('RM.rcode', $roomno)
                ->where('RM.propertyid', $propertyId)
                ->first();

            $guest = DB::table('roomocc as RO')
                ->join('guestprof as GP', function ($j) use ($propertyId) {
                    $j->on('GP.guestcode', '=', 'RO.guestprof')->where('GP.propertyid', $propertyId);
                })
                ->select('GP.name as guestname', 'GP.vipStatus', 'RO.folioNo')
                ->where('RO.roomno', $roomno)->where('RO.propertyid', $propertyId)
                ->whereNull('RO.chkoutdate')->orderByDesc('RO.sn')->first();

            $isOccupied      = $guest && ($guest->folioNo ?? 0) > 0;
            $statusMap       = ['C' => 'Vacant Clean', 'D' => 'Vacant Dirty', 'O' => 'Out of Order', 'I' => 'Inspection Pending'];
            $roomStatusLabel = $isOccupied
                ? ((($room->room_stat ?? 'D') === 'D') ? 'Occupied Dirty' : 'Occupied Clean')
                : ($statusMap[$room->room_stat ?? 'D'] ?? 'Vacant Dirty');

            $hkname  = DB::table('housekeeparmast')->where('propertyid', $propertyId)->where('scode', $hdr->housekeeperid)->value('name');
            $supname = DB::table('hksupervisor')->where('propertyid', $propertyId)->where('code', $hdr->supervisorid)->value('name');

            // Fetch saved footer rows from hkcleaningftr
            $ftrRows = DB::table('hkcleaningftr')
                ->where('propertyid', $propertyId)
                ->where('assid', $cleaningId)
                ->orderBy('sno')
                ->get();

            // Separate checklist (complete=1) sn values — matched by item name
            $allChks = DB::table('hkchecklistmast')
                ->where('propertyid', $propertyId)
                ->orderBy('sno')
                ->get();
            $checkedItems = [];
            foreach ($ftrRows->where('ctype', 'checklist') as $row) {
                if ($row->complete) {
                    // Match by item code (stored in hkcleaningftr.item)
                    foreach ($allChks as $ci) {
                        if (mb_strtolower(trim($ci->code)) === mb_strtolower(trim($row->item ?? ''))) {
                            $checkedItems[] = (string)$ci->sn;
                        }
                    }
                }
            }

            // Amenity rows (ctype != 'checklist'), return item + type + qty
            $amenityData = [];
            foreach ($ftrRows->whereNotIn('ctype', ['checklist']) as $row) {
                if ($row->qty > 0) {
                    $amenityData[] = [
                        'itemcode' => $row->item,
                        'type' => $row->ctype,
                        'qty'  => $row->qty,
                    ];
                }
            }

            date_default_timezone_set('Asia/Kolkata');

            return response()->json([
                'success'        => true,
                'cleaningid'     => $hdr->cleaningid,
                'cleaningno'     => $hdr->cleaningno    ?? '--',
                'cleaningdate'   => $hdr->cleaningdate ? \Carbon\Carbon::parse($hdr->cleaningdate)->format('d-M-Y') : '--',
                'cleaningstatus' => $hdr->cleaningstatus ?? '',
                'roomno'         => $roomno,
                'floorname'      => $room->floorname    ?? '--',
                'roomtype'       => $room->roomtype     ?? '--',
                'roomstatus'     => $roomStatusLabel,
                'priority'       => $hdr->priority      ?? 'High',
                'cleaningtype'   => $hdr->cleaningtype  ?? '',
                'hkname'         => $hkname             ?? '--',
                'supname'        => $supname            ?? '--',
                'starttime'      => $hdr->starttime ? date('h:i A', strtotime($hdr->starttime)) : '--',
                'endtime'        => $hdr->endtime   ? date('h:i A', strtotime($hdr->endtime))   : '--',
                'totalminutes'   => $hdr->totalminutes  ?? null,
                'inspectionreq'  => $hdr->inspectionrequired ?? 'Y',
                'remarks'        => $hdr->remarks       ?? '',
                'beforephoto'    => $hdr->beforephoto   ? asset($hdr->beforephoto) : null,
                'afterphoto'     => $hdr->afterphoto    ? asset($hdr->afterphoto)  : null,
                'guestname'      => $guest->guestname   ?? '',
                'vipstatus'      => $guest->vipStatus   ?? 'N',
                'checkeditems'   => $checkedItems,
                'amenitydata'    => $amenityData,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function submitcleaningentry(Request $request)
    {
        $permission = revokeopen(151115) ?? revokeopen(151112);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;
            $cleaningId = $request->input('cleaning_id');
            if (!$cleaningId) return response()->json(['success' => false, 'message' => 'Cleaning record select karein!']);

            $hdr = DB::table('hkcleaninghdr')
                ->where('cleaningid', $cleaningId)->where('propertyid', $propertyId)->first();
            if (!$hdr) return response()->json(['success' => false, 'message' => 'Record not found!']);

            date_default_timezone_set('Asia/Kolkata');
            $now   = date('Y-m-d H:i:s');
            $date  = $this->ncurdate;   // ncurdate use karo — server date nahi (stock/ftr me sahi date jayegi)
            $time  = date('H:i:s');
            $uname = Auth::user()->u_name;

            $uploadDir = public_path('uploads/hkphotos');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $beforePhotoPath = $hdr->beforephoto;
            if ($request->hasFile('before_photo')) {
                $f  = $request->file('before_photo');
                $fn = 'hkbefore_' . $propertyId . '_' . $cleaningId . '_' . time() . '.' . $f->getClientOriginalExtension();
                $f->move($uploadDir, $fn);
                $beforePhotoPath = 'uploads/hkphotos/' . $fn;
                DB::table('hkcleaninghdr')->where('cleaningid', $cleaningId)->where('propertyid', $propertyId)
                    ->update(['beforephoto' => $beforePhotoPath]);
            }

            $afterPhotoPath = $hdr->afterphoto;
            if ($request->hasFile('after_photo')) {
                $f  = $request->file('after_photo');
                $fn = 'hkafter_' . $propertyId . '_' . $cleaningId . '_' . time() . '.' . $f->getClientOriginalExtension();
                $f->move($uploadDir, $fn);
                $afterPhotoPath = 'uploads/hkphotos/' . $fn;
            }

            DB::beginTransaction();

            DB::table('hkcleaningftr')->where('propertyid', $propertyId)->where('assid', $cleaningId)->delete();

            $rows = [];
            $sno = 1;

            $checklist = json_decode($request->input('checklist', '[]'), true) ?: [];
            $allChks   = DB::table('hkchecklistmast')->where('propertyid', $propertyId)->orderBy('sno')->get();
            foreach ($allChks as $ci) {
                $rows[] = [
                    'propertyid' => $propertyId,
                    'assid' => $cleaningId,
                    'sno' => $sno++,
                    'date' => $date,
                    'item' => $ci->code,
                    'ctype' => 'checklist',
                    'complete' => in_array((string)$ci->sn, array_map('strval', $checklist)) ? 1 : 0,
                    'qty' => null,
                    'time' => $time,
                    'u_name' => $uname,
                    'u_entdt' => $now,
                    'u_ae' => 'a',
                ];
            }

            $amenities = json_decode($request->input('amenities', '[]'), true) ?: [];
            foreach ($amenities as $am) {
                $rawQty = str_replace(',', '.', (string)($am['qty'] ?? ''));
                $qty    = is_numeric($rawQty) ? (float)$rawQty : null;
                if (!$qty || $qty <= 0) continue; // skip zero/invalid amenity rows
                $rows[] = [
                    'propertyid' => $propertyId,
                    'assid' => $cleaningId,
                    'sno' => $sno++,
                    'date' => $date,
                    'item' => $am['itemcode'] ?? '',
                    'ctype' => $am['type'] ?? '',
                    'complete' => 1,
                    'qty' => $qty,
                    'time' => $time,
                    'u_name' => $uname,
                    'u_entdt' => $now,
                    'u_ae' => 'a',
                ];
            }

            if (!empty($rows)) DB::table('hkcleaningftr')->insert($rows);

            $isComplete     = $request->input('action') === 'complete';
            $cleaningStatus = $isComplete ? 'Completed' : 'In Progress';
            $endTime = $totalMinutes = null;
            if ($isComplete) {
                $endTime      = $now;
                $totalMinutes = (int) round((strtotime($endTime) - strtotime($hdr->starttime ?? $now)) / 60);
            }

            $updateHdr = [
                'remarks'            => $request->input('remarks', ''),
                'inspectionrequired' => $request->input('inspection_required', 'N'),
                'afterphoto'         => $afterPhotoPath,
                'priority'           => $request->input('priority', $hdr->priority),
                'cleaningstatus'     => $cleaningStatus,
                'u_name'             => $uname,
                'u_ae' => 'e',
            ];
            if ($isComplete) {
                $updateHdr['endtime']         = $endTime;
                $updateHdr['totalminutes']    = $totalMinutes;
                $updateHdr['roomstatusafter'] = 'C';
            }
            if ($isComplete) {
                // Room ke saare 'In Progress' records complete karo —
                // duplicate/stale records bhi clean ho jayenge taaki room
                // dropdown se hat jaye aur table me 2 baar na dikhe.
                DB::table('hkcleaninghdr')
                    ->where('propertyid', $propertyId)
                    ->where('roommo', $hdr->roommo)
                    ->where('cleaningdate', $hdr->cleaningdate)
                    ->where('cleaningstatus', 'In Progress')
                    ->update($updateHdr);
            } else {
                DB::table('hkcleaninghdr')->where('cleaningid', $cleaningId)->where('propertyid', $propertyId)->update($updateHdr);
            }

            if ($isComplete) {
                DB::table('room_mast')
                    ->where('propertyid', $propertyId)
                    ->where('rcode', $hdr->roommo)
                    ->where('type', 'RO')
                    ->update(['room_stat' => 'C', 'u_updatedt' => $now]);

                // ── hkroomassigns me cleaningstatus aur status update karo ──
                // NOTE: assno se match NAHI karna — assno har housekeeper ke saare
                // rooms ke liye common hota hai aur daily regenerate hota hai.
                // Isliye room ke ACTIVE assignment (cleaningstatus='In Progress')
                // ko roomno se target karo.
                DB::table('hkroomassigns')
                    ->where('propertyid', $propertyId)
                    ->where('roomno', $hdr->roommo)
                    ->where('cleaningstatus', 'In Progress')
                    ->update([
                        'cleaningstatus' => 'Completed',
                        'status'         => 'clean',
                    ]);
            }

            // ── HKISS Stock Voucher — amenities/chemical jo use hue unki stock entry ──
            // Sirf tab jab amenities ho aur qty > 0
            $amenitiesForStock = array_filter($amenities, function($am) {
                $rawQty = str_replace(',', '.', (string)($am['qty'] ?? ''));
                $type   = $am['type'] ?? '';
                // Sirf Amenities aur Chemical — Linen stock me nahi jayega
                return is_numeric($rawQty) && (float)$rawQty > 0
                    && in_array($type, ['Amenities', 'Chemical']);
            });

            if (!empty($amenitiesForStock)) {
                $vtype = 'HKISS';
                $hkGodown = 'HK' . $propertyId;

                // VoucherPrefix se vno aur vprefix fetch karo
                $chkvpf = \App\Models\VoucherPrefix::where('propertyid', $propertyId)
                    ->where('v_type', $vtype)
                    ->whereDate('date_from', '<=', $date)
                    ->whereDate('date_to', '>=', $date)
                    ->first();

                if ($chkvpf) {
                    $vno     = \App\Models\Stock::where('propertyid', $propertyId)->where('vtype', $vtype)->max('vno') + 1;
                    $vprefix = $chkvpf->prefix;
                    $docid   = $propertyId . $vtype . '‎ ‎ ' . $vprefix . '‎ ‎ ‎ ‎ ' . $vno;

                    $stockRows = [];
                    $snoStock  = 1;

                    foreach ($amenitiesForStock as $am) {
                        $rawQty  = str_replace(',', '.', (string)($am['qty'] ?? ''));
                        $qty     = (float)$rawQty;
                        $icode   = $am['itemcode'] ?? '';

                        // Item master se rate, unit, convratio, restcode fetch karo
                        $itemmast = DB::table('itemmast')
                            ->where('Code', $icode)
                            ->where('Property_ID', $propertyId)
                            ->first();

                        $rate      = $itemmast->PurchRate  ?? 0;
                        $unit      = $itemmast->Unit       ?? '';
                        $convratio = $itemmast->ConvRatio  ?? 1;
                        $restcode  = $itemmast->RestCode   ?? '';

                        $stockRows[] = [
                            'propertyid'   => $propertyId,
                            'docid'        => $docid,
                            'sno'          => $snoStock++,
                            'vprefix'      => $vprefix,
                            'vtype'        => $vtype,
                            'vno'          => $vno,
                            'vdate'        => $date,
                            'roomno'       => $hdr->roommo,
                            'item'         => $icode,
                            'contradocid'  => '',
                            'contrasno'    => 0,
                            'departcode'   => $hkGodown,
                            'godowncode'   => $hkGodown,
                            'rate'         => $rate,
                            'recdqty'      => 0,
                            'accqty'       => 0,
                            'qtyiss'       => $qty,
                            'issqty'       => $qty,
                            'qtyrec'       => 0,
                            'recdunit'     => $unit,
                            'issueunit'    => $unit,
                            'unit'         => $unit,
                            'amount'       => $qty * $rate,
                            'convratio'    => $convratio,
                            'total'        => 0,
                            'itemrestcode' => $restcode,
                            'remarks'      => $request->input('remarks', ''),
                            'u_name'       => $uname,
                            'u_entdt'      => $now,
                            'u_ae'         => 'a',
                            'delflag'      => 'N',
                        ];
                    }

                    if (!empty($stockRows)) {
                        \App\Models\Stock::insert($stockRows);

                        // VoucherPrefix increment karo
                        \App\Models\VoucherPrefix::where('propertyid', $propertyId)
                            ->where('v_type', $vtype)
                            ->where('prefix', $vprefix)
                            ->increment('start_srl_no');
                    }
                }
                // Note: agar HKISS VoucherPrefix nahi mila to stock entry skip hogi,
                // cleaning complete hogi normally
            }

            DB::commit();

            return response()->json([
                'success'   => true,
                'message'   => $isComplete ? 'Cleaning Completed! Room status updated to Clean.' : 'Entry saved successfully!',
                'completed' => $isComplete,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ─── Cleaning FTR List (for table below form) ───────────────────────────────

    public function fetchcleaningftrlist(Request $request)
    {
        try {
            $propertyId = $this->propertyid;

            // 1 cleaning = 1 row — sirf aaj (ncur) ki records, har room ka LATEST
            $rows = DB::table('hkcleaninghdr as H')
                ->select(
                    'H.cleaningid',
                    'H.cleaningno',
                    'H.roommo',
                    'H.cleaningdate',
                    'H.cleaningstatus'
                )
                ->where('H.propertyid', $propertyId)
                ->where('H.cleaningdate', $this->ncurdate)
                ->whereRaw('H.cleaningid = (
                    SELECT MAX(H2.cleaningid)
                    FROM hkcleaninghdr H2
                    WHERE H2.propertyid   = H.propertyid
                      AND H2.roommo       = H.roommo
                      AND H2.cleaningdate = H.cleaningdate
                )')
                ->orderByDesc('H.cleaningid')
                ->get();

            $data = $rows->map(function ($r) {
                return [
                    'cleaningno'     => $r->cleaningno     ?? '--',
                    'roommo'         => $r->roommo          ?? '--',
                    'date'           => $r->cleaningdate
                                        ? \Carbon\Carbon::parse($r->cleaningdate)->format('d-M-Y')
                                        : '--',
                    'cleaningstatus' => $r->cleaningstatus  ?? '--',
                ];
            });

            return response()->json(['success' => true, 'data' => $data]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ─── Damage Report ───────────────────────────────────────────────────────────

    /**
     * Store a new damage report entry into hkdamage.
     */
    public function storedamagereport(Request $request)
    {
        $permission = revokeopen(151118);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;

            $request->validate([
                'roomno'      => 'required|string|max:20',
                'date'        => 'required|date',
                'damagetype'  => 'required|string|max:50',
                'item'        => 'required|string|max:100',
                'description' => 'nullable|string',
            ]);

            date_default_timezone_set('Asia/Kolkata');
            $now = date('Y-m-d H:i:s');

            // Auto-increment damageid per property
            $lastId = DB::table('hkdamage')
                ->where('propertyid', $propertyId)
                ->max('damageid');
            $damageid = ($lastId ?? 0) + 1;

            DB::table('hkdamage')->insert([
                'propertyid'  => $propertyId,
                'damageid'    => $damageid,
                'roomno'      => strtoupper(trim($request->roomno)),
                'date'        => $request->date,
                'damagetype'  => $request->damagetype,
                'item'        => trim($request->item),
                'description' => trim($request->description ?? ''),
                'status'      => 'Pending',
                'u_name'      => Auth::user()->u_name ?? Auth::user()->name ?? 'system',
                'u_entdt'     => $now,
                'u_ae'        => 'a',
            ]);

            return response()->json(['success' => true, 'message' => 'Damage report saved successfully! ID: DR/' . $propertyId . '/' . $damageid]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['success' => false, 'message' => implode(' ', Arr::flatten($ve->errors()))], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Fetch all damage reports for the property (for DataTable refresh).
     */
    public function fetchdamagereports(Request $request)
    {
        try {
            $propertyId = $this->propertyid;

            $rows = DB::table('hkdamage')
                ->where('propertyid', $propertyId)
                ->orderByDesc('sn')
                ->get();

            return response()->json(['success' => true, 'data' => $rows]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ─── Damage Report Page ───────────────────────────────────────────────────

    /**
     * Show Damage Report page.
     */
    /**
     * Damage Report page — full list of damage reports with filters (Item column included).
     */
    public function damagereport(Request $request)
    {
        $company = Companyreg::where('propertyid', $this->propertyid)->first();
        $statename = States::where('propertyid', $this->propertyid)
            ->where('state_code', $company->state_code)
            ->value('name');

        return view('property.housekeeping.damagereport', [
            'ncurdate'  => $this->ncurdate,
            'company'   => $company,
            'statename' => $statename,
        ]);
    }

    /**
     * Fetch filtered damage reports for the report page DataTable.
     */
    public function fetchdamagereportdata(Request $request)
    {
        try {
            $propertyId = $this->propertyid;
            $fromdate   = $request->input('fromdate');
            $todate     = $request->input('todate');
            $roomno     = trim((string) $request->input('roomno'));
            $status     = trim((string) $request->input('status'));

            $query = DB::table('hkdamage')->where('propertyid', $propertyId);

            if ($fromdate) {
                $query->whereDate('date', '>=', $fromdate);
            }
            if ($todate) {
                $query->whereDate('date', '<=', $todate);
            }
            if ($roomno !== '') {
                $query->where('roomno', 'LIKE', '%' . $roomno . '%');
            }
            if ($status !== '' && $status !== 'All') {
                $query->where('status', $status);
            }

            $rows = (clone $query)->orderByDesc('sn')->get();

            $counts = (clone $query)
                ->select('status', DB::raw('COUNT(*) as cnt'))
                ->groupBy('status')
                ->pluck('cnt', 'status')
                ->all();

            return response()->json([
                'success' => true,
                'data'    => $rows,
                'counts'  => [
                    'total'      => $rows->count(),
                    'pending'    => $counts['Pending']     ?? 0,
                    'inprogress' => $counts['In Progress'] ?? 0,
                    'resolved'   => $counts['Resolved']    ?? 0,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update damage report status (Pending / In Progress / Resolved).
     */
    public function updatedamagereport(Request $request)
    {
        $permission = revokeopen(151216);
        if (is_null($permission) || $permission->edit == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;

            $request->validate([
                'sn'     => 'required|integer',
                'status' => 'required|string|in:Pending,In Progress,Resolved',
            ]);

            date_default_timezone_set('Asia/Kolkata');

            $record = DB::table('hkdamage')
                ->where('propertyid', $propertyId)
                ->where('sn', $request->sn)
                ->first();

            if (! $record) {
                return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
            }

            DB::table('hkdamage')
                ->where('propertyid', $propertyId)
                ->where('sn', $request->sn)
                ->update([
                    'status' => $request->status,
                    'u_name' => Auth::user()->u_name ?? Auth::user()->name ?? 'system',
                    'u_ae'   => 'e',
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated to ' . $request->status . '.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['success' => false, 'message' => implode(' ', Arr::flatten($ve->errors()))], 422);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store Out of Order room blockout from damage report flow.
     */
    public function storeoutofororder(Request $request)
    {
        $permission = revokeopen(151118);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;

            $request->validate([
                'roomno'     => 'required|string|max:20',
                'ooo_type'   => 'required|string|max:50',
                'from_date'  => 'required|date',
                'to_date'    => 'required|date',
            ]);

            date_default_timezone_set('Asia/Kolkata');
            $now     = date('Y-m-d H:i:s');
            $roomno  = strtoupper(trim($request->roomno));
            $oooType = $request->ooo_type;
            // reasons comes from damage report description; fallback to ooo_type
            $reasons = trim($request->reasons ?? $oooType);

            // FINANCIAL SAFETY: close old blockout + insert new + room_mast + audit — one unit.
            DB::beginTransaction();

            // Close any existing open OOO blockout for this room
            RoomBlockout::where('propertyid', $propertyId)
                ->where('roomcode', $roomno)
                ->where('type', 'O')
                ->whereNull('cleardate')
                ->update([
                    'cleardate'  => $this->ncurdate,
                    'cleartime'  => date('H:i:s'),
                    'u_updatedt' => $now,
                ]);

            // Create new blockout record
            $rblkout               = new RoomBlockout;
            $rblkout->propertyid   = $propertyId;
            $rblkout->roomcode     = $roomno;
            $rblkout->block        = $oooType;      // Out of Order / Maintenance
            $rblkout->reasons      = $reasons;       // damage report description
            $rblkout->fromdate     = $request->from_date;
            $rblkout->todate       = $request->to_date;
            $rblkout->type         = 'O';
            $rblkout->u_name       = Auth::user()->u_name ?? Auth::user()->name ?? 'system';
            $rblkout->u_entdt      = $now;
            $rblkout->u_updatedt   = null;
            $rblkout->u_ae         = 'a';
            $rblkout->vtime        = date('H:i:s');
            $rblkout->guestname    = '';
            $rblkout->mobileno     = '';
            $rblkout->save();

            // Update room_mast status to Out of Order
            RoomMast::where('propertyid', $propertyId)
                ->where('inclcount', 'Y')
                ->where('type', 'RO')
                ->where('rcode', $roomno)
                ->update(['room_stat' => 'O', 'u_updatedt' => $now]);

            // Audit history: mark Out of Order status change (damage-report flow)
            $roomclean = new RoomClean;
            $roomclean->propertyid = $propertyId;
            $roomclean->hosuekeeper = '';
            $roomclean->roomno = $roomno;
            $roomclean->remarks = mb_substr('OOO via damage report: ' . $reasons, 0, 50);
            $roomclean->type = 'O';
            $roomclean->u_entdt = $now;
            $roomclean->u_updatedt = null;
            $roomclean->u_ae = 'a';
            $roomclean->save();
            \App\Helpers\MasterDataCache::flushAvailability($propertyId);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Room ' . $roomno . ' marked as Out of Order (' . $oooType . ').'
            ]);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => implode(' ', Arr::flatten($ve->errors()))], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }



    
    // ─── HK QR Code Generator ────────────────────────────────────────────────────

    public function hkQrGenerate(Request $request)
    {
        $rcode = $request->rcode;

        if (empty($rcode)) {
            return response()->json(['success' => false, 'message' => 'Room code required'], 422);
        }

        try {
            $compdata = companydata();

            $url     = url("/hk-scan/{$compdata->propertyid}/{$rcode}");
            $toptext = 'Room No ' . $rcode;

            // Logo path
            $logo = null;
            if (!empty($compdata->logo)) {
                $path = storage_path('app/public/admin/property_logo/' . $compdata->logo);
                if (file_exists($path)) {
                    $logo = $path;
                }
            }
            if (!$logo) {
                $fallback = public_path('assets/img/logo.png');
                $logo = file_exists($fallback) ? $fallback : null;
            }

            $builder = Builder::create()
                ->writer(new PngWriter())
                ->data($url)
                ->encoding(new Encoding('UTF-8'))
                ->errorCorrectionLevel(ErrorCorrectionLevel::High)
                ->size(512)
                ->margin(10)
                ->roundBlockSizeMode(RoundBlockSizeMode::Margin);

            if ($logo && file_exists($logo)) {
                $builder
                    ->logoPath($logo)
                    ->logoResizeToWidth(100)
                    ->logoPunchoutBackground(true);
            }

            $result  = $builder->build();
            $qrImage = imagecreatefromstring($result->getString());
            $qrWidth  = imagesx($qrImage);
            $qrHeight = imagesy($qrImage);

            $fontSize   = 20;
            $fontPath   = realpath(__DIR__ . '/../../../vendor/endroid/qr-code/assets/noto_sans.otf');
            $textBox    = imagettfbbox($fontSize, 0, $fontPath, $toptext);
            $textWidth  = $textBox[2] - $textBox[0];
            $textHeight = $textBox[1] - $textBox[7];
            $padding    = 15;
            $headerHeight = $textHeight + $padding * 2;

            $finalWidth  = max($qrWidth, $textWidth + 20);
            $finalHeight = $qrHeight + $headerHeight;
            $finalImage  = imagecreatetruecolor($finalWidth, $finalHeight);

            $white = imagecolorallocate($finalImage, 255, 255, 255);
            $black = imagecolorallocate($finalImage, 0, 0, 0);
            imagefill($finalImage, 0, 0, $white);

            $textX = intval(($finalWidth - $textWidth) / 2);
            $textY = $padding + $textHeight;
            imagettftext($finalImage, $fontSize, 0, $textX, $textY, $black, $fontPath, $toptext);

            $qrX = intval(($finalWidth - $qrWidth) / 2);
            imagecopy($finalImage, $qrImage, $qrX, $headerHeight, 0, 0, $qrWidth, $qrHeight);
            imagedestroy($qrImage);

            ob_start();
            imagepng($finalImage);
            $imageData = ob_get_clean();
            imagedestroy($finalImage);

            return response()->json([
                'success'   => true,
                'message'   => 'HK QR Code generated successfully',
                'file_data' => 'data:image/png;base64,' . base64_encode($imageData),
                'filename'  => 'HK_Room_' . $rcode . '_QR.png',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong: ' . $e->getMessage(),
            ], 500);
        }
    }

 // ─── Service Facilities Load Up (Inconsistency Import) ───────────────────

    public function servicefacilitiesloadup()
    {
        $path = storage_path('app/public/servicefacilities.json');

        if (!file_exists($path)) {
            return response()->json(['message' => "File not found: $path"], 500);
        }

        $raw      = ltrim(file_get_contents($path), "\xEF\xBB\xBF");
        $jsonData = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['message' => 'JSON parsing error: ' . json_last_error_msg()], 500);
        }

       $propertyid    = $this->propertyid;
        $u_name        = Auth::user()->u_name;
        $insertedCount = 0;
        $skipped       = [];

        foreach ($jsonData as $item) {
            $type  = trim($item['type'] ?? '');
            $items = $item['items'] ?? [];

            foreach ($items as $entry) {
                $serviceName = trim($entry['service'] ?? '');
                $remark      = trim($entry['remark'] ?? '');
                $sno         = intval($entry['sno'] ?? 0);

                if (empty($serviceName)) {
                    continue;
                }

                // Check duplicate
                if (\App\Models\CompServiceFacilities::where('propertyid', $propertyid)
                    ->where('service', $serviceName)
                    ->exists()
                ) {
                    $skipped[] = $serviceName;
                    continue;
                }

                \App\Models\CompServiceFacilities::create([
                    'propertyid'   => $propertyid,
                    'displayorder' => $sno,
                    'service'      => $serviceName,
                    'servicehdr'   => $type,
                    'remark'       => $remark,
                    'isactive'     => 1,
                    'U_name'       => $u_name,
                    'U_Entdt'      => $this->currenttime,
                    'u_ae'         => 'a',
                ]);

                $insertedCount++;
            }
        }


        if ($insertedCount > 0) {
            return response()->json([
                'message' => $insertedCount . ' Service(s) Inserted Successfully! And ' . count($skipped) . ' Skipped.',
            ]);
        }

        return response()->json(['message' => 'All Services already exist! ' . count($skipped) . ' Skipped.'], 500);
    }


     // ─── Inspection Entry ─────────────────────────────────────────────────────────

    public function inspection(Request $request)
    {
        $permission = revokeopen(151111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $propertyId = $this->propertyid;
        $asOnDate   = $this->ncurdate;

        // Rooms eligible for inspection = today's Completed cleanings (latest per room)
        // Also pull rooms already in hkinspectionhdr for today so we can show re-inspect
        $completedRooms = DB::table('hkcleaninghdr as H')
            ->select(
                'H.cleaningid',
                'H.roommo as rcode',
                'H.cleaningno',
                'H.starttime',
                'H.endtime',
                'H.housekeeperid',
                'HK.name as hkname'
            )
            ->leftJoin('housekeeparmast as HK', function ($j) use ($propertyId) {
                $j->on('HK.scode', '=', 'H.housekeeperid')
                  ->where('HK.propertyid', $propertyId);
            })
            ->where('H.propertyid', $propertyId)
            ->where('H.cleaningdate', $asOnDate)
            ->where('H.cleaningstatus', 'Completed')
            ->whereRaw('H.cleaningid = (
                SELECT MAX(H2.cleaningid)
                FROM hkcleaninghdr H2
                WHERE H2.propertyid   = H.propertyid
                  AND H2.roommo       = H.roommo
                  AND H2.cleaningdate = H.cleaningdate
            )')
            // Hide rooms whose inspection is already Passed — so dropdown refreshes
            ->whereNotIn('H.cleaningid', function ($q) use ($propertyId) {
                $q->select('cleaningid')
                  ->from('hkinspectionhdr')
                  ->where('propertyid', $propertyId)
                  ->where('inspectionstatus', 'Passed');
            })
            ->orderBy('H.roommo')
            ->get();

        $checklistItems = DB::table('hkchecklistmast')
            ->where('propertyid', $propertyId)
            ->orderBy('sno')
            ->get();

        return view('property.housekeeping.inspection', compact(
            'completedRooms',
            'checklistItems',
            'asOnDate'
        ));
    }

    public function fetchinspection(Request $request)
    {
        try {
            $propertyId = $this->propertyid;
            $cleaningId = $request->input('cleaningid');

            if (!$cleaningId) {
                return response()->json(['success' => false, 'message' => 'Cleaning ID required']);
            }

            // Get cleaning header
            $hdr = DB::table('hkcleaninghdr as H')
                ->select(
                    'H.cleaningid',
                    'H.roommo as roomno',
                    'H.starttime',
                    'H.endtime',
                    'H.housekeeperid',
                    'H.roomstatusbefore',
                    'HK.name as hkname'
                )
                ->leftJoin('housekeeparmast as HK', function ($j) use ($propertyId) {
                    $j->on('HK.scode', '=', 'H.housekeeperid')
                      ->where('HK.propertyid', $propertyId);
                })
                ->where('H.cleaningid', $cleaningId)
                ->where('H.propertyid', $propertyId)
                ->first();

            if (!$hdr) {
                return response()->json(['success' => false, 'message' => 'Record not found']);
            }

            $roomno = $hdr->roomno;

            // Room details with floor & type
            $room = DB::table('room_mast as RM')
                ->select(
                    'RM.rcode',
                    'RM.room_stat',
                    DB::raw("COALESCE(FL.name, '--') as floorname"),
                    DB::raw("COALESCE(C.name,  '--') as roomtype")
                )
                ->leftJoin('hkfloors as FL', function ($j) {
                    $j->whereRaw('RM.floor COLLATE utf8mb4_unicode_ci = FL.code COLLATE utf8mb4_unicode_ci');
                })
                ->leftJoin('room_cat as C', 'RM.room_cat', '=', 'C.cat_code')
                ->where('RM.rcode', $roomno)
                ->where('RM.propertyid', $propertyId)
                ->first();

            // Occupancy check
            $isOccupied = DB::table('roomocc')
                ->where('propertyid', $propertyId)
                ->where('roomno', $roomno)
                ->whereNull('chkoutdate')
                ->exists();

            $statusMap    = ['C' => 'Vacant Clean', 'D' => 'Vacant Dirty', 'O' => 'Out of Order', 'I' => 'Inspection Pending'];
            $currentStat  = $room->room_stat ?? 'D';
            $beforeStatus = $isOccupied
                ? ($currentStat === 'D' ? 'Occupied Dirty' : 'Occupied Clean')
                : ($statusMap[$currentStat] ?? 'Vacant Dirty');

            // Check if inspection already done for this cleaningid today
            $existing = DB::table('hkinspectionhdr')
                ->where('propertyid', $propertyId)
                ->where('cleaningid', $cleaningId)
                ->orderByDesc('sn')
                ->first();

            // Next inspection serial for auto-numbering
            $lastSn       = DB::table('hkinspectionhdr')->where('propertyid', $propertyId)->max('sn') ?? 0;
            $inspectionNo = 'INS-' . date('Ymd') . '-' . str_pad($lastSn + 1, 5, '0', STR_PAD_LEFT);

            date_default_timezone_set('Asia/Kolkata');

            return response()->json([
                'success'        => true,
                'cleaningid'     => $hdr->cleaningid,
                'roomno'         => $roomno,
                'floorname'      => $room->floorname ?? '--',
                'roomtype'       => $room->roomtype  ?? '--',
                'before_status'  => $beforeStatus,
                'housekeeper'    => $hdr->hkname     ?? '',
                'starttime'      => $hdr->starttime  ? date('h:i A', strtotime($hdr->starttime))  : '--',
                'endtime'        => $hdr->endtime    ? date('h:i A', strtotime($hdr->endtime))    : '--',
                'inspection_no'  => $inspectionNo,
                'already_done'   => $existing ? true : false,
                'prev_status'    => $existing->inspectionstatus ?? null,
                'prev_score'     => $existing->inspectionscore  ?? null,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function submitinspection(Request $request)
    {
        $permission = revokeopen(151116);
        if (is_null($permission) || $permission->ins == 0) {
            return response()->json(['success' => false, 'message' => 'You have no permission to execute this functionality!'], 403);
        }
        try {
            $propertyId = $this->propertyid;

            $request->validate([
                'cleaningid'        => 'required|integer',
                'inspectionstatus'  => 'required|string|in:Passed,Failed',
            ]);

            date_default_timezone_set('Asia/Kolkata');
            $now = date('Y-m-d H:i:s');

            // Verify the cleaning record belongs to this property
            $hdr = DB::table('hkcleaninghdr')
                ->where('cleaningid', $request->cleaningid)
                ->where('propertyid', $propertyId)
                ->first();

            if (!$hdr) {
                return response()->json(['success' => false, 'message' => 'Invalid cleaning record'], 422);
            }

            $roomno = $hdr->roommo;

            // Parse checklist JSON
            $checklistData = json_decode($request->input('checklist_data', '[]'), true) ?: [];
            $totalItems    = count($checklistData);
            $passedItems   = collect($checklistData)->where('status', 'Pass')->count();
            $score         = $totalItems > 0 ? round(($passedItems / $totalItems) * 100, 2) : 0;

            DB::beginTransaction();

            // Next inspectionid for this property (inspectionid + propertyid = composite PK)
            $inspectionId = (DB::table('hkinspectionhdr')
                ->where('propertyid', $propertyId)
                ->max('inspectionid') ?? 0) + 1;

            // Insert into original hkinspectionhdr table
            $insSn = DB::table('hkinspectionhdr')->insertGetId([
                'cleaningid'       => $request->cleaningid,
                'assignmentid'     => $hdr->assno ?? 0,
                'propertyid'       => $propertyId,
                'roomno'           => $roomno,
                'inspectionid'     => $inspectionId,
                'inspectiondate'   => $now,
                'inspectionTime'   => $now,
                'inspectionstatus' => $request->inspectionstatus,
                'inspectionscore'  => $score,
                'remarks'          => mb_substr($request->input('remarks', ''), 0, 50) ?: null,
                'approvedby'       => null,
                'approveddate'     => null,
                'u_name'           => mb_substr(Auth::user()->u_name ?? '', 0, 15),
                'u_ae'             => 'a',
                'u_entdt'          => $now,
            ]);

            // Insert checklist detail rows into original hkinspectionhdtl table
            $sno = 0;
            foreach ($checklistData as $item) {
                $sno++;
                $result = strtoupper($item['status'] ?? 'Fail');
                if (!in_array($result, ['PASS', 'FAIL', 'NA'], true)) {
                    $result = 'FAIL';
                }
                DB::table('hkinspectionhdtl')->insert([
                    'propertyid'   => $propertyId,
                    'inspectionid' => $inspectionId,
                    'sno'          => $sno,
                    'checklistid'  => (int) ($item['sn'] ?? 0),
                    'u_name'       => mb_substr(Auth::user()->u_name ?? '', 0, 15),
                    'u_entdt'      => $now,
                    'u_ae'         => 'a',
                    'result'       => $result,
                    'score'        => $result === 'PASS' ? 100 : 0,
                    'remarks'      => mb_substr($item['remarks'] ?? '', 0, 50) ?: null,
                ]);
            }

            // Update room_stat based on outcome
            if ($request->inspectionstatus === 'Passed') {
                // Pass & Complete → Clean (C)
                DB::table('room_mast')
                    ->where('propertyid', $propertyId)
                    ->where('rcode', $roomno)
                    ->update(['room_stat' => 'C', 'u_updatedt' => $now]);

                // hkcleaninghdr update
                DB::table('hkcleaninghdr')
                    ->where('propertyid', $propertyId)
                    ->where('cleaningid', $request->cleaningid)
                    ->update([
                        'inspectionstatus' => 'Passed',
                        'roomstatusafter'  => 'Clean',
                    ]);

            } elseif ($request->inspectionstatus === 'Failed') {
                // Fail → Dirty (D) — goes back to cleaning queue
                DB::table('room_mast')
                    ->where('propertyid', $propertyId)
                    ->where('rcode', $roomno)
                    ->update(['room_stat' => 'D', 'u_updatedt' => $now]);

                // hkcleaninghdr update
                DB::table('hkcleaninghdr')
                    ->where('propertyid', $propertyId)
                    ->where('cleaningid', $request->cleaningid)
                    ->update([
                        'inspectionstatus' => 'Failed',
                        'roomstatusafter'  => 'Dirty',
                    ]);
            }

            DB::commit();

            $msg = 'Inspection saved! Score: ' . $score . '%';

            session()->flash('success', $msg);

            return response()->json([
                'success'          => true,
                'message'          => $msg,
                'inspection_sn'    => $insSn,
                'score'            => $score,
                'inspectionstatus' => $request->inspectionstatus,
            ]);

        } catch (\Illuminate\Validation\ValidationException $ve) {
            return response()->json(['success' => false, 'message' => implode(' ', Arr::flatten($ve->errors()))], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    // ─── GM-01: Wake-up Call Booking ───────────────────────────────────

    public function wakeuplist(Request $request)
    {
        $fromdate = $this->ncurdate;
        return view('property.housekeeping.wakeuplist', [
            'fromdate' => $fromdate,
        ]);
    }

    public function fetchwakeupdata(Request $request)
    {
        $fromdate = $request->input('fromdate', $this->ncurdate);
        $todate = $request->input('todate', $fromdate);

        $data = Guestwakeup::where('propertyid', $this->propertyid)
            ->whereBetween('wdate', [$fromdate, $todate])
            ->orderBy('wdate', 'DESC')
            ->orderBy('vno', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function openwakeupentry(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $rooms = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->where('type', 'I')
            ->leftJoin('room_mast', 'room_mast.code', '=', 'roomocc.roomno')
            ->leftJoin('roomcat', 'roomcat.code', '=', 'room_mast.roomcat')
            ->select('roomocc.roomno', 'roomocc.guestprof', 'roomocc.folionodocid', 'roomcat.name as roomcatname')
            ->orderBy('roomocc.roomno')
            ->get();

        $nextVno = Guestwakeup::where('propertyid', $this->propertyid)->max('vno') + 1;

        return response()->json([
            'rooms' => $rooms,
            'vno' => $nextVno,
            'wdate' => $this->ncurdate,
            'wtime' => date('H:i'),
        ]);
    }

    public function submitwakeup(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $validate = $request->validate([
            'roomno' => 'required',
            'wdate' => 'required',
            'wtime' => 'required',
        ]);

        try {
            $vno = Guestwakeup::where('propertyid', $this->propertyid)->max('vno') + 1;
            $docid = 'WU' . $vno . '|' . date('Y', strtotime($this->ncurdate)) . '|' . $this->propertyid;

            Guestwakeup::create([
                'propertyid' => $this->propertyid,
                'docid' => $docid,
                'vno' => $vno,
                'roomno' => $request->input('roomno'),
                'roomcat' => $request->input('roomcat', ''),
                'extension' => $request->input('extension', ''),
                'remreqd' => $request->input('remreqd', 'N'),
                'foodord' => $request->input('foodord', 'N'),
                'otherreq' => $request->input('otherreq', ''),
                'wdate' => $request->input('wdate'),
                'wtime' => $request->input('wtime'),
                'guestprof' => $request->input('guestprof', ''),
                'folionodocid' => $request->input('folionodocid', ''),
                'u_name' => $this->username,
                'u_entdt' => $this->currenttime,
                'u_ae' => 'A',
            ]);

            return response()->json(['success' => true, 'message' => 'Wake-up call booked successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function deletewakeup(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID required'], 400);
        }
        try {
            Guestwakeup::where('id', $id)->where('propertyid', $this->propertyid)->delete();
            return response()->json(['success' => true, 'message' => 'Wake-up call deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function printwakeuplist(Request $request)
    {
        $fromdate = $request->input('fromdate', $this->ncurdate);
        $todate = $request->input('todate', $fromdate);

        $data = Guestwakeup::where('propertyid', $this->propertyid)
            ->whereBetween('wdate', [$fromdate, $todate])
            ->orderBy('wdate', 'DESC')
            ->orderBy('vno', 'DESC')
            ->get();

        $company = Companyreg::where('propertyid', $this->propertyid)->first();

        return view('property.housekeeping.printwakeuplist', [
            'data' => $data,
            'fromdate' => $fromdate,
            'todate' => $todate,
            'company' => $company,
        ]);
    }

    // ─── GM-02: House Guest Messages ───────────────────────────────────

    public function guestmessagelist(Request $request)
    {
        $fromdate = $this->ncurdate;
        return view('property.housekeeping.guestmessagelist', [
            'fromdate' => $fromdate,
        ]);
    }

    public function fetchguestmessagedata(Request $request)
    {
        $fromdate = $request->input('fromdate', $this->ncurdate);
        $todate = $request->input('todate', $fromdate);
        $status = $request->input('status', '');

        $query = Guestmessage::where('propertyid', $this->propertyid)
            ->whereBetween('recddate', [$fromdate, $todate]);

        if ($status) {
            $query->where('status', $status);
        }

        $data = $query->orderBy('recddate', 'DESC')
            ->orderBy('id', 'DESC')
            ->get();

        return response()->json(['data' => $data]);
    }

    public function openguestmessageentry(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $rooms = DB::table('roomocc')
            ->where('propertyid', $this->propertyid)
            ->where('type', 'I')
            ->leftJoin('room_mast', 'room_mast.code', '=', 'roomocc.roomno')
            ->leftJoin('roomcat', 'roomcat.code', '=', 'room_mast.roomcat')
            ->select('roomocc.roomno', 'roomocc.guestprof', 'roomocc.folionodocid', 'roomcat.name as roomcatname')
            ->orderBy('roomocc.roomno')
            ->get();

        return response()->json([
            'rooms' => $rooms,
            'recddate' => $this->ncurdate,
            'recdtime' => date('H:i'),
        ]);
    }

    public function submitguestmessage(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $validate = $request->validate([
            'roomno' => 'required',
            'caller' => 'required',
            'message' => 'required',
            'recddate' => 'required',
        ]);

        try {
            Guestmessage::create([
                'propertyid' => $this->propertyid,
                'roomno' => $request->input('roomno'),
                'roomcat' => $request->input('roomcat', ''),
                'caller' => $request->input('caller'),
                'telephone' => $request->input('telephone', ''),
                'message' => $request->input('message'),
                'recddate' => $request->input('recddate'),
                'recdtime' => $request->input('recdtime', date('H:i')),
                'guestprof' => $request->input('guestprof', ''),
                'folionodocid' => $request->input('folionodocid', ''),
                'status' => 'Pending',
                'u_name' => $this->username,
                'u_entdt' => $this->currenttime,
                'u_ae' => 'A',
            ]);

            return response()->json(['success' => true, 'message' => 'Message recorded successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function markmessagedelivered(Request $request)
    {
        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID required'], 400);
        }
        try {
            Guestmessage::where('id', $id)
                ->where('propertyid', $this->propertyid)
                ->update([
                    'status' => 'Delivered',
                    'deliveredby' => $this->username,
                ]);
            return response()->json(['success' => true, 'message' => 'Message marked as delivered']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function deleteguestmessage(Request $request)
    {
        $permission = revokeopen(151112);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json(['success' => false, 'message' => 'Unauthorized access'], 403);
        }
        $id = $request->input('id');
        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID required'], 400);
        }
        try {
            Guestmessage::where('id', $id)->where('propertyid', $this->propertyid)->delete();
            return response()->json(['success' => true, 'message' => 'Message deleted']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function printguestmessagelist(Request $request)
    {
        $fromdate = $request->input('fromdate', $this->ncurdate);
        $todate = $request->input('todate', $fromdate);

        $data = Guestmessage::where('propertyid', $this->propertyid)
            ->whereBetween('recddate', [$fromdate, $todate])
            ->orderBy('recddate', 'DESC')
            ->get();

        $company = Companyreg::where('propertyid', $this->propertyid)->first();

        return view('property.housekeeping.printguestmessagelist', [
            'data' => $data,
            'fromdate' => $fromdate,
            'todate' => $todate,
            'company' => $company,
        ]);
    }
}
