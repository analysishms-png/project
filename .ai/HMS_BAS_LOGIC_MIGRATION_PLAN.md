# HMS.bas Legacy Logic to Laravel Migration Master Plan (0-Bug Policy)

> **Storage Location**: `C:\xampp\htdocs\analysishms-master\.ai\HMS_BAS_LOGIC_MIGRATION_PLAN.md`  
> **Purpose**: Authoritative blueprint for extracting, understanding, and migrating all business logic, algorithms, and rules from the legacy Visual Basic 6 source code (`.ai/HMS.bas` / `.ai/HMS.text`, ~995,000 lines, 151 forms) into Laravel with **0 errors**.

---

## 1. Core Directives for AI Agents (0-Error Policy)

Every AI agent working on this codebase MUST follow these mandatory rules before taking any implementation decision:

1. **Read Before Editing**:
   - Read `.ai/.rules.md`, `.ai/CODING_RULES.md`, `.ai/DEVELOPMENT_STANDARDS.md`, `.ai/DATABASE_MAP.md`, `.ai/LEGACY_TO_LARAVEL_MAP.md`, and this file BEFORE modifying code.
   - Inspect existing Eloquent models, migration files, routes, and controllers. Never guess column names, table names, or helper function parameters.

2. **Preserve Compatibility & Schema Standards**:
   - Primary and foreign key conventions (`propertyid`, `vdate`, `rcode`, `dcode`) must be strictly respected.
   - Use parameter binding (`?` or named bindings) for all raw SQL queries (`DB::select`, `DB::statement`). Raw string interpolation (`'{$var}'`) is STRICTLY BANNED.
   - All Laravel Eloquent models MUST define explicit `$fillable` arrays. Never use `$guarded = []`.

3. **Transaction Safety & Atomicity**:
   - Multi-step operations involving financial postings, stock adjustments, room status changes, or POS settlements MUST be wrapped in `DB::transaction(function() { ... })`.

4. **Verification & Testing Protocol**:
   - After writing or modifying any code:
     a) Run `php artisan view:cache` to confirm zero Blade compilation errors.
     b) Run test verification scripts (`php scratch/test_routes.php` or `php artisan test`) to confirm HTTP 200 responses.
   - Never declare a task complete without empirical test output verification.

5. **Auto-Memory Documentation**:
   - Update `.ai/CHANGELOG.md`, `.ai/MEMORY.md`, and relevant `.ai/` documentation after every task completion.

---

## 2. HMS.bas Module-by-Module Business Logic Migration Plan

### Module 1: Reservation & Room Blocking Engine
- **Legacy Source**: `HMS.bas` → `FrmBookingInquiry`, `FrmBlock`, `FrmSoftBlock`, `FrmCancel`, `FrmPackage`, `FrmPlanPkg`.
- **Target Laravel Stack**: `Booking`, `GrpBookingDetails`, `PlanMast` models; `CompanyController`, `ReservationController`.
- **Logic to Migrate & Verify**:
  - **Room Blocking Rules**: Support both Hard Block (physically locks room out of inventory) and Soft Block (tentative hold with auto-expiry timestamp).
  - **Package Tariff Breakdown**: Auto-calculate plan charges (`plan1`, `plandetails`) including inclusions (breakfast, airport transfer) vs core room tariff.
  - **Cancellation Fee Calculation**: Calculate refund amounts based on cancellation lead time, retention policy rules, and advance deposit status.

---

### Module 2: Advance Deposit & Folio Reconciliation Engine
- **Legacy Source**: `HMS.bas` → `frmAdvanceDepDialog`, `paycharge`, `paychargelog`.
- **Target Laravel Stack**: `Paycharge`, `PaychargeLog` models; `CompanyController`, `ToolsController`.
- **Logic to Migrate & Verify**:
  - **3-Way Reconciliation Rule**: Ensure zero mismatch between Reservation Advance (`booking.advance`), Folio Advance (`guestfolio.advance`), and PayCharge Log (`paychargelog`).
  - **Advance Transfer at Check-in**: Automatically transition reservation advance into active guest folio credit upon check-in.
  - **Duplicate & Refund Safeguards**: Block advance deletion or double-refund if the payment has already been settled, merged, or posted to ledger.

---

### Module 3: Front Office, Check-in & Guest Folio Management
- **Legacy Source**: `HMS.bas` → `FrmCheckIn`, `FrmRoomChange`, `FrmMergeFolio`, `FrmResettlement`.
- **Target Laravel Stack**: `CheckinRegController`, `GuestFolioService`, `RoomMast` model.
- **Logic to Migrate & Verify**:
  - **Room Status Transition**: Auto-change room status from `Clean` / `Inspected` to `Occupied` on check-in, and `Dirty` on check-out.
  - **Room Change Audit Logging**: Log previous room, new room, reason, timestamp (`ChngTime`), and authorized user ID in `roomchangehistory`.
  - **Folio Split & Merge**: Allow splitting room charges vs extra consumption (POS, laundry) into sub-folios (`Folio A`, `Folio B`).

---

### Module 4: Point of Sale (POS) & Kitchen Order Ticket (KOT) Engine
- **Legacy Source**: `HMS.bas` → `FrmPOS`, `FrmHotKey`, `FrmNCType`, `FrmChangeKitch`, `FrmPOSBillDeletion`, `FrmPOSSaleDataTransfer`.
- **Target Laravel Stack**: `Pointofsale`, `Kot` controllers; `Sale1`, `Sale2`, `Kot`, `Items`, `ItemRate`, `NctypeMast` models.
- **Logic to Migrate & Verify**:
  - **Atomic POS Bill & Stock Deduction**: In a single transaction: insert `sale1` header, insert `sale2` items, update `kot` status, and deduct item recipe ingredients from `stock`.
  - **NC KOT Percentage Deduction**: Calculate non-chargeable KOT discounts using `ncper` rules from `nctype_mast` table.
  - **Kitchen Routing (`FrmChangeKitch`)**: Route KOT items dynamically to specific kitchen printers based on item department code (`dcode`).

---

### Module 5: Suntran Financial Accounts & Ledger Engine
- **Legacy Source**: `HMS.bas` → `FrmACC`, `FrmAdjust`, `FrmMergeCharge`, `FrmRevMergeCharge`, `FrmSerialiseVr`.
- **Target Laravel Stack**: `app/Services/AccountPosting.php`, `Suntran`, `Ledger`, `SubGroup`, `Revmast` models.
- **Logic to Migrate & Verify**:
  - **Double-Entry Balance Validation**: Verify that `SUM(Debit) == SUM(Credit)` before committing any financial voucher (`Vtype`).
  - **Voucher Serialization**: Generate sequential voucher numbers (`VNo`) per financial year and property ID without race conditions.
  - **Immutable Audit Trail**: Prohibit direct hard-deletion of posted ledger entries. Require reversal vouchers (`Vtype = 'REV'`) for corrections.

---

### Module 6: Store, Inventory & Purchase Engine
- **Legacy Source**: `HMS.bas` → `FrmItemMast`, `FrmItemCatMast`, `FrmOPStock`, `FrmStockTransfer`, `FrmPurch`, `FrmItemIssuedOnCleaning`.
- **Target Laravel Stack**: `InventoryController`, `Stock`, `Purch1`, `Purch2`, `Indent`, `Porder`, `Gin`, `ItemMast` models.
- **Logic to Migrate & Verify**:
  - **Stock Transfer Atomicity**: Deduct item quantity from source store and credit destination store in a single atomic transaction.
  - **Weighted Average Valuation**: Recalculate item unit cost upon new Goods Receipt Note (GRN) entry.
  - **Requisition & Indent Approval Flow**: Enforce multi-tier approval before converting store indent to purchase order.

---

### Module 7: Banquet & Event Operations
- **Legacy Source**: `HMS.bas` → `FrmEventMast`, `FrmVenueMast`, `FrmVenueFeat`, `FrmHallItemCatMast`.
- **Target Laravel Stack**: `Banquet` controller; `HallBook`, `HallSale1`, `HallSale2`, `VenueMast`, `VenueOcc` models.
- **Logic to Migrate & Verify**:
  - **Venue Collision Guard**: Prevent double-booking of halls/venues for overlapping time slots on the same date.
  - **Banquet Package & Menu Billing**: Combine venue rental, menu per-pax charge, and extra equipment charges into unified hall bill.

---

### Module 8: Night Audit & Automated Day-End Closing
- **Legacy Source**: `HMS.bas` → `FrmNightAudit`, `FrmNAMessageA/B/C`.
- **Target Laravel Stack**: `NightAuditController`, `NightAuditLog` model.
- **Logic to Migrate & Verify**:
  - **Automated Room Charge Posting**: Post room tariff and applicable GST to active in-house guest folios for the current audit date.
  - **Unposted Transaction Warning**: Block night audit execution if there are un-settled POS bills or un-printed KOTs.
  - **System Date Roll Forward**: Advance property working date (`vdate`) to `vdate + 1 day` upon successful audit completion.

---

### Module 9: Missing Legacy Features Migration Roadmap

| Legacy Feature | Legacy VB6 Form | Target Laravel Implementation Plan | Priority |
|---|---|---|---|
| **Cashier Denomination** | `FrmDenomination` | Create `DenominationDetail` model, table, and modal in Cashier Settlement report. | P1 |
| **Foreign Exchange Register** | `FrmForExRec` | Create `ForEx` model and currency exchange receipt generator. | P2 |
| **Meter Reading Tracker** | `FrmMeterReading` | Add energy/water meter reading form to Maintenance module. | P2 |
| **Guest Wake-Up Calls** | `FrmGuestWakeUp` | Add wake-up call scheduler and alert panel to Front Office dashboard. | P1 |

---

## 3. Step-by-Step AI Execution Instructions

Whenever an AI model is tasked with implementing a feature or migrating logic from `HMS.bas`:

1. **Step 1: Read Plan Files**: Read `.ai/HMS_BAS_LOGIC_MIGRATION_PLAN.md`, `.ai/UI_FULL_REDESIGN_PLAN.md`, and `.ai/.rules.md`.
2. **Step 2: Inspect Source Code**: Locate target models, views, and controllers. Inspect database schema in `.ai/DATABASE_MAP.md`.
3. **Step 3: Write Code with 0-Bug Guards**:
   - Use Eloquent `$fillable` arrays.
   - Wrap SQL in parameter bindings.
   - Use `DB::transaction` for multi-table updates.
   - Use pure Bootstrap 4 utility classes for UI markup.
4. **Step 4: Execute Verification Commands**:
   - Run `php artisan view:cache`
   - Run `php scratch/test_routes.php`
5. **Step 5: Document Changes**: Update `.ai/CHANGELOG.md` and `.ai/MEMORY.md`.
