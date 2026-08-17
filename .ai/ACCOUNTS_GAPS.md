# ACCOUNTS MODULE — GAP ANALYSIS (Laravel vs Legacy HMS)

**Date:** 2026-08-16
**Module:** 12 Accounts / Finance

## 1. Current Laravel implementation

| Area | Files | Status |
|---|---|---|
| Voucher Entry (save/update/delete/print/cheque print) | `Finance/Transaction/VoucherEntry.php` | ✅ COMPLETE |
| Voucher Verification (verify/reject, userwise detail) | `Finance/Transaction/VoucherVerification.php` | ✅ COMPLETE |
| Bank Reconciliation | `Finance/Transaction/BankReconcilation.php` | ✅ COMPLETE |
| Trial Balance | `Finance/FinanceController.php` | ✅ COMPLETE |
| Group Trial | `Finance/FinanceController.php` | ✅ COMPLETE |
| Profit & Loss | `Finance/FinanceController.php` | ✅ COMPLETE |
| Balance Sheet | `Finance/FinanceController.php` | ✅ COMPLETE |
| TDS Report | `Finance/FinanceController.php` | ✅ COMPLETE |
| Detailed Trial Ledger (summary: opening/trans/closing per account) | `Finance/FinanceController.php` | ✅ COMPLETE |
| **General Ledger (transaction-level, running balance)** | — | ❌ was MISSING → **ADDED 2026-08-16** |
| **Day Book (chronological register, vtype filter)** | — | ❌ was MISSING → **ADDED 2026-08-16** |
| **Cash Book / Bank Book (acgroup nature Cash/Bank)** | — | ❌ was MISSING → **ADDED 2026-08-16** |
| Ledger master + group accounts (CRUD, deleteledger guarded) | `CompanyController.php` | ✅ COMPLETE |

## 2. Legacy HMS implementation

Legacy report menu (GRepFormName) for accounts:
- `DayBook` — Day Book
- `CashBook` — Cash Book
- `BankBook` — Bank Book
- `JournalBook` — Journal Book
- `Led` / `LedDeb` / `LedCred` / `LedInt` — General Ledger (all / debit / credit / interest)
- `AgingDr` / `AgingCr` — Aging analysis (debit/credit)
- `DUELIST` — Due List
- `AcCheckList` — Accounts checklist
- `MemLed` — Member ledger
- `DetailedTrial` — Detailed Trial Ledger

Legacy Led query (from HMS.text, Proc_203_75_1ED9BE8):
```
SELECT VIEWLEDGER.*, NameWithCity AS NAME
FROM VIEWLEDGER LEFT JOIN ViewSubgroup ON VIEWLEDGER.PARTY1=ViewSubgroup.SUBCODE
WHERE VIEWLEDGER.PARTY IN (...) AND VIEWLEDGER.V_DATE Between ...
ORDER BY PARTY, V_DATE, CASE WHEN CREDIT>0 THEN 3 WHEN DEBIT>0 THEN 2 ELSE 1 END, DOCID, V_SNo
```
→ Per-account transaction listing with opening balance, running balance, closing balance.

## 3. Database mapping

| Laravel | Legacy | Notes |
|---|---|---|
| `ledger` | VIEWLEDGER | subcode=PARTY, vdate=V_DATE, amtdr/amtcr=DEBIT/CREDIT, docid=DOCID, vsno=V_SNo, narration=NARR, contrasub=contra |
| `subgroup` | ViewSubgroup / PARTY_LIST | sub_code=PARTY1, name=NAME |
| `acgroup` | subgroup master | group_code, group_name |
| `menuhelp` | Enviro/rights | code=111211 Trail Balance, 111218 Detailed Trial Ledger (finance report family) |

## 4. Business logic comparison

| Logic | Legacy | Laravel | Verdict |
|---|---|---|---|
| Voucher save → ledger rows | Yes | Yes (Ledger model, vsno per row) | ✅ |
| Voucher delete → audit | — | ✅ LedgerLogService::store() + revokeopen guard | ✅ (already fixed in earlier pass) |
| Ledger master delete guard | — | ✅ deleteledger checks usage + guard | ✅ |
| Bank recon (clgdate marking) | Yes | Yes | ✅ |
| General Ledger report | Led | **ADDED** | ✅ now parity |
| Day Book report | DayBook | **ADDED** | ✅ now parity |
| Cash Book / Bank Book | CashBook/BankBook | **ADDED** | ✅ now parity |
| Journal Book report | JournalBook | **ADDED** | ✅ now parity |

## 5. Deletion audit map (financial safety)

| Delete path | File | Audited? |
|---|---|---|
| deletevoucherentry | VoucherEntry.php | ✅ LedgerLogService + guard (prior pass) |
| deleteledger | CompanyController.php | ✅ guard + usage check |
| VoucherVerification / BankReconcilation deletes | — | none exist (no deletes) |

## 6. Missing logic / screens / reports (PRIORITY ORDER)

- ✅ **DONE 2026-08-16 — General Ledger report** (`generalledger`): per-account transaction listing with opening/running/closing balance, account filter, Excel export, PDF print. Read-only.
- ✅ **DONE 2026-08-16 — Day Book report** (`daybook`): chronological register of ALL ledger postings in a date range (vdate/vtype/vno/docid/account/narration/dr/cr), optional vtype filter, PDF print (`printdaybook`), Excel export (`DayBookExport`). Read-only. **BUG-044**: acgroup join now scoped to propertyid (group_code not globally unique) — fixed here + in General Ledger / Detailed Trial Ledger (row inflation ~5% on prop 169).
- ✅ **DONE 2026-08-16 — Cash Book / Bank Book report** (`cashbankbook`): book-type toggle (Cash/Bank), per-account opening/running/closing balance, optional account filter, PDF print (`printcashbankbook`), Excel export (`CashBankBookExport`). Filters ledger by `acgroup.nature IN ('Cash','Bank')` via BUG-044-scoped join (canonical — the denormalized `ledger.groupnature` is stale for 372 rows on prop 169). Read-only.
- ✅ **DONE 2026-08-17 — Journal Book report** (`journalbook`): ledger postings for a voucher type in a date range (default `JV` = Journal), vtype dropdown, PDF print (`printjournalbook`), Excel export (`JournalBookExport`). Mirrors legacy `Proc_203_70_14FE4CC` (`VIEWLEDGER ... WHERE V_date BETWEEN ... AND V_TYPE='<type>' ORDER BY V_DATE,V_TYPE,V_NO,V_ADD,V_SNO`). Reuses `dayBookRows()` + Trail Balance permission 111211. Read-only.
- ⬜ **Aging** (`AgingDr`/`AgingCr`) — receivable/payable aging buckets. Requires business decision on bucket definitions.
- ⬜ **Due List** (`DUELIST`) — overdue payables. Requires aging bucket decision.
- ⬜ **AcCheckList** — accounts checklist (verification coverage). Low priority.
- ⬜ **MemLed** — member ledger (belongs to Membership module).

## 7. Bugs

| ID | Severity | Title | Status |
|---|---|---|---|
| — | — | None found in Accounts module this pass (voucher/ledger deletes already audited) | — |

## 8. Security / performance

- Report queries are parameterized (Laravel query builder). ✅
- `generalledger/fetch` validates dates (`required|date|after_or_equal`). ✅
- Report limited by propertyid + delflag='Y' exclusion (soft-deleted postings excluded, matching accounting convention). ✅
- Performance: opening-balance subquery is grouped by sub_code with index on (propertyid, vdate) recommended if report is slow on large ledgers (201,619 rows live). See PERFORMANCE_AUDIT.

## 9. Live-data findings (read-only)

- Ledger table: 201,619 rows across 16 vtypes (HPOST 161k, PBPB 20k, JV 5.7k, RCT 3.1k, PMT 2.7k, PBPC 2.5k, CPV 2.2k...), date range 2025-04-01 → 2026-09-28.
- General Ledger logic validated on property 169 (17,881 ledger rows): **216 account identities OK, 67 running-balance recomputations OK, 0 mismatches**.

## 10. Implementation plan (remaining)

1. ✅ **DONE 2026-08-16 — Day Book report** — `daybook` + fetch/vtypes/print/export (5 routes), `FinanceController` methods, `DayBookExport`, 2 views.
2. ✅ **DONE 2026-08-16 — Cash Book / Bank Book report** — `cashbankbook` + fetch/accounts/print/export (5 routes), `FinanceController` methods, `CashBankBookExport`, 2 views. Live-validated prop 169 Apr-2026: Cash 1 acct (CASH IN HAND), Bank 3 accts (CREDIT CARD A/C, HDFC, UPI), 0 identity mismatches.
3. ✅ **DONE 2026-08-17 — Journal Book report** — `journalbook` + fetch/vtypes/print/export (5 routes), `FinanceController` methods, `JournalBookExport`, 2 views. Live-validated prop 169 Apr-2026: JV = 332 rows Dr=Cr=₹1,015,580.20 exact (matches Day Book JV filter).
4. Aging / Due List (P2) — needs user confirmation of bucket definitions (never invent business rules).
5. Menu registration SQL for new report (per-property `menuhelp` insert, code 111219) — **requires approval before touching production menu data**.

## 11. Test plan

- [x] php -l on controller + export class
- [x] view:cache (blade compiles)
- [x] route:list — 5 routes registered
- [x] php artisan test — 33 passed
- [x] Live-DB identity check: opening+trans = closing per account (216 accts)
- [x] Live-DB running-balance recomputation (67 accts)
- [x] Day Book live-DB: JV filter Dr=Cr exact (332 rows, ₹1,015,580.20), ALL rows = 2,822 (prop 169, Apr 2026); GL total now matches Day Book total (₹20,851,979.69) post BUG-044
- [x] Cash/Bank Book live-DB (prop 169, Apr 2026): Cash 1 acct + Bank 3 accts, 0 identity mismatches; controller + export outputs identical; bank accts ledger history starts in-period (opening 0 correct)
- [ ] Browser smoke test (menu access + Excel download)
