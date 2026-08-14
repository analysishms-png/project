import re
import os

filepath = 'C:/xampp/htdocs/analysishms-master/app/Http/Controllers/HouseKeeping.php'

print(f"Reading from: {filepath}")
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

print(f"Original size: {len(content)} chars")
fixes = []

# ═══════════════════════════════════════════════════════════════
# FIX 1: Duplicate Assignment Prevention
# Add validation before DB::transaction
# ═══════════════════════════════════════════════════════════════

old1 = """public function saveAssignmentReport(Request $request)
    {
        $propertyId = $this->propertyid;
        $asOnDate = getNcurDate();
        $assignments = json_decode($request->input('assignments', '[]'), true);
        $userName = Auth::user()->name ?? '';
        $vtime = date('H:i:s');

        DB::transaction(function () use ($propertyId, $asOnDate, $assignments, $userName, $vtime) {"""

new1 = """public function saveAssignmentReport(Request $request)
    {
        $propertyId = $this->propertyid;
        $asOnDate = getNcurDate();
        $assignments = json_decode($request->input('assignments', '[]'), true);
        $userName = Auth::user()->name ?? '';
        $vtime = date('H:i:s');

        // CRITICAL FIX #1: Prevent duplicate room assignment for same date
        foreach ($assignments as $hk) {
            if (empty($hk['scode']) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;
            foreach ($hk['rooms'] as $room) {
                if (empty($room['roomno'])) continue;
                
                $existingAssignment = DB::table('hkroomassigns')
                    ->where('propertyid', $propertyId)
                    ->where('roomno', $room['roomno'])
                    ->where('vdate', $asOnDate)
                    ->where('status', 'dirty')
                    ->first();
                
                if ($existingAssignment) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Room ' . $room['roomno'] . ' is already assigned for this date! Cannot assign the same room twice.'
                    ]);
                }
            }
        }

        // CRITICAL FIX #2: Prevent assigning Out of Order / Maintenance rooms
        foreach ($assignments as $hk) {
            if (empty($hk['scode']) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;
            foreach ($hk['rooms'] as $room) {
                if (empty($room['roomno'])) continue;
                
                $roomStatus = DB::table('room_mast')
                    ->where('propertyid', $propertyId)
                    ->where('rcode', $room['roomno'])
                    ->value('room_stat');
                
                if (in_array($roomStatus, ['X', 'M'])) { // X=OOO, M=Maintenance
                    return response()->json([
                        'success' => false,
                        'message' => 'Room ' . $room['roomno'] . ' is Out of Order/Maintenance and cannot be assigned!'
                    ]);
                }
            }
        }

        DB::transaction(function () use ($propertyId, $asOnDate, $assignments, $userName, $vtime) {"""

if old1 in content:
    content = content.replace(old1, new1)
    fixes.append("FIX 1: Added duplicate assignment and OOO/Maintenance room prevention")
else:
    print("WARNING: FIX 1 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 2: RBAC for Inspection Approval
# Add role validation in savehousecleaning
# ═══════════════════════════════════════════════════════════════

old2 = """'message' => 'Only Supervisors/Managers can approve inspection.',"""

new2 = """// CRITICAL FIX #2: Verify user has Supervisor/Manager/Admin role
                    $userRole = Auth::user()->u_type ?? '';
                    if (!in_array($userRole, ['Supervisor', 'Manager', 'Admin', 'HK Supervisor', 'Housekeeping Manager'])) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Only Supervisors/Managers can approve inspection. Your role: ' . $userRole
                        ]);
                    }
                    'message' => 'Only Supervisors/Managers can approve inspection.',"""

if old2 in content:
    content = content.replace(old2, new2)
    fixes.append("FIX 2: Added RBAC validation for inspection approval")
else:
    print("WARNING: FIX 2 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 3: Inspection Rejection Workflow
# Add rejection method with reason
# ═══════════════════════════════════════════════════════════════

# Find a good place to insert the rejection method - after the approval logic
old3_insert = """// Step 7: Approve inspection and set room to Clean"""

new3_insert = """// FIX #3: Inspection Rejection Method
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

if old3_insert in content:
    content = content.replace(old3_insert, new3_insert)
    fixes.append("FIX 3: Added inspection rejection workflow with mandatory reason")
else:
    print("WARNING: FIX 3 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 4: Before Photo Frontend Validation
# Add validation in submitstartcleaning
# ═══════════════════════════════════════════════════════════════

old4 = """public function submitstartcleaning(Request $request)
    {"""

new4 = """public function submitstartcleaning(Request $request)
    {
        // CRITICAL FIX #4: Validate before photo is mandatory
        if (!$request->hasFile('before_photo')) {
            return response()->json([
                'success' => false,
                'message' => 'Before Photo is mandatory! Please take a photo before starting cleaning.'
            ]);
        }
        
        // Validate file size (max 2MB)
        $file = $request->file('before_photo');
        if ($file && $file->getSize() > 2 * 1024 * 1024) {
            return response()->json([
                'success' => false,
                'message' => 'Photo size must be less than 2MB!'
            ]);
        }"""

if old4 in content:
    content = content.replace(old4, new4)
    fixes.append("FIX 4: Added before photo validation with size check")
else:
    print("WARNING: FIX 4 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 6: Fix duplicate HK Supervisor in role table (Manual)
# This requires docx modification - skip for now
# ═══════════════════════════════════════════════════════════════

# ═══════════════════════════════════════════════════════════════
# FIX 8: QR Authorization Validation
# Add validation in housekeepingscreen for QR login
# ═══════════════════════════════════════════════════════════════

old8 = """public function housekeepingscreen(Request $request)
    {"""

new8 = """public function housekeepingscreen(Request $request)
    {
        // CRITICAL FIX #8: Validate QR room authorization
        $qrRoom = $request->input('qr_room');
        $qrUser = $request->input('qr_user');
        
        if (!empty($qrRoom) && !empty($qrUser)) {
            // Verify user is assigned to this room
            $isAssigned = DB::table('hkroomassigns')
                ->where('propertyid', $this->propertyid)
                ->where('roomno', $qrRoom)
                ->where('code', $qrUser)
                ->where('vdate', date('Y-m-d'))
                ->where('status', 'dirty')
                ->exists();
            
            if (!$isAssigned) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not assigned to room ' . $qrRoom . '. Access denied.'
                ], 403);
            }
        }"""

if old8 in content:
    content = content.replace(old8, new8)
    fixes.append("FIX 8: Added QR room authorization validation")
else:
    print("WARNING: FIX 8 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 10: Negative Quantity Validation for Amenities
# Add validation in submitcleaningentry
# ═══════════════════════════════════════════════════════════════

old10 = """$amenities = json_decode($request->input('amenities', '[]'), true) ?: [];
            foreach ($amenities as $am) {
                $rawQty = str_replace(',', '.', (string)($am['qty'] ?? ''));
                $qty    = is_numeric($rawQty) ? (float)$rawQty : null;
                if (!$qty || $qty <= 0) continue; // skip zero/invalid amenity rows"""

new10 = """$amenities = json_decode($request->input('amenities', '[]'), true) ?: [];
            foreach ($amenities as $am) {
                $rawQty = str_replace(',', '.', (string)($am['qty'] ?? ''));
                $qty    = is_numeric($rawQty) ? (float)$rawQty : null;
                
                // CRITICAL FIX #10: Prevent negative quantities
                if ($qty !== null && $qty < 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Amenity quantity cannot be negative! Item: ' . ($am['itemcode'] ?? 'Unknown')
                    ]);
                }
                
                if (!$qty || $qty <= 0) continue; // skip zero/invalid amenity rows"""

if old10 in content:
    content = content.replace(old10, new10)
    fixes.append("FIX 10: Added negative quantity validation for amenities")
else:
    print("WARNING: FIX 10 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 9: Auto-save hint comment (frontend implementation needed)
# Add comment for frontend implementation
# ═══════════════════════════════════════════════════════════════

old9 = """// ── HKISS Stock Voucher"""

new9 = """// ═══════════════════════════════════════════════════════════════
// FIX #9: AUTO-SAVE RECOMMENDATION
// Frontend should implement auto-save every 30 seconds
// to prevent data loss during long cleaning sessions
// Example: setInterval(() => saveFormData(), 30000);
// ═══════════════════════════════════════════════════════════════

// ── HKISS Stock Voucher"""

if old9 in content:
    content = content.replace(old9, new9)
    fixes.append("FIX 9: Added auto-save recommendation comment")
else:
    print("WARNING: FIX 9 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 5: Laundry Billing Validation (add if not exists)
# ═══════════════════════════════════════════════════════════════

# Check if laundry billing validation exists
if 'quantity > 0' not in content and 'qty > 0' not in content:
    # Add validation comment
    laundry_comment = """// FIX #5: LAUNDRY BILLING VALIDATIONS
        // - Quantity must be > 0
        // - Rate must be >= 0
        // - Discount must be >= 0
        // - Payment cannot exceed balance
        // - Cannot modify settled bill"""
    
    # Find laundry memo method and add validation
    old5_pattern = "public function storelaundrymemo"
    if old5_pattern in content:
        content = content.replace(old5_pattern, laundry_comment + "\n    " + old5_pattern)
        fixes.append("FIX 5: Added laundry billing validation comments")
    else:
        print("WARNING: FIX 5 - Laundry method not found, skipping")

# ═══════════════════════════════════════════════════════════════
# FIX 7: Audit Logging Integration
# Add audit log helper method
# ═══════════════════════════════════════════════════════════════

old7 = """// ─── Room Status Board"""

new7 = """// ═══════════════════════════════════════════════════════════════
    // FIX #7: AUDIT LOG HELPER METHOD
    // ═══════════════════════════════════════════════════════════════
    
    private function auditLog($action, $module, $recordId, $oldValue = null, $newValue = null)
    {
        try {
            DB::table('hk_audit_log')->insert([
                'user_id' => Auth::id(),
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'old_value' => is_array($oldValue) ? json_encode($oldValue) : $oldValue,
                'new_value' => is_array($newValue) ? json_encode($newValue) : $newValue,
                'created_at' => now(),
            ]);
        } catch (\\Exception $e) {
            // Silently fail - audit log should not break main functionality
            Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }

    // ─── Room Status Board"""

if old7 in content:
    content = content.replace(old7, new7)
    fixes.append("FIX 7: Added audit log helper method")
else:
    print("WARNING: FIX 7 not found")

# ═══════════════════════════════════════════════════════════════
# Write the fixed content
# ═══════════════════════════════════════════════════════════════
print(f"\nNew size: {len(content)} chars")
print(f"\nFixes applied: {len(fixes)}")
for i, fix in enumerate(fixes, 1):
    print(f"  {i}. {fix}")

# Write back
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("\nHouseKeeping.php updated successfully!")
