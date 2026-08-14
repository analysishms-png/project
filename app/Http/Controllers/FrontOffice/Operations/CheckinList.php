<?php

namespace App\Http\Controllers\FrontOffice\Operations;

use App\Http\Controllers\Controller;
use App\Models\Guestfolio;
use App\Models\RoomOcc;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CheckinList extends Controller
{
    protected $username;
    protected $propertyid;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!isset(Auth::user()->name)) {
                return redirect('/');
            }

            $this->username = Auth::user()->name;
            $this->propertyid = Auth::user()->propertyid;
            return $next($request);
        });
    }

    public function opencheckinlist()
    {
        $permission = revokeopen(141113);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        return view('property.checkinlist');
    }

    public function checkinlistData(Request $request)
    {
        $permission = revokeopen(141113);
        if (is_null($permission) || $permission->view == 0) {
            return response()->json([
                'draw' => (int) $request->input('draw', 0),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'message' => 'You have no permission to execute this functionality!'
            ], 403);
        }

        $draw = (int) $request->input('draw', 0);
        $start = max((int) $request->input('start', 0), 0);
        $length = (int) $request->input('length', 15);
        $length = $length > 0 ? min($length, 100) : 15;
        $searchValue = trim((string) $request->input('search.value', ''));

        $columnMap = [
            1 => 'guest_name',
            2 => 'city',
            3 => 'mobile_no',
            4 => 'room_no',
            5 => 'folio_no',
            6 => 'bill_no',
            7 => 'chkindate',
            8 => 'checkin_time',
            9 => 'exp_dep_date',
            10 => 'dep_date',
            11 => 'deptime',
            12 => 'rate',
            13 => 'tax_inc',
            14 => 'compname',
            15 => 'travelagent',
        ];

        $orderColumnIndex = (int) $request->input('order.0.column', 5);
        $orderDirection = strtolower((string) $request->input('order.0.dir', 'desc')) === 'asc' ? 'asc' : 'desc';
        $orderColumn = $columnMap[$orderColumnIndex] ?? 'folio_no';

        $baseQuery = $this->checkinListBaseQuery();

        $totalRecords = DB::query()->fromSub($baseQuery, 'checkins')->count();

        $filteredQuery = DB::query()->fromSub($this->checkinListBaseQuery(), 'checkins');

        if ($searchValue !== '') {
            $filteredQuery->where(function ($query) use ($searchValue) {
                $query->where('guest_name', 'like', "%{$searchValue}%")
                    ->orWhere('city', 'like', "%{$searchValue}%")
                    ->orWhere('mobile_no', 'like', "%{$searchValue}%")
                    ->orWhere('room_no', 'like', "%{$searchValue}%")
                    ->orWhere('folio_no', 'like', "%{$searchValue}%")
                    ->orWhere('bill_no', 'like', "%{$searchValue}%")
                    ->orWhere('compname', 'like', "%{$searchValue}%")
                    ->orWhere('travelagent', 'like', "%{$searchValue}%")
                    ->orWhere('remark', 'like', "%{$searchValue}%");
            });
        }

        $recordsFiltered = (clone $filteredQuery)->count();

        $rows = $filteredQuery
            ->orderBy($orderColumn, $orderDirection)
            ->orderByDesc('sort_vprefix')
            ->orderByDesc('sort_folio_no')
            ->orderByDesc('sort_u_entdt')
            ->orderByDesc('docid')
            ->offset($start)
            ->limit($length)
            ->get();

        $data = $rows->map(function ($row, $index) use ($start) {
            $depTime = $this->formatTime($row->deptime);

            return [
                'sn' => $start + $index + 1,
                'docid' => $row->docid,
                'sno1' => $row->sno1,
                'type' => $row->type,
                'guest_name' => $row->guest_name ?? '',
                'city' => $row->city ?? '',
                'mobile_no' => $row->mobile_no ?? '',
                'room_no' => $row->room_no ?? '',
                'folio_no' => $row->folio_no ?? '',
                'bill_no' => $row->bill_no ?? '',
                'chkindate' => $this->formatDate($row->chkindate),
                'checkin_time' => $this->formatTime($row->checkin_time),
                'exp_dep_date' => $this->formatDate($row->exp_dep_date),
                'dep_date' => $this->formatDate($row->dep_date),
                'deptime' => $depTime,
                'deptime_raw' => $depTime,
                'rate' => $row->rate ?? '',
                'tax_inc' => $row->tax_inc === 'Y' ? 'Yes' : ($row->tax_inc === 'N' ? 'No' : ''),
                'compname' => $row->compname ?? '',
                'travelagent' => $row->travelagent ?? '',
                'action' => $this->buildActionButtons($row->docid, $row->sno1, $row->guest_name, $row->folio_no),
            ];
        });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function updatecheckouttime(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'docid' => ['required'],
            'sno1' => ['required'],
            'new_checkout_time' => ['required', 'date_format:H:i'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $docid = $request->input('docid');
        $sno1 = $request->input('sno1');
        $checkouttime = $request->input('new_checkout_time');

        $updated = RoomOcc::where('propertyid', $this->propertyid)
            ->where('docid', $docid)
            ->where('sno1', $sno1)
            ->update(['chkouttime' => $checkouttime]);

        if (!$updated) {
            return response()->json([
                'success' => false,
                'message' => 'Checkout time could not be updated'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Checkout time updated successfully',
            'formatted_time' => $this->formatTime($checkouttime)
        ]);
    }

    protected function checkinListBaseQuery()
    {
        $query = Guestfolio::query()
            ->select([
                'guestfolio.docid',
                'roomocc.sno1',
                'guestfolio.folio_no as folio_no',
                DB::raw('MAX(paycharge.billno) as bill_no'),
                'roomocc.roomno as room_no',
                'roomocc.chkindate as chkindate',
                'roomocc.chkintime as checkin_time',
                'roomocc.chkoutdate as dep_date',
                'roomocc.depdate as exp_dep_date',
                'roomocc.chkouttime as deptime',
                'guestfolio.Name as guest_name',
                'roomocc.type',
                'guestprof.mobile_no',
                'cities.cityname as city',
                'guestfolio.Remark as remark',
                'roomocc.rackrate as rate',
                'roomocc.rrtaxinc as tax_inc',
                'subcom.name as compname',
                'travelcom.name as travelagent',
                'roomocc.vprefix as sort_vprefix',
                'roomocc.folioNo as sort_folio_no',
                'roomocc.u_entdt as sort_u_entdt',
            ])
            ->leftJoin('roomocc', 'guestfolio.docid', '=', 'roomocc.docid')
            ->leftJoin('guestprof', function ($join) {
                $join->on('guestprof.guestcode', '=', 'roomocc.guestprof')
                    ->where('guestprof.propertyid', '=', $this->propertyid);
            })
            ->leftJoin('cities', function ($join) {
                $join->on('cities.city_code', '=', 'guestfolio.city')
                    ->where('cities.propertyid', '=', $this->propertyid);
            })
            ->leftJoin('subgroup as subcom', function ($join) {
                $join->on('subcom.sub_code', '=', 'guestfolio.company')
                    ->where('subcom.propertyid', '=', $this->propertyid);
            })
            ->leftJoin('subgroup as travelcom', function ($join) {
                $join->on('travelcom.sub_code', '=', 'guestfolio.travelagent')
                    ->where('travelcom.propertyid', '=', $this->propertyid);
            })
            ->leftJoin('paycharge', function ($join) {
                $join->on('paycharge.folionodocid', '=', 'roomocc.docid')
                    ->on('paycharge.sno1', '=', 'roomocc.sno1')
                    ->where('paycharge.propertyid', '=', $this->propertyid);
            })
            ->where('guestfolio.propertyid', $this->propertyid)
            ->where(function ($query) {
                $query->where('roomocc.type', '!=', 'C')
                    ->orWhereNull('roomocc.type');
            })
            ->groupBy([
                'guestfolio.docid',
                'roomocc.sno1',
                'guestfolio.folio_no',
                'roomocc.roomno',
                'roomocc.chkindate',
                'roomocc.chkintime',
                'roomocc.chkoutdate',
                'roomocc.depdate',
                'roomocc.chkouttime',
                'guestfolio.Name',
                'roomocc.type',
                'guestprof.mobile_no',
                'cities.cityname',
                'guestfolio.Remark',
                'roomocc.rackrate',
                'roomocc.rrtaxinc',
                'subcom.name',
                'travelcom.name',
                'roomocc.vprefix',
                'roomocc.folioNo',
                'roomocc.u_entdt',
            ]);

        if (Auth::user()->superwiser != 1) {
            $query->whereDate('roomocc.chkindate', ncurdate());
        }

        return $query;
    }

    protected function formatDate($value)
    {
        if (empty($value)) {
            return '';
        }

        try {
            return date('d-m-Y', strtotime($value));
        } catch (\Throwable $th) {
            return '';
        }
    }

    protected function formatTime($value)
    {
        if (empty($value)) {
            return '';
        }

        try {
            return date('H:i', strtotime($value));
        } catch (\Throwable $th) {
            return substr((string) $value, 0, 5);
        }
    }

    protected function buildActionButtons($docid, $sno1, $guestName = '', $folioNo = '')
    {
        $editUrl = url('updatewalkin?docid=' . $docid);
        $printUrl = url('printwalkin/' . $docid);
        $deleteUrl = url('deletewalkin?docid=' . base64_encode($docid) . '&sno1=' . base64_encode($sno1));

        return '
            <div class="action-buttons">
                <a href="' . e($editUrl) . '" class="btn btn-success btn-sm"><i class="far fa-edit mr-1"></i>Edit</a>
                <a href="' . e($printUrl) . '" target="_blank" class="btn btn-primary btn-sm"><i class="fas fa-print mr-1"></i>Print</a>
                <a href="' . e($deleteUrl) . '" class="btn btn-danger btn-sm js-delete-checkin" data-guest-name="' . e($guestName) . '" data-folio-no="' . e($folioNo) . '"><i class="fas fa-trash mr-1"></i>Delete</a>
            </div>
        ';
    }
}
