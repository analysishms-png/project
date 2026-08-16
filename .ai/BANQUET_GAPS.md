# BANQUET — Gap Analysis (Laravel vs Legacy HMS)

> Date: 2026-08-16 · Verified against `app/Http/Controllers/Banquet.php`, `routes/company.php`, live `analysis` DB, and `.ai/HMS.text` legacy source.

## 1. Full Trace (Laravel)

| Step | Laravel implementation | Status |
|---|---|---|
| Enquiry | `bookinginquiry` / `BookingInquiryController` (store/update/delete) | ✅ COMPLETE |
| Booking | `banquetbooking` / `banquetupdate` / `deletebanquet` (HallBook) | ✅ COMPLETE |
| Hall | `venuemast` / `VenueMast` + `venueavailability` / `availablitybanquet` / `venueavailabilitydaywise` | ✅ COMPLETE |
| Function | `func_name` on hallbook; function booking flow | ✅ COMPLETE |
| Menu / Package | menu-spl fields on hallbook + `banquetbilling` item entry | ✅ COMPLETE |
| Advance | `advancebanquetsubmit` → paychargeh (AD, sno=1 main + sno=2 GST split) + Ledger (2 rows) + receipt print | ✅ COMPLETE |
| Hall Charges | `banquetbillsubmit` → hallsale1 (bill) + hallstock (items) + hallsale2 (tax lines) + suntranh + ledger | ✅ COMPLETE |
| Discount / Tax / Round Off | Sundrytype revcode posting; taxable/nontaxable split; roundoff | ✅ COMPLETE (see config note) |
| Settlement | `hallbillsettle` + settle submit → paychargeh (IDC) + ledger; `banqsettlefetch` | ✅ COMPLETE |
| Bill | `banquetbillprint` / `performaInvoicePrint` / `printfp` | ✅ COMPLETE |
| Cancellation | `deletebanquet` (guarded), `deletePerformaInvoice` (estimate tables only) | ✅ COMPLETE |
| Outdoor catering | partyname/venue-level; no dedicated module (legacy also limited) | ⚠️ NOT VERIFIED |

## 2. Financial Safety (this pass)

### BUG-038 — FIXED ✅
- **`deleteAdvance`** (newer advance flow): deleted `paychargeh` rows with **zero audit** and **no ledger cleanup**. Live: each banquet advance carries 2 ledger rows (main + tax) — deleting only paychargeh left orphaned ledger rows.
- **`deletebanquetbill`**: wiped hallsale1 + hallstock + hallsale2 + suntranh + ledger with **no audit log**.
- **Fix**: both paths now (a) delete paired ledger rows, (b) log every deleted row via `PaychargeLog::auditDeleted` (user/time/reason/amount/linkage) before removal. Validated on live sample bill (1 hallsale1 + 6 suntranh + 5 ledger rows).
- `deleteadvancebanquet` (older path) already correct since BUG-030.

### Accounting config note (documented, NOT changed — accounting rule)
- `EnviroBanquet.roundoffac` / `discountac` are configured in `submitbanquetparameter` but **only used for account-lookup filters** — actual discount/round-off posting flows through `Sundrytype.revcode`.
- Legacy HMS enforced `enviro.HallDiscAC` + `HallRoundOff` before posting (see HMS.text: "Please define Banquet Discount A/c In Enviro").
- **Divergent design**: Laravel's Sundrytype approach is a deliberate modernization. If the business needs the Hall Discount/Round Off accounts to be the posting targets, that is a change to accounting mappings → requires approval (mission §22/§29).

## 3. Report / MIS Comparison

| Report | Laravel | Status |
|---|---|---|
| Banquet Sales Register | `banqsalesreg` / `fetchsalesregister` | ✅ EXISTS |
| Banquet Settlement Summary | `banqsettlementsummary` / `banqsettlefetch` | ✅ EXISTS |
| Venue Availability (list + day-wise) | `venueavailability` / `venuestatus` | ✅ EXISTS |
| Performa Invoice + print | `performahallsalefetch` / `performainvoiceprint` | ✅ EXISTS |
| **Banquet Outstanding (party-wise)** | **`banqoutstanding` — NEW this pass** | ✅ **ADDED** |
| Daily Function Sheet | — | ❌ MISSING (legacy `DailyFunctionSheet`) |
| Cashier report (banquet) | — | ⚠️ PARTIAL (covered by settlement summary) |

## 4. Live-Data Reconciliation Findings

- **Banquet Outstanding scan (all properties, exact `hallbillsettle` model: AD sno=1 + IDC by contradocid)**:
  - Property 162: 16 bills, all reconcile to ₹0 (healthy).
  - Real outstanding: prop 141 ₹252,525 (4 bills, 0 paid), prop 132 ₹305,030, prop 108 ₹297,799, prop 109 ₹414,040, prop 105 ₹42,084, prop 111 ₹12,156, prop 115 ₹100,000, prop 136 ₹200,000, prop 138 ₹124,962, prop 151 ₹87,150, prop 156 ₹117,500. **Total ~₹1.95M+ outstanding across properties.**
  - **Overpayment anomalies** (paid > bill — needs review): prop 108 vno=14 net=₹3,00,000 vs paid ₹5,00,000 (₹2,00,000 excess), prop 108 vno=5/6 minor excess ₹15,750 each.
  - **Unpaid bills from 2025** (aging risk): prop 109 vno=1/2/3/5 (Sep–Oct 2025), prop 108 vno=12/14/18/19 (Jan–Feb 2026), prop 141 all bills (Nov–Dec 2025).
- GST split rows (sno=2) are correctly EXCLUDED from paid (matches `hallbillsettle`).

## 5. Gaps Requiring Business Decision

| # | Gap | Legacy source | Recommendation |
|---|---|---|---|
| BQ-01 | **Daily Function Sheet report** | `DailyFunctionSheet` | Build (read-only, safe) — P1 |
| BQ-02 | Overpayment anomalies (paid > bill) | — | Business review; likely refund/adj voucher needed |
| BQ-03 | 2025 unpaid banquet bills | — | Credit-control review; report now exists to surface them |
| BQ-04 | Outdoor catering stock flow | legacy limited | Verify need before building |
| BQ-05 | Hall discount/round-off posting accounts | `HallDiscAC`/`HallRoundOff` | Approval needed if posting target must change |

## 6. Definition of Done Status

- [x] UI works (report page, design-system compliant)
- [x] Route works (`banqoutstanding`, `banqoutstandingfetch`)
- [x] Controller works (validated SQL on live DB)
- [x] Financial logic verified (model matches `hallbillsettle` exactly)
- [x] Excel / Print verified (Tabulator pattern)
- [x] Deletion audit complete (BUG-038)
- [ ] Daily Function Sheet report (BQ-01) — next
- [ ] `.ai/BANQUET_GAPS.md` written — done
