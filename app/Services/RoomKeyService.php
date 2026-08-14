<?php

namespace App\Services;

use App\Models\Roomkey;
use App\Models\RoomOcc;
use Illuminate\Support\Facades\Auth;
use App\Models\Log as MaintainLog;

class RoomKeyService
{
    public function store(array $data): array
    {
        return [
            'status' => true,
            'message' => 'Success',
            'data' => $data
        ];
    }

    public function push($docid)
    {

        try {
            $propertyid = Auth::user()->propertyid;

            // Check if an unprocessed request already exists for this docid
            $exists = Roomkey::where('propertyid', $propertyid)
                ->where('docid', $docid)
                ->where('status', 0)
                ->exists();

            if ($exists) {
                return [
                    'status' => false,
                    'message' => 'Room key request already queued.'
                ];
            }

            $roomoccs = RoomOcc::where('propertyid', $propertyid)
                ->where('docid', $docid)
                ->get();

            if ($roomoccs->isEmpty()) {
                return [
                    'status' => false,
                    'message' => 'Room occupancy not found'
                ];
            }

            $requests = [];

            foreach ($roomoccs as $roomocc) {
                $requests[] = [
                    'roomno' => $roomocc->roomno,
                    'guest_no' => $roomocc->guestprof,
                    'guest_name' => $roomocc->name,
                    'arrival_date' => date('ymd', strtotime($roomocc->chkindate)),
                    'departure_date' => date('ymd', strtotime($roomocc->depdate)),
                    'departure_time' => date('H:i', strtotime($roomocc->deptime)),
                    'keytype' => 'KTN',
                    'keys' => '01',
                    'keycoder' => 'KC01',
                ];
            }

            $payload = [
                'command' => 'KR',
                'workstation' => 'WSINTFCE',
                'request_date' => now()->format('ymd'),
                'request_time' => now()->format('His'),
                'requests' => $requests
            ];

            Roomkey::create([
                'propertyid' => $propertyid,
                'docid' => $docid,
                'data' => $payload,
                'u_name' => Auth::user()->name,
                'status' => 0
            ]);

            return [
                'status' => true,
                'message' => 'Key request queued'
            ];
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];

            MaintainLog::create([
                'propertyid' => Auth::user()->propertyid,
                'username' => Auth::user()->name,
                'log_type' => 'RoomKeyService',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'ip_address' => request()->ip()
            ]);
        }
    }
}
