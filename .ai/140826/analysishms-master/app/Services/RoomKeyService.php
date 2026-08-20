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

    public function push($docid, $sno1): array
    {

        try {
            $propertyid = Auth::user()->propertyid;

            // Check if an unprocessed request already exists for this docid
            $exists = Roomkey::where('propertyid', $propertyid)
                ->where('docid', $docid)
                ->where('sno1', $sno1)
                ->where('status', 0)
                ->exists();

            if ($exists) {
                Roomkey::where('propertyid', $propertyid)
                    ->where('docid', $docid)
                    ->where('sno1', $sno1)
                    ->where('status', 0)
                    ->delete();
            }

            $roomoccs = RoomOcc::where('propertyid', $propertyid)
                ->where('docid', $docid)
                ->where('sno1', $sno1)
                ->get();

            if ($roomoccs->isEmpty()) {
                return [
                    'success' => false,
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
                'workstation' => 'WSBH.COM.694798548',
                'request_date' => now()->format('ymd'),
                'request_time' => now()->format('His'),
                'requests' => $requests
            ];

            $roomkey = Roomkey::create([
                'propertyid' => $propertyid,
                'docid' => $docid,
                'sno1' => $sno1,
                'data' => $payload,
                'u_name' => Auth::user()->name,
                'status' => 0
            ]);

            $waitTimeoutSeconds = 3;
            $pollIntervalMicroseconds = 500000; // 0.5 seconds
            $deadline = microtime(true) + $waitTimeoutSeconds;
            $finalStatus = $roomkey->status;
            $finalReason = null;

            while (microtime(true) < $deadline) {
                usleep($pollIntervalMicroseconds);

                $roomkey = Roomkey::where('propertyid', $propertyid)
                    ->where('docid', $docid)
                    ->where('sno1', $sno1)
                    ->orderByDesc('id')
                    ->first(['status', 'reason']);

                if (!$roomkey) {
                    break;
                }

                $finalStatus = $roomkey->status;
                $finalReason = $roomkey->reason ?? null;

                if ($finalStatus === 1 || $finalStatus === 2) {
                    break;
                }
            }

            if ($finalStatus === 1) {
                return [
                    'success' => true,
                    'message' => 'Room key allotted successfully',
                    'status' => 1
                ];
            }

            if ($finalStatus === 2) {
                return [
                    'success' => false,
                    'message' => 'Room key push failed' . ($finalReason ? ': ' . $finalReason : ''),
                    'status' => 2,
                    'reason' => $finalReason
                ];
            }

            return [
                'success' => true,
                'message' => 'Key request queued',
                'status' => 0
            ];
        } catch (\Exception $e) {

            MaintainLog::create([
                'propertyid' => Auth::user()->propertyid,
                'username' => Auth::user()->name,
                'log_type' => 'RoomKeyService',
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'ip_address' => request()->ip()
            ]);

            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }
}
