# Analysis HMS — MISSING REPORTS

> Old HMS reports vs current Laravel reports. STATUS: EXISTS / PARTIAL / MISSING / IMPROVED / OBSOLETE.
> Full legacy report list requires a dedicated scan of `.ai/HMS.text` report-print subs — this is the current best-known state.

---

## Known report areas (Laravel)

- `routes/reporting.php` — main reporting routes (Reporting controller, 5.4K lines)
- `app/Http/Controllers/Reporting.php`, `CheckRegister.php`, `DailyReport.php`(?)
- Print views under `resources/views/property/prints/`, `resources/views/property/` (*print*.blade.php)
- Excel/PDF exports via phpspreadsheet + dompdf

## Report status table (current knowledge)

| Report | Legacy | Laravel | STATUS | Notes |
|--------|--------|---------|--------|-------|
| FOM bill / guest bill print | ✅ | `billprint_pdf.blade.php`, PrintController | EXISTS | |
| KOT print | ✅ | Kot controller + PrintEvent | EXISTS | |
| POS bill | ✅ | Pointofsale + SaleBillPrintEvent | EXISTS | |
| Advance receipt | ✅ | `property/advancelistreceipt.blade.php` | EXISTS | |
| Banquet bill | ✅ | `banquetbillprint.blade.php` | EXISTS | |
| Night audit | ✅ | nightauditlog/ views | EXISTS | |
| GST / GSTR1 | ✅ | Gstr1 helper, tdsreport | EXISTS | |
| Supplier-wise purchase | ✅ | `supplierwisepurchase.blade.php` | EXISTS | |
| TDS report | ✅ | `finance/tdsreport.blade.php` | EXISTS | |
| Detailed Trial Ledger | ✅ | `finance/detailedtrialledger.blade.php` | EXISTS | summary per account (opening/trans/closing) |
| **General Ledger** (legacy `Led`) | ✅ | `finance/generalledger.blade.php` | **ADDED 2026-08-16** | transaction-level, running balance, account filter, print + Excel |
| Day Book (legacy `DayBook`) | ✅ | `finance/daybook.blade.php` | **ADDED 2026-08-16** | chronological register, vtype filter, print + Excel |
| Cash Book / Bank Book (legacy `CashBook`/`BankBook`) | ✅ | `finance/cashbankbook.blade.php` | **ADDED 2026-08-16** | book toggle, running balance, print + Excel |
| Journal Book (legacy `JournalBook`) | ✅ | `finance/journalbook.blade.php` | **ADDED 2026-08-17** | journal-voucher postings (vtype default JV, selectable), print + Excel |
| Aging Dr/Cr (legacy `AgingDr`/`AgingCr`) | ✅ | ❌ | **MISSING** | P2 — needs bucket-definition decision |
| Due List (legacy `DUELIST`) | ✅ | ❌ | **MISSING** | P2 — needs bucket-definition decision |
| **Advance/Folio reconciliation** (mission §10) | ❓ | ❌ not found | **MISSING** | HIGH priority — reservation→advance→check-in→folio→settlement trace |
| **Advance mismatch report** | ❓ | ❌ not found | **MISSING** | Detect ₹1000 adv vs ₹0 folio vs ₹1000 log |
| Denomination report | FrmDenomination | ❌ not found | **MISSING** | Verified 2026-08-16 — legacy POS Reports "Denomination Detail" (`DenominationDetail` table absent); cashier closeout, needs business confirmation |
| Lost & Found report | FrmLostFound | ✅ `property/housekeeping/lostfound.blade.php` (lostfoundregister print) | **EXISTS** | Verified 2026-08-16 — full CRUD + register + print |
| Unsettled bills | FrmUnSettledBillsInfo | ⚠️ `pos_saledeletereport` (del/unsettle) + `pendingkotreport` + dashboard UnsettledRooms | **REPLACED (partial)** | Verified 2026-08-16 — no single legacy-shaped screen, but sale-bill pending/settle + pending-KOT views exist |
| Revenue-vs-budget MIS | FrmRevenueWiseBudget | ⚠️ | PARTIAL | Verify |
| Foreign exchange | FrmForExRec | ❌ | OBSOLETE? | Confirm need |
| Membership/reward | FrMember | ⚠️ | PARTIAL | Verify |
| Telephone/EPABX | — | ❌ | UNKNOWN | Verify module existence first |

### Night Audit Reports
| Legacy Report | Laravel Equivalent | Status | Notes |
|---|---|---|---|
| DailyReport | dailyreport (NightAudit/Reports/DailyReport.php) | ✅ EXISTS | |
| NightAuditReport | nightauditrecon | ✅ **NEW 2026-08-19** | Room occupancy vs charges + settlement + prior-night comparison |
| NightAuditReportI | nightauditrecon (list variant) | ✅ **NEW 2026-08-19** | Covered by reconciliation report |
| AMRMorningReport | amrmorningreport | ✅ **NEW 2026-08-19** | Daily operational snapshot: occupancy + arrivals + departures + revenue |
| CheckedInGuestDtl | checkedinguestdetail | ✅ **NEW 2026-08-19** | All checked-in guests with room, ID, company, balance |
| DailySumm | dailyreport | ✅ EXISTS | |
| DailyDiet | — | ❌ MISSING | P2 — meal plan tracking |
| DailyFuncSheet | — | ❌ MISSING | P2 — documented in BANQUET_GAPS.md |
| RoomNights | — | ❌ MISSING | P2 — occupancy statistics |
| DailyStoreIssRpt | inventory reports | ✅ EXISTS | |

### GST/TAX Reports
| Legacy Report | Laravel Equivalent | Status | Notes |
|---|---|---|---|
| FOM Tax Detail | fomtaxdetail | ✅ EXISTS | |
| FOM Tax Wise Charge Detail | fomtaxdetail (rate pivot) | ✅ EXISTS | |
| TaxReport (room) | fomtaxdetail | ✅ EXISTS | |
| TaxReportHall | taxReport | ✅ EXISTS | |
| TaxSummaryHall | taxReportData | ✅ EXISTS | |
| TaxSumm | fomtaxdetail summary | ✅ EXISTS | |
| TaxInvoiceDetail | taxreportinv | ✅ EXISTS | |
| TaxWiseSale / TaxRegister | gstconsolidatedregister | ✅ **NEW 2026-08-19** | Unified all-source tax view |
| VATRegister / II / III | N/A | ⬛ OBSOLETE | Pre-GST |
| UPVATXXIV | N/A | ⬛ OBSOLETE | Pre-GST |
| LuxuryTaxRegister / I | N/A | ⬛ OBSOLETE | Absorbed in GST |
| MemTaxReport | — | ❌ MISSING | Belongs to Membership module (16) |

## Next actions

1. Run a full report inventory: grep `*.blade.php` in `resources/views/property` + list `Reporting.php` methods → build complete EXISTS list.
2. Diff against legacy print subs in `.ai/HMS.text` (search `Print` / `Report` subs).
3. Implement the **Advance/Folio reconciliation report** first (mission §10, P0).
