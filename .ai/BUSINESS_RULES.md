# Analysis HMS - Business Rules

## Business Rules Overview

This document defines business rules for the Analysis HMS hotel management system.

---

## Reservation System

### Rule 1: Reservation Creation
- **Condition**: Guest wants to book a room
- **Action**: Create reservation with guest details
- **Validation**: 
  - Arrival date must be today or future
  - Departure date must be after arrival
  - Room must be available
- **Output**: Reservation confirmation with booking number

### Rule 2: Room Availability
- **Condition**: Checking room availability
- **Action**: Check room status for requested dates
- **Logic**:
  - Room must be 'VACANT' for requested dates
  - No overlapping bookings
  - Room not blocked out
- **Output**: Available rooms list

### Rule 3: Reservation Modification
- **Condition**: Guest wants to change reservation
- **Action**: Update reservation details
- **Validation**:
  - New dates must be available
  - Room changes must be available
  - Rate changes must be approved
- **Output**: Updated reservation confirmation

### Rule 4: Reservation Cancellation
- **Condition**: Guest wants to cancel reservation
- **Action**: Cancel reservation and release room
- **Validation**:
  - Check cancellation policy
  - Calculate cancellation charges if applicable
  - Release room availability
- **Output**: Cancellation confirmation

---

## Check-In Process

### Rule 5: Guest Check-In
- **Condition**: Guest arrives at hotel
- **Action**: Process check-in
- **Steps**:
  1. Verify reservation
  2. Collect guest ID
  3. Collect advance payment
  4. Assign room
  5. Generate room key
  6. Update room status to 'OCCUPIED'
  7. Create guest folio
- **Output**: Check-in confirmation, room key

### Rule 6: Walk-In Guest
- **Condition**: Guest without reservation arrives
- **Action**: Process walk-in
- **Steps**:
  1. Check room availability
  2. Collect guest details
  3. Collect advance payment
  4. Assign room
  5. Generate room key
  6. Update room status to 'OCCUPIED'
  7. Create guest folio
- **Output**: Check-in confirmation, room key

### Rule 7: Group Check-In
- **Condition**: Group arrives together
- **Action**: Process group check-in
- **Steps**:
  1. Verify group reservation
  2. Collect group leader ID
  3. Collect advance payment
  4. Assign rooms to group
  5. Generate room keys
  6. Update room statuses
  7. Create group folio
- **Output**: Group check-in confirmation, room keys

---

## Check-Out Process

### Rule 8: Guest Check-Out
- **Condition**: Guest wants to check out
- **Action**: Process check-out
- **Steps**:
  1. Collect room key
  2. Verify room condition
  3. Calculate final bill
  4. Process payment
  5. Generate invoice
  6. Update room status to 'DIRTY'
  7. Close guest folio
- **Output**: Invoice, receipt

### Rule 9: Late Check-Out
- **Condition**: Guest requests late check-out
- **Action**: Process late check-out
- **Validation**:
  - Check availability for late check-out
  - Apply late check-out charges if applicable
- **Output**: Updated check-out time, additional charges

### Rule 10: Early Check-Out
- **Condition**: Guest wants to check out early
- **Action**: Process early check-out
- **Validation**:
  - Calculate early check-out charges if applicable
  - Process refund if applicable
- **Output**: Updated bill, refund if applicable

---

## Room Management

### Rule 11: Room Status Updates
- **Condition**: Room status changes
- **Action**: Update room status
- **Statuses**:
  - VACANT: Room is empty and ready
  - OCCUPIED: Room has guest
  - DIRTY: Room needs cleaning
  - CLEAN: Room is clean
  - INSPECTION: Room is being inspected
  - OUT_OF_ORDER: Room is not available
- **Output**: Updated room status

### Rule 12: Room Assignment
- **Condition**: Assigning room to guest
- **Action**: Assign room
- **Validation**:
  - Room must be available
  - Room category must match request
  - Room must be in good condition
- **Output**: Room assignment confirmation

### Rule 13: Room Transfer
- **Condition**: Guest wants to change room
- **Action**: Transfer guest to new room
- **Validation**:
  - New room must be available
  - Calculate rate difference if applicable
  - Update all bookings
- **Output**: Room transfer confirmation

---

## Point of Sale (POS)

### Rule 14: KOT Creation
- **Condition**: Guest orders items
- **Action**: Create Kitchen Order Ticket (KOT)
- **Steps**:
  1. Select items
  2. Add quantities
  3. Assign table/room
  4. Send to kitchen
- **Output**: KOT number, estimated time

### Rule 15: Bill Generation
- **Condition**: Guest wants to pay
- **Action**: Generate bill
- **Steps**:
  1. Calculate total
  2. Apply taxes
  3. Apply discounts if applicable
  4. Generate invoice
- **Output**: Invoice with total amount

### Rule 16: Payment Processing
- **Condition**: Guest makes payment
- **Action**: Process payment
- **Methods**:
  - Cash
  - Credit/Debit Card
  - Room Charge
  - UPI
  - Online
- **Output**: Payment receipt

---

## Banquet Management

### Rule 17: Hall Booking
- **Condition**: Customer wants to book hall
- **Action**: Create hall booking
- **Steps**:
  1. Check hall availability
  2. Collect event details
  3. Calculate estimate
  4. Collect advance payment
  5. Confirm booking
- **Output**: Booking confirmation

### Rule 18: Function Sheet
- **Condition**: Event is scheduled
- **Action**: Create function sheet
- **Details**:
  - Event name and type
  - Date and time
  - Number of guests
  - Menu selection
  - Special requirements
- **Output**: Function sheet

---

## Inventory Management

### Rule 19: Stock Receipt
- **Condition**: Items received from supplier
- **Action**: Record stock receipt
- **Steps**:
  1. Verify items against purchase order
  2. Check quality
  3. Update stock levels
  4. Generate receipt
- **Output**: Stock receipt

### Rule 20: Stock Issue
- **Condition**: Items needed for operations
- **Action**: Issue stock
- **Steps**:
  1. Create indent
  2. Approve indent
  3. Issue items
  4. Update stock levels
- **Output**: Stock issue slip

### Rule 21: Stock Adjustment
- **Condition**: Stock count differs from system
- **Action**: Adjust stock
- **Validation**:
  - Requires approval
  - Must document reason
- **Output**: Stock adjustment report

---

## Housekeeping

### Rule 22: Room Cleaning
- **Condition**: Room needs cleaning
- **Action**: Assign cleaning
- **Steps**:
  1. Assign housekeeper
  2. Provide checklist
  3. Complete cleaning
  4. Inspect room
  5. Update status to 'CLEAN'
- **Output**: Cleaning completion report

### Rule 23: Room Inspection
- **Condition**: Room cleaned and ready for inspection
- **Action**: Inspect room
- **Steps**:
  1. Check cleanliness
  2. Check amenities
  3. Check maintenance
  4. Approve or reject
- **Output**: Inspection report

### Rule 24: Damage Report
- **Condition**: Damage found in room
- **Action**: Report damage
- **Steps**:
  1. Document damage
  2. Take photos
  3. Calculate charges
  4. Bill guest if applicable
- **Output**: Damage report, invoice if applicable

---

## Finance

### Rule 25: Revenue Posting
- **Condition**: Revenue needs to be recorded
- **Action**: Post revenue
- **Steps**:
  1. Identify revenue type
  2. Calculate amount
  3. Post to ledger
  4. Update accounts
- **Output**: Revenue entry

### Rule 26: Payment Recording
- **Condition**: Payment received
- **Action**: Record payment
- **Steps**:
  1. Identify payment method
  2. Record amount
  3. Update ledger
  4. Generate receipt
- **Output**: Payment receipt

### Rule 27: Night Audit
- **Condition**: End of business day
- **Action**: Process night audit
- **Steps**:
  1. Post room charges
  2. Post taxes
  3. Reconcile accounts
  4. Generate daily report
  5. Close day
- **Output**: Daily audit report

---

## GST Compliance

### Rule 28: GST Calculation
- **Condition**: Taxable supply made
- **Action**: Calculate GST
- **Rates**:
  - CGST: 9%
  - SGST: 9%
  - IGST: 18%
- **Output**: GST amount

### Rule 29: GST Invoice
- **Condition**: Sale made
- **Action**: Generate GST invoice
- **Details**:
  - Invoice number
  - Customer details
  - Item details with HSN
  - Tax amounts
  - Total with tax
- **Output**: GST invoice

### Rule 30: GST Return
- **Condition**: Tax period ends
- **Action**: File GST return
- **Steps**:
  1. Compile sales data
  2. Calculate tax liability
  3. File return
  4. Pay tax
- **Output**: GST return acknowledgment

---

## Reports

### Rule 31: Daily Report
- **Condition**: End of day
- **Action**: Generate daily report
- **Details**:
  - Room occupancy
  - Revenue summary
  - Guest statistics
  - Outstanding payments
- **Output**: Daily report

### Rule 32: Monthly Report
- **Condition**: End of month
- **Action**: Generate monthly report
- **Details**:
  - Revenue analysis
  - Occupancy trends
  - Guest demographics
  - Financial summary
- **Output**: Monthly report

---

## User Management

### Rule 33: User Creation
- **Condition**: New user needs access
- **Action**: Create user account
- **Steps**:
  1. Collect user details
  2. Assign role
  3. Set permissions
  4. Send credentials
- **Output**: User account

### Rule 34: Role-Based Access
- **Condition**: User accesses system
- **Action**: Check permissions
- **Roles**:
  - Admin: Full access
  - Manager: Department access
  - Staff: Limited access
  - Viewer: Read-only access
- **Output**: Access granted/denied

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
