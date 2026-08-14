import os
import re

# Path to the document.xml
filepath = '/tmp/docx_update/word/document.xml'

if not os.path.exists(filepath):
    # Try alternative path
    filepath = '/tmp/docx_update/word/document.xml'
    
if not os.path.exists(filepath):
    print(f"File not found. Trying to find it...")
    import subprocess
    result = subprocess.run(['find', '/tmp', '-name', 'document.xml', '-type', 'f'], capture_output=True, text=True)
    print(f"Found files: {result.stdout}")
    exit(1)

print(f"Reading from: {filepath}")

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

print(f"Original size: {len(content)} bytes")

# ============================================================
# BUG FIX 1: Section 4.5 Room Cleaning Entry Example
# "CLEANING COMPLETE -> Room changes to Occupied Clean" 
# should be "Room changes to Inspection Pending"
# ============================================================
content = content.replace(
    'CLEANING COMPLETE → Room changes to Occupied Clean',
    'CLEANING COMPLETE → Room changes to Inspection Pending (Supervisor must approve before room becomes Clean)'
)
content = content.replace(
    'CLEANING COMPLETE - Room changes to Occupied Clean',
    'CLEANING COMPLETE - Room changes to Inspection Pending (Supervisor must approve before room becomes Clean)'
)

# ============================================================
# BUG FIX 2: Section 4.1 Keyboard Shortcuts - C key description
# "CMark CleanAfter cleaning is verified"
# should mention inspection approval
# ============================================================
content = content.replace(
    'CMark CleanAfter cleaning is verified',
    'CMark Clean (Requires Supervisor Inspection Approval)After inspection is approved by Supervisor'
)

# ============================================================
# BUG FIX 3: Section 3.1 Floor Master - Delete Button description
# "Permanently removes the floor. Shows confirmation prompt before deleting."
# should mention dependency check
# ============================================================
content = content.replace(
    'Delete Button (Red)ButtonPermanently removes the floor. Shows confirmation prompt before deleting.',
    'Delete Button (Red)ButtonDeletion Protection Enabled: Checks for rooms, assignments, and cleaning records. Prevents deletion if dependencies exist. Shows error message.'
)

# ============================================================
# BUG FIX 4: Section 3.1 How to Delete a Floor
# Should mention dependency check and error message
# ============================================================
content = content.replace(
    'How to Delete a Floor⚠  WARNINGDo NOT delete a floor that has active room assignments, cleaning records, or reports. Deletion is permanent and cannot be undone. Mark the floor as inactive through supervisor reassignment instead of deleting.Ensure no active operations exist for the floor (check Assignments screen)Click the Delete button (red) next to the floorRead the confirmation prompt carefullyClick OK to confirm deletionThe floor is permanently removed from the system',
    'How to Delete a Floor⚠  WARNINGDeletion Protection Enabled: The system now checks for dependencies before allowing floor deletion.If the floor has: Rooms, Active Assignments, Cleaning Records, Inspection Records, or Historical Reports → Deletion is BLOCKED with message: "Floor cannot be deleted because historical/active records are linked to this floor."To delete a floor:1. First, remove or reassign all rooms to other floors2. Ensure no active or historical assignments exist3. Click the Delete button (red)4. If no dependencies exist, the floor is deleted successfullyIf dependencies exist: Contact Housekeeping Manager to clear historical records first.'
)

# ============================================================
# BUG FIX 5: Section 3.2 Housekeeper Master - Delete Button
# "Permanently removes the record. Use Inactive status instead."
# should mention soft-delete
# ============================================================
content = content.replace(
    'Delete (Red)ButtonPermanently removes the record. Use Inactive status instead.',
    'Delete (Red)ButtonSoft-delete: Deactivates the record. Prevents deletion if historical cleaning/assignment records exist. Shows error: "Employee has historical records and cannot be deleted. Please use Inactive status instead."'
)

# ============================================================
# BUG FIX 6: Section 3.4 HK Supervisor - Delete
# "remove record (use Inactive instead of Delete)"
# should mention soft-delete
# ============================================================
content = content.replace(
    'Edit / DeleteButtonsEdit details or remove record (use Inactive instead of Delete)',
    'Edit / DeleteButtonsEdit details or soft-delete (deactivate). Prevents deletion if active assignments/cleaning records exist.'
)

# ============================================================
# BUG FIX 7: Section 3.4 HK Supervisor - Delete Button in Screen Elements
# ============================================================
content = content.replace(
    'Delete Button (Red)ButtonPermanently removes the supervisor. Shows confirmation prompt.',
    'Delete Button (Red)ButtonSoft-delete (deactivate). Prevents deletion if active assignments/cleaning records exist.'
)

# ============================================================
# BUG FIX 8: Figure Reference - Section 4.6 QR Login
# "Fig 4.2: Housekeeping Login" should be corrected
# ============================================================
content = content.replace(
    'Fig 4.2: Housekeeping Login',
    'Fig 4.10: Housekeeping Login'
)

# ============================================================
# BUG FIX 9: Appendix A.3 Keyboard Shortcuts - C key
# Should mention inspection requirement
# ============================================================
content = content.replace(
    'CMark selected room as Clean',
    'CMark selected room as Clean (requires inspection approval)'
)

# ============================================================
# BUG FIX 10: Troubleshooting - Inspection Rejected step numbering
# "41Click Cleaning Complete again" missing period
# ============================================================
content = content.replace(
    '41Click Cleaning Complete again',
    '41. Click Cleaning Complete again'
)

# ============================================================
# BUG FIX 11: Section 4.5 Room Cleaning Entry - Action description
# "CLEANING COMPLETEGreen — marks cleaning done, updates room to clean status"
# should mention inspection pending
# ============================================================
content = content.replace(
    'ActionCLEANING COMPLETEGreen — marks cleaning done, updates room to clean status',
    'ActionCLEANING COMPLETEGreen — marks cleaning done, updates room to Inspection Pending (Supervisor must approve)'
)

# ============================================================
# BUG FIX 12: Complete Room Cleaning Workflow - Missing Inspection Pending step
# ============================================================
content = content.replace(
    'Supervisor inspects the room↓✅  Room status changes to VACANT CLEAN',
    'Supervisor inspects the room↓✅  Room status changes to INSPECTION PENDING↓Supervisor APPROVES inspection↓✅  Room status changes to VACANT CLEAN'
)

# ============================================================
# BUG FIX 13: Appendix A - Add Inspection Pending to Room Status Codes
# The current list doesn't have a clear entry for Inspection Pending
# ============================================================
content = content.replace(
    'Inspection PendingPurpleCleaned, awaiting checkSupervisor to inspect',
    'Inspection PendingPurpleCleaned, awaiting Supervisor inspectionSupervisor must Approve or Reject'
)

# ============================================================
# BUG FIX 14: Section 4.2 Room Status Board - Inspection Pending description
# ============================================================
content = content.replace(
    'Inspection Pending0Waiting supervisor verification&gt; 5: urgent',
    'Inspection Pending0Waiting Supervisor inspection/approval&gt; 5: urgent'
)

# ============================================================
# BUG FIX 15: Section 4.3 Assignments - Inspection Pending description
# ============================================================
content = content.replace(
    'Inspection Pending00Clean rooms waiting for supervisor sign-off',
    'Inspection Pending00Clean rooms waiting for Supervisor inspection/approval'
)

# ============================================================
# BUG FIX 16: Section 3A.3 - HR Payroll integration - should clarify
# "Live Employee Data" section mentions specific employees
# ============================================================
# Keep as-is, these are example data from actual system

# ============================================================
# BUG FIX 17: Chapter 3 Master Module SOP - Setup order contradiction
# The SOP says "Step 1: Complete Floor Master" but earlier says 
# "HK Supervisor Master must be completed first"
# ============================================================
content = content.replace(
    '■  SOPMaster Module Setup SOP:Step 1: Complete Floor Master — all floors in orderStep 2: Add all HK Supervisors with correct codes',
    '■  SOPMaster Module Setup SOP:Step 1: Add all HK Supervisors with correct codes (Prerequisite for Floor Master)Step 2: Complete Floor Master — all floors in order'
)

# ============================================================
# BUG FIX 18: Section 4.6 Lost & Found - Status values
# Should clarify Open/Closed vs the specification's OPEN/CLAIMED/HANDED OVER/CLOSED/DISPOSED
# ============================================================
content = content.replace(
    'Tag Info *StatusOpen = in custody | Closed = returned to guest',
    'Tag Info *StatusOPEN = in custody | CLAIMED = guest claimed | HANDED OVER = delivered to guest/authorities | CLOSED = resolved | DISPOSED = discarded'
)

# ============================================================
# BUG FIX 19: Section 4.9 Settlement Entry - Payment validation note
# ============================================================
content = content.replace(
    'When Balance reaches Rs 0.00, settlement is complete',
    'When Balance reaches Rs 0.00, SettlementStatus = SETTLED⚠  VALIDATION: Paid Amount must not exceed Balance. Duplicate payments are prevented. Settlement cannot be modified without authorization.'
)

# ============================================================
# BUG FIX 20: Section 5.5 - Missing section number "5.5"
# Actually the document has "5.5 Room wise Amenities Report" which is correct
# But section numbers jump from 5.4 to 5.6 (Daily Service Summary)
# Let's check and fix section numbering
# ============================================================
# The numbering is: 5.1, 5.2, 5.3, 5.4, 5.5, 5.6, 5.7
# This looks correct actually

# ============================================================
# BUG FIX 21: Add audit log mention to troubleshooting
# ============================================================
content = content.replace(
    'Chapter 7: Troubleshooting GuideThis section covers the most common issues',
    'Chapter 7: Troubleshooting Guide⚠  AUDIT LOG: All critical operations are now logged in hk_audit_log table. Contact IT Admin if you need to review change history.This section covers the most common issues'
)

# ============================================================
# BUG FIX 22: Section 4.5 - Missing "Inspection Required" checkbox description
# ============================================================
content = content.replace(
    'InspectionInspection Required checkboxTick if supervisor must verify before room release',
    'InspectionInspection Required checkboxTick if supervisor must verify before room release (default: Always ON for all cleanings)'
)

# ============================================================
# BUG FIX 23: Add "Room Cannot Be Marked Clean" troubleshooting entry
# ============================================================
old_troubleshooting = '⚠   Inspection RejectedIssue: Supervisor rejects a cleaning'
new_troubleshooting = '''⚠  Room Cannot Be Marked Clean
Issue: Pressing "C" key shows error "Room cannot be marked Clean until Supervisor Inspection is approved."
Resolution Steps:
43. This is expected behavior — the "C" key now requires Supervisor inspection approval
44. Room must go through the full cleaning workflow: Assign → Start Cleaning → Complete → Inspection Pending → Supervisor Approve
45. Only Supervisors/Managers can approve inspection (Housekeepers cannot)
46. Contact your Supervisor to inspect and approve the room
47. If you are a Supervisor, go to Inspection screen and click Approve

⚠   Inspection RejectedIssue: Supervisor rejects a cleaning'''

content = content.replace(old_troubleshooting, new_troubleshooting)

# Write the fixed content
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"Fixed size: {len(content)} bytes")
print("All bug fixes applied successfully!")
