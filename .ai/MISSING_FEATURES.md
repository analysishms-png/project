# Analysis HMS — MISSING FEATURES

> Legacy HMS (`.ai/HMS.bas`/`.text`, 151 forms) vs current Laravel. For each gap: is it still required / covered elsewhere / obsolete?
> Classifications: MISSING / PARTIAL / REPLACED / OBSOLETE / IMPROVED / UNKNOWN.

---

## Candidate gaps (from legacy form inventory)

| # | Legacy feature (form) | Purpose | Laravel status | Class | Action |
|---|----------------------|---------|----------------|-------|--------|
| MF-01 | FrmLostFound | Lost & Found register | Not located | ⚠️ UNKNOWN | Scan routes/views for lost-found; else MISSING → build register module |
| MF-02 | FrmDenomination | Cashier denomination count | ✅ DenominationController + views | ✅ IMPLEMENTED | Complete CRUD + print + formats |
| MF-03 | FrmForExRec / FrmForeignExMast | Foreign exchange records | Not located | ⚠️ UNKNOWN | Scan; likely obsolete for domestic hotels — confirm with business |
| MF-04 | FrmMeterReading | Utility meter readings | Not located | ⚠️ UNKNOWN | Scan maintenance module |
| MF-05 | FrmGuestWakeUp | Wake-up call service | Not located | ⚠️ UNKNOWN | Scan front office |
| MF-06 | FrmPaxDetails | Pax detail entry | Not located | ⚠️ UNKNOWN | Scan reservation/banquet |
| MF-07 | FrmUnSettledBillsInfo | Unsettled bills list | Not located | ⚠️ UNKNOWN | Scan reporting/company |
| MF-08 | FrmHotKey | POS keyboard hotkeys | Not located | ⚠️ UNKNOWN | Scan POS settings |
| MF-09 | FrmNAMessageA/B/C | Night audit messages | Night audit views exist | PARTIAL | Verify message flows |
| MF-10 | FrmMergeCharge / FrmRevMergeCharge | Merge/reverse-merge charges | Part of accounting (AccountPosting) | PARTIAL | Verify folio merge/reverse-merge exists (mission §18) |
| MF-11 | FrmPOSBillModificationDatewise / ItemGroupwise | POS bill modification tools | POS tools exist (posrecycle) | PARTIAL | Verify parity |
| MF-12 | FrmRevenueWiseBudget | Revenue-vs-budget MIS | Reporting exists | PARTIAL | Verify budget report |
| MF-13 | FrmSMSEnviro / FrmSMSMultiType | SMS config + bulk SMS | WhatsApp/SMS logs exist | PARTIAL | Verify bulk SMS + SMS center settings |
| MF-14 | FrmStockTransfer / FrmOPStock | Stock transfer + opening stock | InventoryController | PARTIAL | Verify stock transfer flow + transaction safety |
| MF-15 | FrmWaiterMast / FrmDeliveryBoyMast | Waiter/delivery boy masters | Not located | ⚠️ UNKNOWN | Scan POS masters |
| MF-16 | FrmClaimEntry / FrmExpense | Expense claims | Finance module | PARTIAL | Verify claims workflow |
| MF-17 | FrmGuestAddObj / FrmGuestStat | Guest objectives/statistics | Not located | ⚠️ UNKNOWN | Low priority |
| MF-18 | FrmDataRecieving / FrmPOSSaleDataTransfer / FrmUploadProcess | Legacy data transfer utilities | Tools exist (dataempty, posrecycle) | REPLACED | Tools module supersedes |

## Modern features worth evaluating (no legacy equivalent)

- **Advance/Folio reconciliation report** (mission §10) — REQUIRED by master mission. Not found in Laravel. → HIGH priority.
- Automated **DB backup** job (mission §25) — only a directory exists.
- **Eager-loading/perf hardening** — see PERFORMANCE_AUDIT.md.

## Newly Implemented (2026-08-21)

| # | Feature | Module | Status |
|---|---------|--------|--------|
| MF-02 | Denomination Module | Cashier/Denomination | ✅ FULLY IMPLEMENTED |
| — | Call Type Master | Telephone/EPABX | ✅ FULLY IMPLEMENTED |
| — | Call Code Master | Telephone/EPABX | ✅ FULLY IMPLEMENTED |
| — | Cash Card Registration | Cash Card | ✅ FULLY IMPLEMENTED |
| — | Cash Card Recharge | Cash Card | ✅ FULLY IMPLEMENTED |
| — | Cash Card Refund | Cash Card | ✅ FULLY IMPLEMENTED |
| — | Cash Card History | Cash Card | ✅ FULLY IMPLEMENTED |

## Rule reminder

Do NOT implement any of the above until: (1) Laravel equivalent searched (routes + views + controllers), (2) business requirement confirmed, (3) database support verified. Never invent business rules (see QUESTIONS.md if ambiguous).
