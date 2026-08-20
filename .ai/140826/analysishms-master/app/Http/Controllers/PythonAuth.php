<?php

namespace App\Http\Controllers;

use App\Models\Billprintthermal;
use App\Models\Companyreg;
use App\Models\EnviroPos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Kot;
use App\Models\PrintDelay;
use App\Models\PrintingSetup;
use App\Models\Sagar;
use App\Models\Sale1;
use App\Models\Stock;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class PythonAuth extends Controller
{

    public function login(Request $request)
    {
        $data = $request->json()->all();

        $request->validate([
            'username' => 'required|string',
            'property_id' => 'required|string',
            'password' => 'required|string',
        ]);

        // Log::info('Login Request:', $data);

        $user = User::where('name', $data['username'])
            ->where('propertyid', $data['property_id'])
            ->first();

        // Log::info('User Found:', ['user' => $user]);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'Account does not exist',
            ]);
        }

        if ($user->status !== 1) {
            return response()->json([
                'status' => 'error',
                'Account is not active',
            ]);
        }

        if ($user && Hash::check($data['password'], $user->password)) {

            $userdata = User::select('users.*', 'userpermission.system_name')
                ->leftJoin('userpermission', function ($join) use ($user) {
                    $join->on('userpermission.username', '=', 'users.u_name')
                        ->where('userpermission.propertyid', '=', $user->propertyid);
                })
                ->where('users.propertyid', $user->propertyid)
                ->where('users.u_name', $user->u_name)
                ->where('users.useroradmin', 'user')
                ->first();

            return response()->json([
                'status' => 'success',
                'message' => 'Login successful',
                'userdetails' => $userdata,
            ]);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid credentials',
            ], 401);
        }
    }

    public function getproperty(Request $request)
    {
        $data = $request->json()->all();

        $request->validate([
            'property_id' => 'required|string',
            'username' => 'required|string',
        ]);


        $user = User::where('name', $data['username'])
            ->where('propertyid', $data['property_id'])
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account does not exist',
            ]);
        }

        if ($user->status !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account is not active',
            ]);
        }

        try {

            $compdata = Companyreg::where('propertyid', $user->propertyid)
                // ->where('u_name', $user->name)
                // ->whereIn('role', ['Property', 'User'])
                ->first();

            if ($compdata) {
                return response()->json([
                    'status' => 'success',
                    'data' => $compdata,
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Company Data Not Found',
                ]);
            }
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
            ]);
        }
    }

    public function fetchprintdata(Request $request)
    {
        $request->validate([
            'property_id' => 'required|string'
        ]);

        $payload = $request->json()->all();

        $cacheKey = 'print_' . $payload['property_id'];

        return Cache::remember($cacheKey, 3, function () use ($payload) {

            $printdelay = PrintDelay::where('propertyid', $payload['property_id'])
                ->latest('sn')
                ->select('propertyid', 'docid', 'restcode')
                ->first();

            if (!$printdelay) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No data found'
                ]);
            }

            try {
                $enviropos = EnviroPos::where('propertyid', $printdelay->propertyid)->first();
                $company = Companyreg::where('propertyid', $printdelay->propertyid)->first();

                $printdata = PrintDelay::select('printdelay.*', 'depart.name as kitchenname')
                    ->leftJoin('depart', 'depart.dcode', '=', 'printdelay.kitchen')
                    ->where('printdelay.propertyid', $printdelay->propertyid)
                    ->where('printdelay.docid', $printdelay->docid)
                    ->orderBy('printdelay.kitchen', 'ASC')
                    ->get();

                $kotdetail = Kot::select(
                    'kot.*',
                    'server_mast.name as waitername',
                    'depart.name as departname',
                    'nctype_mast.nctype as nctypename'
                )
                    ->leftJoin('depart', 'depart.dcode', '=', 'kot.restcode')
                    ->leftJoin('server_mast', 'server_mast.scode', '=', 'kot.waiter')
                    ->leftJoin('nctype_mast', function($join) {
                        $join->on('nctype_mast.ncode', '=', 'kot.nctype')
                             ->on('nctype_mast.propertyid', '=', 'kot.propertyid');
                    })
                    ->where('kot.docid', $printdelay->docid)
                    ->where('kot.propertyid', $printdelay->propertyid)
                    ->first();

                if (!$kotdetail) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'KOT details not found for docid ' . $printdelay->docid,
                    ]);
                }

                $printerpath = PrintingSetup::where('restcode', $printdelay->restcode)
                    ->where('propertyid', $printdelay->propertyid)
                    ->get();

                $roomno = $kotdetail->roomno;

                $chk = Kot::where('docid', '!=', $printdelay->docid)
                    ->where('propertyid', $payload['property_id'])
                    ->where('roomno', $roomno)
                    ->first();

                $kottype = $kotdetail->nckot == 'Y' ? 'NC KOT' : 'KOT';
                $ordertype = is_null($chk) ? 'New Order' : 'Running Order';
                $ordertype = $kottype == 'NC KOT' ? '' : $ordertype;

                return response()->json([
                    'status' => 'success',
                    'data' => [
                        'kottype' => $kottype,
                        'ordertype' => $ordertype,
                        'printdata' => $printdata,
                        'kotdetail' => $kotdetail,
                        'printerpath' => $printerpath,
                        'enviropos' => $enviropos,
                        'company' => $company
                    ]
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
                ]);
            }
        });
    }


    public function deleteprintdata(Request $request)
    {
        $data = $request->json()->all();

        $request->validate([
            'property_id' => 'required|string',
            'docid' => 'required'
        ]);

        $docid = $data['docid'];

        // Support both single docid (string) and array of docids
        if (is_string($docid)) {
            $docid = [$docid];
        }

        try {
            PrintDelay::where('propertyid', $data['property_id'])
                ->whereIn('docid', $docid)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Queue Data Deleted Successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
            ]);
        }
    }

    public function deleteprintdatabill(Request $request)
    {
        $data = $request->json()->all();
        // Log::info('Delete Print Data Bill Request:', $data);
        $request->validate([
            'property_id' => 'required|string',
            'docid' => 'required'
        ]);

        $docid = $data['docid'];
        // Handle both string and array of docids
        if (is_string($docid)) {
            $docid = [$docid];
        }

        try {
            $deletedCount = Billprintthermal::where('propertyid', $data['property_id'])
                ->whereIn('docid', $docid)
                ->delete();

            return response()->json([
                'status' => 'success',
                'message' => "Deleted $deletedCount Billprintthermal records",
                'deleted_count' => $deletedCount
            ]);
        } catch (Exception $e) {
            Log::error('Error deleting bill print data:', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
            ]);
        }
    }

    public function fetchprintdatabill(Request $request)
    {
        try {
            $data = $request->json()->all();

            $billprint = Billprintthermal::where('propertyid', $data['property_id'])
                ->orderByDesc('sn')
                ->get();

            if ($billprint) {
                return $billprint;
            }

            return response()->json([
                'status' => 'error',
                'message' => 'No data found'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
