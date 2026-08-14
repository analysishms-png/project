import re

filepath = 'C:/xampp/htdocs/analysishms-master/app/Http/Controllers/HouseKeeping.php'

print(f"Reading from: {filepath}")
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

print(f"Original size: {len(content)} chars")
fixes = []

# ═══════════════════════════════════════════════════════════════
# FIX 1: Duplicate Assignment Prevention
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

        // FIX #1: Prevent duplicate room assignment for same date
        foreach ($assignments as $hk) {
            if (empty($hk['scode']) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;
            foreach ($hk['rooms'] as $room) {
                if (empty($room['roomno'])) continue;
                $exists = DB::table('hkroomassigns')
                    ->where('propertyid', $propertyId)
                    ->where('roomno', $room['roomno'])
                    ->where('vdate', $asOnDate)
                    ->where('status', 'dirty')
                    ->exists();
                if ($exists) {
                    return response()->json(['success' => false, 'message' => 'Room ' . $room['roomno'] . ' already assigned!']);
                }
            }
        }

        // FIX #1b: Prevent assigning OOO/Maintenance rooms
        foreach ($assignments as $hk) {
            if (empty($hk['scode']) || empty($hk['rooms']) || !is_array($hk['rooms'])) continue;
            foreach ($hk['rooms'] as $room) {
                if (empty($room['roomno'])) continue;
                $roomStat = DB::table('room_mast')->where('propertyid', $propertyId)->where('rcode', $room['roomno'])->value('room_stat');
                if (in_array($roomStat, ['X', 'M'])) {
                    return response()->json(['success' => false, 'message' => 'Room ' . $room['roomno'] . ' is OOO/Maintenance!']);
                }
            }
        }

        DB::transaction(function () use ($propertyId, $asOnDate, $assignments, $userName, $vtime) {"""

if old1 in content:
    content = content.replace(old1, new1)
    fixes.append("FIX 1: Duplicate assignment & OOO room prevention")
else:
    print("WARNING: FIX 1 not found")

# ═══════════════════════════════════════════════════════════════
# FIX 2: Room goes to Inspection Pending (I) instead of Clean (C)
# ═══════════════════════════════════════════════════════════════

old2a = "$updateHdr['roomstatusafter'] = 'C';"
new2a = "$updateHdr['roomstatusafter'] = 'I';  // FIX #2: Room goes to Inspection Pending"

if old2a in content:
    content = content.replace(old2a, new2a)
    fixes.append("FIX 2a: roomstatusafter changed to 'I' (Inspection Pending)")

old2b = """DB::table('room_mast')
                    ->where('propertyid', $propertyId)
                    ->where('rcode', $hdr->roommo)
                    ->where('type', 'RO')
                    ->update(['room_stat' => 'C', 'u_updatedt' => $now]);"""

new2b = """DB::table('room_mast')
                    ->where('propertyid', $propertyId)
                    ->where('rcode', $hdr->roommo)
                    ->where('type', 'RO')
                    ->update(['room_stat' => 'I', 'u_updatedt' => $now]);  // FIX #2b: Room goes to Inspection Pending"""

if old2b in content:
    content = content.replace(old2b, new2b)
    fixes.append("FIX 2b: room_mast room_stat changed to 'I'")

# ═══════════════════════════════════════════════════════════════
# FIX 3: Inspection Required defaults to 'Y'
# ═══════════════════════════════════════════════════════════════

old3 = "'inspectionrequired' => $request->input('inspection_required', 'N'),"
new3 = "'inspectionrequired' => $request->input('inspection_required', 'Y'),  // FIX #3: Default to mandatory inspection"

if old3 in content:
    content = content.replace(old3, new3)
    fixes.append("FIX 3: inspectionrequired default changed to 'Y'")

# ═══════════════════════════════════════════════════════════════
# FIX 4: Checklist and After Photo Validation
# ═══════════════════════════════════════════════════════════════

old4 = "$isComplete     = $request->input('action') === 'complete';"
new4 = """$isComplete     = $request->input('action') === 'complete';

            // FIX #4: Validate mandatory fields before cleaning complete
            if ($isComplete) {
                // Validate After Photo
                if (!$request->hasFile('after_photo') && empty($hdr->afterphoto)) {
                    return response()->json(['success' => false, 'message' => 'After Photo is mandatory!']);
                }
                // Validate Checklist
                $checklist = json_decode($request->input('checklist', '[]'), true) ?: [];
                $allItems = DB::table('hkchecklistmast')->where('propertyid', $propertyId)->count();
                if (count($checklist) < $allItems) {
                    return response()->json(['success' => false, 'message' => 'Complete all ' . $allItems . ' checklist items!']);
                }
            }"""

if old4 in content:
    content = content.replace(old4, new4)
    fixes.append("FIX 4: After photo and checklist validation")

# ═══════════════════════════════════════════════════════════════
# FIX 5: Employee Soft-Delete
# ═══════════════════════════════════════════════════════════════

old5_start = "public function deletehousekeepingmaster(Request $request, $sn, $ucode)"
new5_start = """public function deletehousekeepingmaster(Request $request, $sn, $ucode)
    {
        // FIX #5: Check for historical records before delete
        $hasRecords = DB::table('hkroomassigns')->where('propertyid', $this->propertyid)->where('code', $ucode)->exists()
            || DB::table('hkcleaninghdr')->where('propertyid', $this->propertyid)->where('housekeeper', $ucode)->exists();
        if ($hasRecords) {
            return response()->json(['success' => false, 'message' => 'Employee has historical records! Use Inactive status instead.']);
        }
        DB::table('housekeeparmast')->where('propertyid', $this->propertyid)->where('scode', $ucode)->update(['activeYN' => 'N']);
        return response()->json(['success' => true, 'message' => 'Employee deactivated successfully.']);
    }
    
    // Original delete method (kept for reference)
    public function deletehousekeepingmaster_original(Request $request, $sn, $ucode)"""

# Instead of replacing, let's just add the check at the beginning
old5_simple = "public function deletehousekeepingmaster(Request $request, $sn, $ucode)\n    {"
new5_simple = """public function deletehousekeepingmaster(Request $request, $sn, $ucode)
    {
        // FIX #5: Check for historical records before delete
        $hasRecords = DB::table('hkroomassigns')->where('propertyid', $this->propertyid)->where('code', $ucode)->exists()
            || DB::table('hkcleaninghdr')->where('propertyid', $this->propertyid)->where('housekeeper', $ucode)->exists();
        if ($hasRecords) {
            return response()->json(['success' => false, 'message' => 'Employee has historical records! Use Inactive status instead.']);
        }
        // Soft delete - set activeYN to N
        DB::table('housekeeparmast')->where('propertyid', $this->propertyid)->where('scode', $ucode)->update(['activeYN' => 'N']);
        return response()->json(['success' => true, 'message' => 'Employee deactivated successfully.']);
    }
    
    public function deletehousekeepingmaster_original(Request $request, $sn, $ucode)
    {""

if old5_simple in content:
    content = content.replace(old5_simple, new5_simple)
    fixes.append("FIX 5: Employee soft-delete with dependency check")

# ═══════════════════════════════════════════════════════════════
# FIX 6: Floor Delete Protection
# ═══════════════════════════════════════════════════════════════

old6 = "public function deletefloormaster(Request $request)\n    {"
new6 = """public function deletefloormaster(Request $request)
    {
        // FIX #6: Check for rooms/assignments before delete
        $floorId = $request->input('floorid');
        if ($floorId) {
            $hasRooms = DB::table('room_mast')->where('propertyid', $this->propertyid)->where('floor', $floorId)->exists();
            if ($hasRooms) {
                return response()->json(['success' => false, 'message' => 'Floor has rooms! Cannot delete.']);
            }
        }
"""

if old6 in content:
    content = content.replace(old6, new6)
    fixes.append("FIX 6: Floor deletion protection")

# ═══════════════════════════════════════════════════════════════
# FIX 7: Supervisor Delete Protection
# ═══════════════════════════════════════════════════════════════

old7 = "public function deletehksupervisor(Request $request)\n    {"
new7 = """public function deletehksupervisor(Request $request)
    {
        // FIX #7: Check for active assignments before delete
        $supCode = $request->input('scode');
        if ($supCode) {
            $hasAssignments = DB::table('hkroomassigns')->where('propertyid', $this->propertyid)->where('supervisor', $supCode)->where('status', 'dirty')->exists();
            if ($hasAssignments) {
                return response()->json(['success' => false, 'message' => 'Supervisor has active assignments! Cannot delete.']);
            }
        }
        // Soft delete
        $supId = $request->input('id');
        if ($supId) {
            DB::table('hksupervisormast')->where('propertyid', $this->propertyid)->where('id', $supId)->update(['activeyn' => 0]);
            return response()->json(['success' => true, 'message' => 'Supervisor deactivated.']);
        }
"""

if old7 in content:
    content = content.replace(old7, new7)
    fixes.append("FIX 7: Supervisor deletion protection with soft-delete")

# ═══════════════════════════════════════════════════════════════
# FIX 8: Audit Log Helper Method
# ═══════════════════════════════════════════════════════════════

old8 = "// ─── Room Status Board"
new8 = """// FIX #8: Audit Log Helper
    private function auditLog($action, $module, $recordId, $oldVal = null, $newVal = null)
    {
        try {
            DB::table('hk_audit_log')->insert([
                'user_id' => Auth::id(),
                'action' => $action,
                'module' => $module,
                'record_id' => $recordId,
                'old_value' => is_array($oldVal) ? json_encode($oldVal) : $oldVal,
                'new_value' => is_array($newVal) ? json_encode($newVal) : $newVal,
                'created_at' => now(),
            ]);
        } catch (\\Exception $e) {
            Log::warning('Audit log failed: ' . $e->getMessage());
        }
    }

    // ─── Room Status Board"""

if old8 in content:
    content = content.replace(old8, new8)
    fixes.append("FIX 8: Audit log helper method")

# ═══════════════════════════════════════════════════════════════
# FIX 9: Before Photo Validation in Start Cleaning
# ═══════════════════════════════════════════════════════════════

old9 = "public function submitstartcleaning(Request $request)\n    {"
new9 = """public function submitstartcleaning(Request $request)
    {
        // FIX #9: Validate before photo is mandatory
        if (!$request->hasFile('before_photo')) {
            return response()->json(['success' => false, 'message' => 'Before Photo is mandatory!']);
        }
"""

if old9 in content:
    content = content.replace(old9, new9)
    fixes.append("FIX 9: Before photo validation in start cleaning")

# ═══════════════════════════════════════════════════════════════
# FIX 10: Negative Quantity Validation
# ═══════════════════════════════════════════════════════════════

old10 = """$qty    = is_numeric($rawQty) ? (float)$rawQty : null;
                if (!$qty || $qty <= 0) continue; // skip zero/invalid amenity rows"""

new10 = """$qty    = is_numeric($rawQty) ? (float)$rawQty : null;
                // FIX #10: Prevent negative quantities
                if ($qty !== null && $qty < 0) {
                    return response()->json(['success' => false, 'message' => 'Quantity cannot be negative for ' . ($am['itemcode'] ?? 'item')]);
                }
                if (!$qty || $qty <= 0) continue; // skip zero/invalid amenity rows"""

if old10 in content:
    content = content.replace(old10, new10)
    fixes.append("FIX 10: Negative quantity validation")

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
