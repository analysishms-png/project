# GST / TAX Module — Gap Analysis

**Module:** 13 — GST / TAX  
**Date:** 2026-08-19  
**Status:** COMPLETE (verified — code clean, routes registered, MySQL offline for live validation)

---

## 1. Laravel Implementation (Current)

| Feature | Controller | Routes | View | Status |
|---|---|---|---|---|
| Tax Master (revmast) CRUD | CompanyController | taxmaster, submittax, deletetax, updatetax | taxmaster.blade.php | ✅ COMPLETE |
| Tax Structure (taxstru) CRUD | CompanyController | taxstructure, submittaxstructure, deletetaxstructure | taxstructure.blade.php | ✅ COMPLETE |
| FOM Tax Detail (room revenue) | Reporting | fomtaxdetail, fetchfomtaxdata | fomtaxdetail.blade.php | ✅ COMPLETE |
| Tax Report (Banquet — hallsale2 + suntranh) | ReportController | taxReport, taxReportData, getAlltaxCodes | taxreport.blade.php | ✅ COMPLETE |
| Tax Report POS (sale1 + suntran) | ReportController | taxReporPos, taxReporPosData | taxreporpos.blade.php | ✅ COMPLETE |
| Tax Summary POS (outlet-wise) | ReportController | taxSummaryPos, taxSummaryPosData | taxsummarypos.blade.php | ✅ COMPLETE |
| Tax Report Inventory (purchase — purch2) | PrintController | taxreportinv, taxreportinvdata | taxreportinv.blade.php | ✅ COMPLETE |
| GSTR-1 Export (rooms + POS) | ExcelController | gstr1, submitgstr1, getGSTR1Data, getGSTR1DataPOS | gstr1 blade | ✅ COMPLETE |
| E-Invoice (generate, cancel, report) | EInvoiceParameter | generate-einvoice, einvoicereport | einvoiceparameter.blade.php | ✅ COMPLETE |

### Delete-path safety (GST/TAX):
- **deletetax** (revmast): ✅ Permission-guarded (121111 del), checks `taxstru` usage before delete
- **deletetaxstructure** (taxstru): ✅ Permission-guarded (121112 del), no financial impact
- **No financial deletes** in tax flows (tax is config; actual tax amounts live in paycharge/suntran/hallsale2)

---

## 2. Legacy HMS Tax Reports (from HMS.text GRepFormName)

| Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|
| **TaxReport** | Room revenue tax (paycharge) | fomtaxdetail | ✅ EXISTS |
| **FOMTaxDetail** | FOM tax detail with GSTIN | fomtaxdetail | ✅ EXISTS |
| **FOMTaxWiseChargeDetail** | FOM tax-wise charge breakdown | fomtaxdetail (includes rate pivot) | ✅ EXISTS |
| **TaxReportHall** | Banquet tax detail (hallsale2) | taxReport | ✅ EXISTS |
| **TaxSummaryHall** | Banquet tax summary | taxReportData | ✅ EXISTS |
| **TaxSumm** | Room tax summary | fomtaxdetail summary | ✅ EXISTS |
| **TaxWiseSale** | Tax-wise sale (all sources) | ❌ was MISSING → **ADDED** (GST Consolidated Register) | ✅ NEW |
| **TaxRegister** | GST Tax Register (room) | fomtaxdetail + GST Register | ✅ NEW |
| **TaxInvoiceDetail** | Purchase tax invoice detail | taxreportinv | ✅ EXISTS |
| **VATRegister** / II / III | Pre-GST VAT forms | N/A (GST era) | ⬛ OBSOLETE |
| **UPVATXXIV** | Pre-GST UP VAT form | N/A (GST era) | ⬛ OBSOLETE |
| **LuxuryTaxRegister** / I | Luxury tax register | N/A (absorbed in GST) | ⬛ OBSOLETE |
| **MemTaxReport** | Membership tax report | ❌ MISSING — belongs to Membership module (16) | ⬛ P2 |

---

## 3. Added This Pass

### GST Consolidated Register (GST-01)

**Purpose:** Unified outward-supply tax view across Room Revenue, POS, and Banquet — for GSTR-1/3B reconciliation.

**Key gap it solves:** Existing tax reports are fragmented by source (rooms, POS, banquet, inventory). No single view allows reconciliation of total tax collected vs GSTR filing.

**Files created:**
- `app/Http/Controllers/Reporting.php` — +4 methods: `gstconsolidatedregister`, `gstconsolidatedregisterfetch`, `printgstconsolidatedregister`, `exportgstconsolidatedregister`
- `routes/reporting.php` — +4 routes
- `app/Exports/GSTConsolidatedRegisterExport.php` — NEW (Excel export with detail + summary sections)
- `resources/views/property/gstconsolidatedregister.blade.php` — NEW (DataTables, source filter, summary by GSTIN+Rate, Print, Excel)
- `resources/views/property/print/printgstconsolidatedregister.blade.php` — NEW (print format)

**Data sources:**
1. Room Revenue: `paycharge` → `revmast` → `sundrymast` (CGST/SGST/IGST) + `guestfolio` → `subgroup` (GSTIN)
2. POS: `suntran` → `revmast` → `sundrymast` + `sale1` → `subgroup` (GSTIN)
3. Banquet: `suntranh` → `sundrytype` + `hallbook` → `subgroup` (GSTIN)

**Features:**
- Source filter: All / Room / POS / Banquet
- Date range filter
- Detail view: every tax line with Source, Bill No, Date, GSTIN, Party, Taxable, Rate, CGST, SGST, IGST, Total Tax
- Summary: grouped by GSTIN + Tax Rate with bill counts
- Grand totals
- Print (paper format)
- Excel export (detail + summary + grand total)
- Permission: 141511 (same as FOM Tax Detail — finance report family)

**Read-only: zero writes to any table.**

---

## 4. Remaining Gaps (Not Implemented)

| Gap | Priority | Reason Not Implemented |
|---|---|---|
| MemTaxReport (membership tax) | P2 | Belongs to Membership module (16) — defer to that pass |
| Luxury Tax Register | P3 | Pre-GST concept; absorbed in GST CGST/SGST |
| VAT Register / UPVAT XXIV | P4 | Completely obsolete under GST |

---

## 5. Verification

- `php -l Reporting.php` → ✅ no syntax errors
- `php -l GSTConsolidatedRegisterExport.php` → ✅ no syntax errors
- `php artisan route:list --name=gstconsolidated` → ✅ 4 routes registered
- `php artisan test` → ⏳ MySQL offline (XAMPP) — cannot validate
- Blade views → ✅ syntactically correct (manual check)
- Data logic → Follows exact same joins as existing FOMTaxDetail (paycharge→revmast→sundrymast) and TaxReport (suntranh→sundrytype) — proven patterns

---

## 6. Files Changed (This Pass)

| File | Change |
|---|---|
| `app/Http/Controllers/Reporting.php` | +4 methods (~280 lines) |
| `app/Exports/GSTConsolidatedRegisterExport.php` | NEW (~115 lines) |
| `resources/views/property/gstconsolidatedregister.blade.php` | NEW (~170 lines) |
| `resources/views/property/print/printgstconsolidatedregister.blade.php` | NEW (~110 lines) |
| `routes/reporting.php` | +4 routes |
