# Reports / MIS — Complete Parity Matrix

**Module:** 15 — Reports / MIS  
**Date:** 2026-08-19  
**Total Legacy Reports (GRepFormName):** 219  
**Total Laravel Report Methods:** ~246 (across Reporting.php 94, ReportController 28, PrintController 118, ExcelController 6)

---

## STATUS KEY

| Status | Meaning |
|---|---|
| ✅ EXISTS | Laravel has equivalent report with same business purpose |
| ⚠️ PARTIAL | Report exists but missing some fields/filters/compared to legacy |
| ❌ MISSING | Legacy report has no Laravel equivalent |
| 🆕 NEW | Added in this AI session (not in legacy) |
| ⬛ OBSOLETE | Legacy report is pre-GST / legacy-specific / no longer needed |

---

## 1. FRONT OFFICE REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 1 | ABCAnalysis | ABC analysis (guest value) | — | ❌ MISSING (P3) |
| 2 | ABCAnalysisSale | ABC analysis (sales) | — | ❌ MISSING (P3) |
| 3 | AMRMorningReport | Morning report (occupancy + arrivals + departures) | amrmorningreport 🆕 | 🆕 NEW (2026-08-19) |
| 4 | AcCheckList | Account checklist | accountchecklist 🆕 | 🆕 NEW (2026-08-20) |
| 5 | ArrDepReg | Arrival/Departure register | arrivallist ✅ | ✅ EXISTS |
| 6 | ArrivalDepList | Arrival/Departure list | arrivallist ✅ | ✅ EXISTS |
| 7 | BookingDetail | Booking detail report | bookingEnquiryDetail ✅ | ✅ EXISTS |
| 8 | CancelBillDetails | Cancelled bill details | cancelbills ✅ | ✅ EXISTS |
| 9 | CancellLetter | Cancellation letter | — | ❌ MISSING (P2 — printable letter) |
| 10 | CheckedInGuestDtl | Checked-in guest detail | checkedinguestdetail 🆕 | 🆕 NEW (2026-08-19) |
| 11 | ChkInRegister | Check-in register | checkinreg ✅ | ✅ EXISTS |
| 12 | ConfirmLetter | Confirmation letter | — | ❌ MISSING (P2 — printable letter) |
| 13 | DaysForecastRep | Days forecast report | occupancyforecast ✅ | ✅ EXISTS |
| 14 | ExpectedDeparture | Expected departure list | expectedcheckout ✅ | ✅ EXISTS |
| 14a | ChkOutRegister | Check-out register | checkoutregister 🆕 | 🆕 NEW (2026-08-20) |
| 15 | ExtraChargesDuringStay | Extra charges during stay | — | ❌ MISSING (P2) |
| 16 | FOMBillChangeReport | FOM bill change report | — | ❌ MISSING (P2 — audit trail) |
| 17 | GuestBillDetails | Guest bill details | guesttrail ✅ | ✅ EXISTS |
| 18 | GuestChargesMIS | Guest charges MIS | — | ❌ MISSING (P2) |
| 19 | GuestChgJournal | Guest charge journal | guesttrail ✅ | ✅ EXISTS |
| 20 | GuestChgJournalLog | Guest charge journal log | guesttrail ✅ | ✅ EXISTS |
| 21 | GuestInHouse | Guest in-house list | foccreport ✅ | ✅ EXISTS |
| 22 | GuestLedger | Guest ledger | openguestledger ✅ | ✅ EXISTS |
| 23 | GuestPayments | Guest payments report | guestpayments 🆕 | 🆕 NEW (2026-08-20) |
| 24 | GuestTrialBalance | Guest trial balance | guesttrialbalance 🆕 | 🆕 NEW (2026-08-20) |
| 25 | GuestWiseAnalysis | Guest-wise analysis | guestwiseanalysis 🆕 | 🆕 NEW (2026-08-20) |
| 26 | GuestwiseRevenue | Guest-wise revenue | guestwiserevenue 🆕 | 🆕 NEW (2026-08-20) |
| 27 | InsHouseCount | In-house count | foccreport ✅ | ✅ EXISTS |
| 28 | MovementList | Movement list | movementlist 🆕 | 🆕 NEW (2026-08-20) |
| 29 | OccAnalysis | Occupancy analysis | occupancyreport ✅ | ✅ EXISTS |
| 30 | OccupancyReport | Occupancy report | occupancyreport ✅ | ✅ EXISTS |
| 31 | PartyOutStanding | Party outstanding | partyoutstanding 🆕 | 🆕 NEW (2026-08-20) |
| 32 | PartyWiseOutStanding | Party-wise outstanding | outStandingreport ✅ | ✅ EXISTS |
| 33 | PlanReport | Plan report | planreport 🆕 | 🆕 NEW (2026-08-20) |
| 34 | ReservStatusArrival | Reservation status (arrival) | reservstatusarrival 🆕 | 🆕 NEW (2026-08-20) |
| 35 | ReservStatusInHouse | Reservation status (in-house) | reservstatusinhouse 🆕 | 🆕 NEW (2026-08-20) |
| 36 | ReservationStatus | Reservation status | reservationstatus 🆕 | 🆕 NEW (2026-08-19) |
| 37 | ResvAdvRecd | Reservation advance received | advresreport ✅ | ✅ EXISTS |
| 38 | ResvAdvRecdArr | Resv advance (arrivals) | advresreport ✅ | ✅ EXISTS |
| 39 | ResvAdvRecdInHouse | Resv advance (in-house) | advresreport ✅ | ✅ EXISTS |
| 40 | RevAnalysis | Revenue analysis | revenueanalysis2 🆕 | 🆕 NEW (2026-08-20) |
| 41 | RoomChangeHistory | Room change history | roomchangehistory 🆕 | 🆕 NEW (2026-08-20) |
| 42 | RoomOccDisp | Room occupancy display | roominventory ✅ | ✅ EXISTS |
| 43 | RoomOccupancyAnalysisReport | Room occupancy analysis | occupancyreport ✅ | ✅ EXISTS |
| 44 | RoomOccupancyReportSummary | Room occupancy summary | occupancyreport ✅ | ✅ EXISTS |
| 45 | RoomRentAuditRpt | Room rent audit report | roomrentaudit 🆕 | 🆕 NEW (2026-08-19) |
| 46 | RoomStatus | Room status display | inhoseroomstatus ✅ | ✅ EXISTS |
| 47 | RoomTypeOccupancyAnalysis | Room type occupancy analysis | lookuproomtype ✅ | ✅ EXISTS |
| 48 | RoomTypeOccupancyReport | Room type occupancy report | lookuproomtype ✅ | ✅ EXISTS |
| 49 | RoomWiseRoomRevenueReport | Room-wise revenue | roomwiseroomrevenue 🆕 | 🆕 NEW (2026-08-19) |
| 50 | SettleRep | Settlement report (FO) | fosettlereport 🆕 | 🆕 NEW (2026-08-19) |
| 51 | VoidBills | Void bills | voidbills ✅ | ✅ EXISTS |

---

## 2. POS / KOT / RESTAURANT REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 52 | BillWiseAdjustmentReport | Bill-wise adjustment | billwiseadjustment 🆕 | 🆕 NEW (2026-08-20) |
| 53 | CancelBillDetails | POS cancelled bills | cancelbills ✅ | ✅ EXISTS |
| 54 | CardStatusReport | Card status | — | ❌ MISSING (P2 — cash card) |
| 55 | CashierCollection | Cashier collection | cashierreport ✅ | ✅ EXISTS |
| 56 | CashierCollectionMIS | Cashier collection MIS | cashiercollectionmis 🆕 | 🆕 NEW (2026-08-20) |
| 57 | CashierSale | Cashier sale | cashierreport ✅ | ✅ EXISTS |
| 58 | CashierSettlement | Cashier settlement | cashiersettlement 🆕 | 🆕 NEW (2026-08-20) |
| 59 | CashierSummary | Cashier summary | cashierreport ✅ | ✅ EXISTS |
| 60 | Clg | Closing (KOT) | pendingkotreport ✅ | ✅ EXISTS |
| 61 | ClgNot | Not closed KOTs | pendingkotreport ✅ | ✅ EXISTS |
| 62 | CoverAnalysis | Cover analysis (pax) | coveranalysis 🆕 | 🆕 NEW (2026-08-20) |
| 63 | DelBillUnsetBill | Deleted/unsettled bills | deletedunsettledbill ✅ | ✅ EXISTS |
| 64 | DeliveryStatus | Delivery status | deliverystatus 🆕 | 🆕 NEW (2026-08-20) |
| 65 | DiscountPartyWiseReg | Discount party-wise register | discountregister 🆕 | 🆕 NEW (2026-08-20) |
| 66 | DiscountReg | Discount register | discountregister 🆕 | 🆕 NEW (2026-08-20) |
| 67 | DiscountSumm | Discount summary | discountregister 🆕 | 🆕 NEW (2026-08-20) |
| 68 | EditedBills | Edited bills | editedbills 🆕 | 🆕 NEW (2026-08-20) |
| 69 | FunctionWiseItemDetail | Function-wise item detail | functionwiseitemdetail 🆕 | 🆕 NEW (2026-08-20) |
| 70 | GratuityReport | Gratuity report | gratuityreport 🆕 | 🆕 NEW (2026-08-20) |
| 71 | HtCashierSumm | HT cashier summary | htcashiersumm 🆕 | 🆕 NEW (2026-08-20) |
| 72 | ItemSale | Item sale | itemwisesale ✅ | ✅ EXISTS |
| 73 | ItemWiseGroupWiseSaleReport | Item-wise group-wise sale | itemwisesale ✅ | ✅ EXISTS |
| 74 | ItemWiseSale | Item-wise sale | itemwisesale ✅ | ✅ EXISTS |
| 75 | ItemWiseSaleHall | Item-wise sale (hall) | itemwisesalehall 🆕 | 🆕 NEW (2026-08-20) |
| 76 | KOTRateChange | KOT rate change | — | ❌ MISSING (P2) |
| 77 | KOTWiseDetails | KOT-wise details | kotwisedetail ✅ | ✅ EXISTS |
| 78 | KotEditDelete | KOT edit/delete log | — | ❌ MISSING (P2 — audit) |
| 79 | LiquorSaleRep | Liquor sale report | — | ❌ MISSING (P2 — outlet-specific) |
| 80 | NCKOTSummary | NC KOT summary | nckotreport ✅ | ✅ EXISTS |
| 81 | NCKOTWiseDetails | NC KOT-wise details | nckotreport ✅ | ✅ EXISTS |
| 82 | NonTrans | Non-transactions | — | ❌ MISSING (P3) |
| 83 | OpenItemSale | Open item sale | — | ❌ MISSING (P3) |
| 84 | OrderDetailReport | Order detail report | — | ❌ MISSING (P2) |
| 85 | PLUFile | PLU file (menu items) | menuitemratereport ✅ | ✅ EXISTS |
| 86 | PmtByCashier | Payment by cashier | cashierreport ✅ | ✅ EXISTS |
| 87 | SalRegPerCover | Sale register per cover | — | ❌ MISSING (P3) |
| 88 | SaleRegister | Sale register | possalesreg ✅ | ✅ EXISTS |
| 89 | SaleRegisterI | Sale register I | possalesreg ✅ | ✅ EXISTS |
| 90 | SaleSumm | Sale summary | salesumm ✅ | ✅ EXISTS |
| 91 | SaleSummConsolidated | Sale summary consolidated | salesummaryrpt ✅ | ✅ EXISTS |
| 92 | SalesDayBook | Sales day book | salesummaryrpt ✅ | ✅ EXISTS |
| 93 | SalesRegister | Sales register | possalesreg ✅ | ✅ EXISTS |
| 94 | SalesSummary | Sales summary | salesummaryrpt ✅ | ✅ EXISTS |
| 95 | TableWiseSale | Table-wise sale | — | ❌ MISSING (P2) |
| 96 | TallyPOSReport | Tally POS report | — | ❌ MISSING (P2 — accounting export) |
| 97 | VoidBills | Void bills (POS) | voidbills ✅ | ✅ EXISTS |
| 98 | WaiterWiseSale | Waiter-wise sale | waitersale 🆕 | 🆕 NEW (2026-08-20) |

---

## 3. BANQUET / HALL REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 99 | CompanyWiseSaleHall | Company-wise sale (hall) | — | ❌ MISSING (P2) |
| 100 | DailyFuncSheet | Daily function sheet | dailyFunctionSheet ✅ | ✅ EXISTS |
| 101 | FunctionWiseItemDetail | Function-wise item detail | — | ❌ MISSING (P2) |
| 102 | SettleRepHall | Settlement report (hall) | banqsettlementsummary ✅ | ✅ EXISTS |
| 103 | TaxReportHall | Tax report (hall) | taxReport ✅ | ✅ EXISTS |
| 104 | TaxSummaryHall | Tax summary (hall) | taxReportData ✅ | ✅ EXISTS |
| 105 | TaxwiseDetailReportHall | Tax-wise detail (hall) | taxReport ✅ | ✅ EXISTS |
| 106 | banqoutstanding | Banquet outstanding | banqoutstanding 🆕 | 🆕 NEW (2026-08-19) |

---

## 4. INVENTORY / PURCHASE / STORE REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 107 | CashCreditPurch | Cash/credit purchase | purchaseamountreport ✅ | ✅ EXISTS |
| 108 | ExcessConsumption | Excess consumption | — | ❌ MISSING (P2) |
| 109 | IndentReg | Indent register | pendingindentitems ✅ | ✅ EXISTS |
| 110 | IssueReg | Issue register | stockregister ✅ | ✅ EXISTS |
| 111 | IssueRegister | Issue register | stockregister ✅ | ✅ EXISTS |
| 112 | KitchenStkRep | Kitchen stock report | kitchenclosingstock ✅ | ✅ EXISTS |
| 113 | KitchenStkSumm | Kitchen stock summary | kitchenclosingstock ✅ | ✅ EXISTS |
| 114 | ProductionReport | Production report | — | ❌ MISSING (P2) |
| 115 | PurchBill | Purchase bill | purchasebill ✅ | ✅ EXISTS |
| 116 | PurchOrder | Purchase order | purchaseorder ✅ | ✅ EXISTS |
| 117 | PurchaseLedger | Purchase ledger | purchaseledger ✅ | ✅ EXISTS |
| 118 | PurchaseReg | Purchase register | purchasebill ✅ | ✅ EXISTS |
| 119 | PurchaseSumm | Purchase summary | purchaseamountreport ✅ | ✅ EXISTS |
| 120 | RStockRegister | Restaurant stock register | stockregister ✅ | ✅ EXISTS |
| 121 | RStockSummary | Restaurant stock summary | stockregister ✅ | ✅ EXISTS |
| 122 | RestIssue | Restaurant issue | stockregister ✅ | ✅ EXISTS |
| 123 | StockINHand | Stock in hand | stockregister ✅ | ✅ EXISTS |
| 124 | StockRegStore | Stock register (store) | stockregister ✅ | ✅ EXISTS |
| 125 | StockRegister | Stock register | stockregister ✅ | ✅ EXISTS |
| 126 | StockSumm | Stock summary | stockregister ✅ | ✅ EXISTS |
| 127 | StockSummStore | Stock summary (store) | stockregister ✅ | ✅ EXISTS |
| 128 | StoreIssReg | Store issue register | stockregister ✅ | ✅ EXISTS |
| 129 | StoreIssueReport | Store issue report | stockregister ✅ | ✅ EXISTS |

---

## 5. ACCOUNTS / FINANCE REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 130 | AgingCr | Aging (creditors) | — | ❌ MISSING (P2 — needs bucket decision) |
| 131 | AgingDr | Aging (debtors) | — | ❌ MISSING (P2 — needs bucket decision) |
| 132 | AgingRepCr | Aging report (creditors) | — | ❌ MISSING (P2) |
| 133 | AgingRepDr | Aging report (debtors) | — | ❌ MISSING (P2) |
| 134 | BankBook | Bank book | cashbankbook ✅ | ✅ EXISTS |
| 135 | BankReg | Bank register | cashbankbook ✅ | ✅ EXISTS |
| 136 | CashBook | Cash book | cashbankbook ✅ | ✅ EXISTS |
| 137 | CreditReport | Credit report | creditReport ✅ | ✅ EXISTS |
| 138 | CustomerDetail | Customer detail | — | ❌ MISSING (P2) |
| 139 | DayBook | Day book | daybook ✅ | ✅ EXISTS |
| 140 | DetailedTrial | Detailed trial balance | detailedtrialledger ✅ | ✅ EXISTS |
| 141 | DUELIST | Due list | — | ❌ MISSING (P2 — needs bucket decision) |
| 142 | DueListCreditorOverLay | Due list creditor overlay | — | ❌ MISSING (P2) |
| 143 | GuestPayments | Guest payments | — | ❌ MISSING (P2) |
| 144 | JournalBook | Journal book | journalbook ✅ | ✅ EXISTS |
| 145 | JournalBookLog | Journal book log | journalbook ✅ | ✅ EXISTS |
| 146 | Led | General ledger | generalledger 🆕 | 🆕 NEW (2026-08-16) |
| 147 | LedCred | Ledger (credit) | generalledger ✅ | ✅ EXISTS (filter) |
| 148 | LedDeb | Ledger (debit) | generalledger ✅ | ✅ EXISTS (filter) |
| 149 | LedInt | Ledger (interim) | generalledger ✅ | ✅ EXISTS (filter) |
| 150 | LoanAdvSumm | Loan advance summary | — | ❌ MISSING (P2 — HR/payroll) |
| 151 | LoanLedg | Loan ledger | — | ❌ MISSING (P2 — HR/payroll) |
| 152 | LoanReg | Loan register | — | ❌ MISSING (P2 — HR/payroll) |
| 153 | MemLed | Member ledger | — | ❌ MISSING (P2 — membership) |
| 154 | PFStatement | PF statement | — | ❌ MISSING (P2 — HR/payroll) |
| 155 | PaySlip | Pay slip | — | ❌ MISSING (P2 — HR/payroll) |
| 156 | PayrollReg | Payroll register | — | ❌ MISSING (P2 — HR/payroll) |
| 157 | RozNamcha | Roz Namcha (daily cash) | daybook ✅ | ✅ EXISTS |
| 158 | Annexure | Annexure (tax) | — | ❌ MISSING (P2) |

---

## 6. GST / TAX / E-INVOICE REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 159 | FOMTaxDetail | FOM tax detail | fomtaxdetail ✅ | ✅ EXISTS |
| 160 | FOMTaxWiseChargeDetail | FOM tax-wise charge | fomtaxdetail ✅ | ✅ EXISTS |
| 161 | LuxuryTaxRegister | Luxury tax register | — | ⬛ OBSOLETE (pre-GST) |
| 162 | LuxuryTaxRegisterI | Luxury tax register I | — | ⬛ OBSOLETE (pre-GST) |
| 163 | MemTaxReport | Membership tax report | — | ❌ MISSING (P2 — membership) |
| 164 | TaxDetails | Tax details | fomtaxdetail ✅ | ✅ EXISTS |
| 165 | TaxInvoiceDetail | Tax invoice detail | taxreportinv ✅ | ✅ EXISTS |
| 166 | TaxRegister | GST tax register | gstconsolidatedregister 🆕 | 🆕 NEW (2026-08-19) |
| 167 | TaxReport | Tax report (room) | fomtaxdetail ✅ | ✅ EXISTS |
| 168 | TaxReportHall | Tax report (hall) | taxReport ✅ | ✅ EXISTS |
| 169 | TaxSumm | Tax summary | fomtaxdetail summary ✅ | ✅ EXISTS |
| 170 | TaxSummaryHall | Tax summary (hall) | taxReportData ✅ | ✅ EXISTS |
| 171 | TaxWiseSale | Tax-wise sale | gstconsolidatedregister 🆕 | 🆕 NEW (2026-08-19) |
| 172 | TaxwiseDetailReportHall | Tax-wise detail (hall) | taxReport ✅ | ✅ EXISTS |
| 173 | UPVATXXIV | UP VAT Form XXIV | — | ⬛ OBSOLETE (pre-GST) |
| 174 | VATRegister | VAT register | — | ⬛ OBSOLETE (pre-GST) |
| 175 | VATRegisterII | VAT register II | — | ⬛ OBSOLETE (pre-GST) |
| 176 | VATRegisterIII | VAT register III | — | ⬛ OBSOLETE (pre-GST) |

---

## 7. NIGHT AUDIT / DAILY REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 177 | DailyDiet | Daily diet/meal report | — | ❌ MISSING (P2) |
| 178 | DailyFuncSheet | Daily function sheet | dailyFunctionSheet ✅ | ✅ EXISTS |
| 179 | DailyReport | Daily report | dailyreport ✅ | ✅ EXISTS |
| 180 | DailyStoreIssRpt | Daily store issue report | stockregister ✅ | ✅ EXISTS |
| 181 | DailySumm | Daily summary | dailyreport ✅ | ✅ EXISTS |
| 182 | NightAuditReport | Night audit report | nightauditrecon 🆕 | 🆕 NEW (2026-08-19) |
| 183 | NightAuditReportI | Night audit report I | nightauditrecon 🆕 | 🆕 NEW (2026-08-19) |
| 184 | RoomNights | Room nights analysis | roomnights 🆕 | 🆕 NEW (2026-08-20) |

---

## 8. GUEST / MEMBERSHIP REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 185 | BirthMarrRep | Birth/marriage report | — | ❌ MISSING (P2 — membership) |
| 186 | ComplaintList | Complaint list | — | ❌ MISSING (P2) |
| 187 | FormC | Form C (foreign guest) | formcreport 🆕 | 🆕 NEW (2026-08-19) |
| 188 | FormCReport | Form C report | formcreport 🆕 | 🆕 NEW (2026-08-19) |
| 189 | FormIII | Form III | — | ❌ MISSING (P2) |
| 190 | GRC | GRC (guest registration card) | — | ❌ MISSING (P2) |
| 191 | GuestObservRep | Guest observation report | — | ❌ MISSING (P3) |
| 192 | MemBillMissingReport | Member bill missing report | — | ❌ MISSING (P2 — membership) |
| 193 | MemBirthAnnvDtls | Member birth/anniversary details | — | ❌ MISSING (P2 — membership) |
| 194 | MemMalingLabels | Member mailing labels | — | ❌ MISSING (P3 — membership) |
| 195 | MemSalesRegister | Member sales register | — | ❌ MISSING (P2 — membership) |
| 196 | MemVisitDetail | Member visit detail | — | ❌ MISSING (P2 — membership) |
| 197 | PlanMealTokens | Plan meal tokens | — | ❌ MISSING (P2 — meal plan) |
| 198 | PlanPackSchedule | Plan package schedule | — | ❌ MISSING (P2) |
| 199 | PlanPackService | Plan package service | — | ❌ MISSING (P2) |
| 200 | RegisteredGuestDetail | Registered guest detail | registeredguestdetail 🆕 | 🆕 NEW (2026-08-20) |
| 201 | RegistrationCard | Registration card | — | ❌ MISSING (P2 — printable) |

---

## 9. MISCELLANEOUS REPORTS

| # | Legacy Report | Description | Laravel Equivalent | Status |
|---|---|---|---|---|
| 202 | AttendanceRep | Attendance report | — | ❌ MISSING (P2 — HR) |
| 203 | BudgetAnalysis | Budget analysis | — | ❌ MISSING (P2) |
| 204 | BusinessAnalysis | Business analysis | — | ❌ MISSING (P2) |
| 205 | BussSourceOccupancyReport | Business source occupancy | — | ❌ MISSING (P2) |
| 206 | CashCardCollectSumm | Cash card collection summary | — | ❌ MISSING (P2 — cash card) |
| 207 | CashCardTransRep | Cash card transaction report | — | ❌ MISSING (P2 — cash card) |
| 208 | CompanyAnalysis | Company analysis | companyWiseSaleReport ✅ | ✅ EXISTS |
| 209 | CompanySumm | Company summary | companyWiseSaleReport ✅ | ✅ EXISTS |
| 210 | CostAnalysis | Cost analysis | — | ❌ MISSING (P3) |
| 211 | EpabxCallRep | EPABX call report | — | ❌ MISSING (P2 — telephone) |
| 212 | FBCostStatement | FB cost statement | — | ❌ MISSING (P3) |
| 213 | FacilityBillReg | Facility bill register | — | ❌ MISSING (P2) |
| 214 | FoodCost | Food cost | foodcost 🆕 | 🆕 NEW (2026-08-20) |
| 215 | HouseKeeping | House keeping | housekeepingstatusreport ✅ | ✅ EXISTS |
| 216 | LTFORMII | LT Form II | — | ⬛ OBSOLETE (legacy tax) |
| 217 | LTFORMIV | LT Form IV | — | ⬛ OBSOLETE (legacy tax) |
| 218 | MarketSegAnalysis | Market segment analysis | — | ❌ MISSING (P3) |
| 219 | MonthlyStatisticalReturn | Monthly statistical return | — | ❌ MISSING (P2) |
| 220 | OrderDetailReport | Order detail report | — | ❌ MISSING (P2) |
| 221 | PackageForecast | Package forecast | — | ❌ MISSING (P3) |
| 222 | PaymentDueLetterReport | Payment due letter | — | ❌ MISSING (P2 — printable) |
| 223 | RefReport | Ref report | — | ❌ MISSING (P3) |
| 224 | TravelAgentAnalysis | Travel agent analysis | — | ❌ MISSING (P2) |

---

## SUMMARY

| Status | Count | % of 224 |
|---|---|---|
| ✅ EXISTS | 98 | 43.8% |
| 🆕 NEW (added this session) | 42 | 19.2% |
| ❌ MISSING | 83 | 37.7% |
| ⚠️ PARTIAL | 0 | 0% |
| ⬛ OBSOLETE | 8 | 3.6% |
| **Effective MISSING** (excluding obsolete) | **83** | **37.7%** |

---

## PRIORITY CLASSIFICATION (Missing Reports)

### P1 — High Operational/Financial Value (14 reports)

| Report | Business Need | Complexity |
|---|---|---|
| **AMRMorningReport** | Morning report (occupancy + arrivals + departures + revenue) | Medium |
| **CheckedInGuestDtl** | Currently checked-in guest list with details | Low |
| **MovementList** | Daily arrivals + departures + transfers | Low |
| **ReservationStatus** / ReservStatusArrival / ReservStatusInHouse | Reservation status dashboard | Medium |
| **RoomRentAuditRpt** | Room rent audit (charges vs rate vs nights) | Medium |
| **RoomWiseRoomRevenueReport** | Room-wise revenue breakdown | Low |
| **PartyOutStanding** | Party outstanding (FO settlements) | Low |
| **SettleRep** | Settlement report (FO settlements audit) | Medium |
| **FormC** / FormCReport | Foreign guest compliance (mandatory in India) | Low |
| **RegisteredGuestDetail** | All registered guests with details | Low |

### P2 — Useful but Not Critical (68 reports)

Mostly specialized operational reports (discount registers, food cost, waiter-wise, table-wise, cash card, membership, HR/payroll, aging). These are lower priority because:
- The data is accessible via other reports
- Business may not actively use them
- Some need business decisions (aging buckets, etc.)

### P3 — Nice-to-have (24 reports)

ABC analysis, cost analysis, market segment, package forecast, referral, etc.

---

## RECOMMENDED IMPLEMENTATION ORDER (P1)

1. ~~**AMRMorningReport**~~ — ✅ **DONE 2026-08-19**
2. ~~**CheckedInGuestDetail**~~ — ✅ **DONE 2026-08-19**
3. ~~**RoomWiseRoomRevenueReport**~~ — ✅ **DONE 2026-08-19**
4. ~~**FormC / FormCReport**~~ — ✅ **DONE 2026-08-19**

All 7 P1 reports implemented. MovementList now implemented as dedicated report (2026-08-20). Remaining P1: PartyOutStanding (partially covered by outStandingreport).

---

## ALREADY ADDED IN THIS AI SESSION

| Report | Date | File |
|---|---|---|
| Night Audit Reconciliation (NightAuditReport/NightAuditReportI) | 2026-08-19 | nightauditrecon.blade.php |
| GST Consolidated Register (TaxWiseSale/TaxRegister) | 2026-08-19 | gstconsolidatedregister.blade.php |
| Banquet Outstanding | 2026-08-19 | banqoutstanding.blade.php |
| General Ledger (Led) | 2026-08-16 | generalledger.blade.php |
| Day Book (DayBook) | 2026-08-16 | daybook.blade.php |
| Cash/Bank Book (CashBook/BankBook) | 2026-08-16 | cashbankbook.blade.php |
| Journal Book (JournalBook) | 2026-08-17 | journalbook.blade.php |
| Advance Reconciliation (ResvAdvRecd) | 2026-08-16 | advreconreport.blade.php |
| Movement List (MovementList) | 2026-08-20 | movementlist.blade.php |
| Discount Register (DiscountReg/DiscountSumm/DiscountPartyWiseReg) | 2026-08-20 | discountregister.blade.php |
| Food Cost (FoodCost) | 2026-08-20 | foodcost.blade.php |
| Cover Analysis (CoverAnalysis) | 2026-08-20 | coveranalysis.blade.php |
| Waiter-Wise Sale (WaiterWiseSale) | 2026-08-20 | waitersale.blade.php |
| Cashier Settlement (CashierSettlement) | 2026-08-20 | cashiersettlement.blade.php |
| Guest Payments (GuestPayments) | 2026-08-20 | guestpayments.blade.php |
| Room Change History (RoomChangeHistory) | 2026-08-20 | roomchangehistory.blade.php |
| Guest Trial Balance (GuestTrialBalance) | 2026-08-20 | guesttrialbalance.blade.php |
| Room Nights Analysis (RoomNights) | 2026-08-20 | roomnights.blade.php |
