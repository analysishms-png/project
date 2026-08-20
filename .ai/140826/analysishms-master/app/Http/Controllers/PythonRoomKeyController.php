<?php

namespace App\Http\Controllers;

use App\Models\Roomkey;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Log;

class PythonRoomKeyController extends Controller
{
    public function fetchroomkeydata(Request $request)
    {
        $request->validate([
            'property_id' => 'required|string',
        ]);

        $propertyId = $request->input('property_id');

        Log::info('data: ' . $request->input('property_id'));

        try {
            $rows = Roomkey::where('propertyid', $propertyId)
                ->where('status', 0)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($row) {
                    return [
                        'id' => $row->id,
                        'propertyid' => $row->propertyid,
                        'docid' => $row->docid,
                        'u_name' => $row->u_name,
                        'status' => $row->status,
                        'data' => $row->data,
                        'created_at' => $row->created_at,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $rows,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateroomkeydata(Request $request)
    {
        $request->validate([
            'property_id' => 'required|string',
            'docid' => 'required',
            'reason' => 'nullable|string'
        ]);

        Log::info('data: ' . $request->input('property_id') . ' docid: ' . json_encode($request->input('docid')) . ' reason: ' . $request->input('reason'));

        $propertyId = $request->input('property_id');
        $docids = $request->input('docid');

        if (is_string($docids)) {
            $docids = [$docids];
        }

        try {
            $updated = Roomkey::where('propertyid', $propertyId)
                ->whereIn('docid', $docids)
                ->where('status', 0)
                ->update([
                    'status' => 1,
                    'reason' => $request->input('reason')
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Room key queue updated',
                'updated_count' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateroomkeyfailed(Request $request)
    {
        $request->validate([
            'property_id' => 'required|string',
            'docid' => 'required',
            'reason' => 'required'
        ]);

        Log::info('data: ' . $request->input('property_id') . ' docid: ' . json_encode($request->input('docid')) . ' reason: ' . $request->input('reason'));

        $propertyId = $request->input('property_id');
        $docids = $request->input('docid');
        $reason = $request->input('reason');

        if (is_string($docids)) {
            $docids = [$docids];
        }

        try {
            $updated = Roomkey::where('propertyid', $propertyId)
                ->whereIn('docid', $docids)
                ->where('status', 0)
                ->update([
                    'reason' => $reason,
                    'status' => 2
                ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Room key queue updated',
                'updated_count' => $updated,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unknown Error Occurred: ' . $e->getMessage(),
            ], 500);
        }
    }
}
