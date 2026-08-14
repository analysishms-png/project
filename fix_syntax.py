import re

filepath = 'C:/xampp/htdocs/analysishms-master/app/Http/Controllers/HouseKeeping.php'

print(f"Reading from: {filepath}")
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Find and remove the incorrectly placed rejectinspection method
# It was inserted inside savehousecleaning, we need to remove it

old_bad = """                }

                // FIX #3: Inspection Rejection Method
    // Room goes to RE-CLEAN REQUIRED status

    public function rejectinspection(Request $request)
    {
        try {
            $propertyId = $this->propertyid;
            $cleaningId = $request->input('cleaning_id');
            $rejectionReason = $request->input('rejection_reason', '');
            
            if (empty($cleaningId)) {
                return response()->json(['success' => false, 'message' => 'Cleaning ID required!']);
            }
            
            if (empty($rejectionReason)) {
                return response()->json(['success' => false, 'message' => 'Rejection reason is mandatory!']);
            }
            
            // Verify user has Supervisor/Manager role
            $userRole = Auth::user()->u_type ?? '';
            if (!in_array($userRole, ['Supervisor', 'Manager', 'Admin', 'HK Supervisor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Supervisors/Managers can reject inspection.'
                ]);
            }
            
            $hdr = DB::table('hkcleaninghdr')
                ->where('cleaningid', $cleaningId)
                ->where('propertyid', $propertyId)
                ->first();
            
            if (!$hdr) {
                return response()->json(['success' => false, 'message' => 'Cleaning record not found!']);
            }
            
            DB::beginTransaction();
            
            // Update inspection status to Rejected
            DB::table('hkcleaninghdr')
                ->where('cleaningid', $cleaningId)
                ->where('propertyid', $propertyId)
                ->update([
                    'inspectionstatus' => 'Rejected',
                    'inspectionremarks' => $rejectionReason,
                    'inspectiondate' => date('Y-m-d H:i:s'),
                    'inspector' => Auth::user()->name ?? '',
                ]);
            
            // Set room status to RE-CLEAN REQUIRED (use 'D' for dirty/needs reclean)
            DB::table('room_mast')
                ->where('propertyid', $propertyId)
                ->where('rcode', $hdr->roommo)
                ->update([
                    'room_stat' => 'D', // Back to dirty for re-cleaning
                    'u_updatedt' => date('Y-m-d H:i:s'),
                ]);
            
            // Update assignment status
            DB::table('hkroomassigns')
                ->where('propertyid', $propertyId)
                ->where('roomno', $hdr->roommo)
                ->where('vdate', $hdr->cleaningdate)
                ->update([
                    'cleaningstatus' => 'In Progress', // Reset to In Progress
                    'status' => 'dirty',
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Inspection rejected. Room marked for re-cleaning. Reason: ' . $rejectionReason
            ]);
            
        } catch (\\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting inspection: ' . $e->getMessage()
            ], 500);
        }
    }

    // Step 7: Approve inspection and set room to Clean"""

new_good = """                }

                // Step 7: Approve inspection and set room to Clean"""

if old_bad in content:
    content = content.replace(old_bad, new_good)
    print("Removed incorrectly placed rejectinspection method")
else:
    print("WARNING: Could not find bad code to remove")

# Now add the rejectinspection method BEFORE the saveAssignmentReport method
old_insert_point = "public function saveAssignmentReport(Request $request)"

reject_method = """// ═══════════════════════════════════════════════════════════════
    // FIX #3: INSPECTION REJECTION METHOD
    // ═══════════════════════════════════════════════════════════════
    
    public function rejectinspection(Request $request)
    {
        try {
            $propertyId = $this->propertyid;
            $cleaningId = $request->input('cleaning_id');
            $rejectionReason = $request->input('rejection_reason', '');
            
            if (empty($cleaningId)) {
                return response()->json(['success' => false, 'message' => 'Cleaning ID required!']);
            }
            
            if (empty($rejectionReason)) {
                return response()->json(['success' => false, 'message' => 'Rejection reason is mandatory!']);
            }
            
            // Verify user has Supervisor/Manager role
            $userRole = Auth::user()->u_type ?? '';
            if (!in_array($userRole, ['Supervisor', 'Manager', 'Admin', 'HK Supervisor'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Only Supervisors/Managers can reject inspection.'
                ]);
            }
            
            $hdr = DB::table('hkcleaninghdr')
                ->where('cleaningid', $cleaningId)
                ->where('propertyid', $propertyId)
                ->first();
            
            if (!$hdr) {
                return response()->json(['success' => false, 'message' => 'Cleaning record not found!']);
            }
            
            DB::beginTransaction();
            
            // Update inspection status to Rejected
            DB::table('hkcleaninghdr')
                ->where('cleaningid', $cleaningId)
                ->where('propertyid', $propertyId)
                ->update([
                    'inspectionstatus' => 'Rejected',
                    'inspectionremarks' => $rejectionReason,
                    'inspectiondate' => date('Y-m-d H:i:s'),
                    'inspector' => Auth::user()->name ?? '',
                ]);
            
            // Set room status to RE-CLEAN REQUIRED (use 'D' for dirty/needs reclean)
            DB::table('room_mast')
                ->where('propertyid', $propertyId)
                ->where('rcode', $hdr->roommo)
                ->update([
                    'room_stat' => 'D', // Back to dirty for re-cleaning
                    'u_updatedt' => date('Y-m-d H:i:s'),
                ]);
            
            // Update assignment status
            DB::table('hkroomassigns')
                ->where('propertyid', $propertyId)
                ->where('roomno', $hdr->roommo)
                ->where('vdate', $hdr->cleaningdate)
                ->update([
                    'cleaningstatus' => 'In Progress', // Reset to In Progress
                    'status' => 'dirty',
                ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Inspection rejected. Room marked for re-cleaning. Reason: ' . $rejectionReason
            ]);
            
        } catch (\\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error rejecting inspection: ' . $e->getMessage()
            ], 500);
        }
    }

    """

if old_insert_point in content and reject_method.strip() not in content:
    content = content.replace(old_insert_point, reject_method + old_insert_point)
    print("Added rejectinspection method in correct location")

# Write back
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"New size: {len(content)} chars")
print("HouseKeeping.php fixed successfully!")
