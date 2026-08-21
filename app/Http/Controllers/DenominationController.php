<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DenominationDetail;
use App\Models\DenominationFormat;

class DenominationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->propertyid = session('propertyid') ?? 0;
    }

    /**
     * Display denomination list
     */
    public function index(Request $request)
    {
        $permission = revokeopen(181111);
        if (is_null($permission) || $permission->view == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $data = DenominationDetail::where('propertyid', $this->propertyid)
            ->where('delflag', '!=', 'Y')
            ->orderBy('sno', 'desc')
            ->get();

        $formats = DenominationFormat::where('propertyid', $this->propertyid)
            ->orderBy('sno')
            ->get();

        return view('property.denomination.denominationlist', compact('data', 'formats'));
    }

    /**
     * Show create form
     */
    public function create(Request $request)
    {
        $permission = revokeopen(181111);
        if (is_null($permission) || $permission->add == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $maxSno = DenominationDetail::where('propertyid', $this->propertyid)->max('sno') ?? 0;
        $nextSno = $maxSno + 1;

        $formats = DenominationFormat::where('propertyid', $this->propertyid)
            ->orderBy('sno')
            ->get();

        return view('property.denomination.denominationform', compact('nextSno', 'formats'));
    }

    /**
     * Store denomination entry
     */
    public function store(Request $request)
    {
        $permission = revokeopen(181111);
        if (is_null($permission) || $permission->add == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $request->validate([
            'vdate' => 'required|date',
            'name' => 'required|string|max:50',
            'items' => 'required|array|min:1',
            'items.*.denominationtype' => 'required|string',
            'items.*.denominationvalue' => 'required|numeric|min:0',
            'items.*.denominationunit' => 'required|string',
            'items.*.denominationtotal' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $sno = DenominationDetail::where('propertyid', $this->propertyid)->max('sno') ?? 0;
            $sno++;

            foreach ($request->items as $idx => $item) {
                DenominationDetail::create([
                    'propertyid' => $this->propertyid,
                    'sno' => $sno,
                    'sno1' => $idx + 1,
                    'vdate' => $request->vdate,
                    'name' => $request->name,
                    'denominationtype' => $item['denominationtype'],
                    'denominationvalue' => $item['denominationvalue'],
                    'denominationunit' => $item['denominationunit'],
                    'denominationtotal' => $item['denominationtotal'],
                    'relation' => $item['relation'] ?? '',
                    'type' => $item['type'] ?? '',
                    'u_name' => auth()->user()->name ?? 'sa',
                    'u_entdt' => now(),
                    'u_ae' => 'a',
                    'delflag' => '',
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Denomination entry saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Show denomination detail
     */
    public function show($sno)
    {
        $data = DenominationDetail::where('propertyid', $this->propertyid)
            ->where('sno', $sno)
            ->where('delflag', '!=', 'Y')
            ->get();

        $formats = DenominationFormat::where('propertyid', $this->propertyid)
            ->orderBy('sno')
            ->get();

        return response()->json(['data' => $data, 'formats' => $formats]);
    }

    /**
     * Delete denomination entry
     */
    public function destroy($sno)
    {
        $permission = revokeopen(181111);
        if (is_null($permission) || $permission->delete == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        DB::beginTransaction();
        try {
            DenominationDetail::where('propertyid', $this->propertyid)
                ->where('sno', $sno)
                ->update([
                    'delflag' => 'Y',
                    'u_updatedt' => now(),
                ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Denomination entry deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get denomination formats
     */
    public function getFormats()
    {
        $formats = DenominationFormat::where('propertyid', $this->propertyid)
            ->orderBy('sno')
            ->get();

        return response()->json(['data' => $formats]);
    }

    /**
     * Save denomination format
     */
    public function saveFormat(Request $request)
    {
        $permission = revokeopen(181111);
        if (is_null($permission) || $permission->add == 0) {
            return redirect()->back()->with('error', 'You have no permission to execute this functionality!');
        }

        $request->validate([
            'denominationtype' => 'required|string|max:50',
            'denominationvalue' => 'required|numeric|min:0',
            'denominationunit' => 'required|string|max:10',
        ]);

        DB::beginTransaction();
        try {
            // Clear existing formats
            DenominationFormat::where('propertyid', $this->propertyid)->delete();

            // Save new format
            DenominationFormat::create([
                'propertyid' => $this->propertyid,
                'sno' => 1,
                'denominationtype' => $request->denominationtype,
                'denominationvalue' => $request->denominationvalue,
                'denominationunit' => $request->denominationunit,
                'denominationtotal' => 0,
                'relation' => '',
                'type' => '',
                'u_name' => auth()->user()->name ?? 'sa',
                'u_entdt' => now(),
                'u_ae' => 'a',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Format saved successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Print denomination report
     */
    public function print($sno)
    {
        $data = DenominationDetail::where('propertyid', $this->propertyid)
            ->where('sno', $sno)
            ->where('delflag', '!=', 'Y')
            ->get();

        $comp = \App\Models\Companyreg::where('propertyid', $this->propertyid)->first();

        return view('property.denomination.printdenomination', compact('data', 'comp', 'sno'));
    }
}
