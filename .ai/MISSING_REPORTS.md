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
| **Advance/Folio reconciliation** (mission §10) | ❓ | ❌ not found | **MISSING** | HIGH priority — reservation→advance→check-in→folio→settlement trace |
| **Advance mismatch report** | ❓ | ❌ not found | **MISSING** | Detect ₹1000 adv vs ₹0 folio vs ₹1000 log |
| Denomination report | FrmDenomination | ❌ not found | MISSING | Verify |
| Lost & Found report | FrmLostFound | ❌ not found | MISSING | Verify |
| Unsettled bills | FrmUnSettledBillsInfo | ❌ not found | MISSING | Verify |
| Revenue-vs-budget MIS | FrmRevenueWiseBudget | ⚠️ | PARTIAL | Verify |
| Foreign exchange | FrmForExRec | ❌ | OBSOLETE? | Confirm need |
| Membership/reward | FrMember | ⚠️ | PARTIAL | Verify |
| Telephone/EPABX | — | ❌ | UNKNOWN | Verify module existence first |

## Next actions

1. Run a full report inventory: grep `*.blade.php` in `resources/views/property` + list `Reporting.php` methods → build complete EXISTS list.
2. Diff against legacy print subs in `.ai/HMS.text` (search `Print` / `Report` subs).
3. Implement the **Advance/Folio reconciliation report** first (mission §10, P0).
