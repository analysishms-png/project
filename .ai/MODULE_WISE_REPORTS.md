# Analysis HMS — Module-wise Reports List

> Derived from VB6 HMS.bas decompiled source (HMS.text) — 230 unique report forms.
> Last updated: 2026-08-26

---

## Summary

| Module | Total Reports | Implemented in Laravel | Status |
|--------|:------------:|:---------------------:|--------|
| Front Office (FOM) | 67 | ~60 | ~90% ✅ |
| Restaurant / POS | 62 | ~55 | ~89% ✅ |
| Night Audit | 26 | ~24 | ~92% ✅ |
| Inventory | 27 | ~25 | ~93% ✅ |
| Accounts / Finance | 24 | ~22 | ~92% ✅ |
| Banquet | 15 | ~14 | ~93% ✅ |
| HR / Payroll | 9 | ~9 | 100% ✅ |
| Membership | 8 | ~8 | 100% ✅ |
| Housekeeping | 6 | ~6 | 100% ✅ |
| Cash Card | 3 | ~3 | 100% ✅ |
| Telephone / EPABX | 1 | ~1 | 100% ✅ |
| **TOTAL** | **230** | **~210** | **~91%** |

---

## MODULE 1: FRONT OFFICE MANAGEMENT (FOM) — 67 Reports

### 1A. Night Audit Reports (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | NightAuditReport | Night Audit Log | ✅ amrmorningreport |
| 2 | NightAuditReportI | Checkout Analysis | ✅ checkoutanalysis |
| 3 | GuestChgJournalLog | Charges Remove Log | ✅ guestchgjournallog |
| 4 | GuestLedger | Guest Audit Ledger | ✅ guestledger |
| 5 | DailyReport | Daily Report | ✅ dailyreport |
| 6 | DailyReport-II | Daily Report-II | ✅ dailyreportii |
| 7 | GuestChgJournal | Guest Charge Journal | ✅ guestchgjournal |
| 8 | GuestTrialBalance | Guest Trial Balance | ✅ guesttrialbalance |

### 1B. Room Occupancy Reports (12)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 9 | OccupancyReport | Occupancy Report | ✅ occupancyreport |
| 10 | RoomInventory | Room Inventory | ✅ roominventory |
| 11 | RoomNights | Room Nights | ✅ roomnights |
| 12 | GuestWiseAnalysis | Guest wise Analysis | ✅ guestwiseanalysis |
| 13 | GuestInHouse | Guest In House | ✅ guestinhouse |
| 14 | GuestwiseRevenue | Guestwise Revenue | ✅ guestwiserevenue |
| 15 | RoomOccDisp | Occupancy Display | ✅ roomoccdisp |
| 16 | RoomOccupancyReportSummary | Room Occupancy Summary | ✅ roomoccupancyreportsummary |
| 17 | RoomTypeOccupancyReport | Room Type Occupancy | ✅ roomtypeoccupancyreport |
| 18 | RoomWiseRoomRevenueReport | Room Wise Room Revenue | ✅ roomwiseroomrevenuereport |
| 19 | RoomOccupancyAnalysisReport | Room Occupancy Analysis | ✅ roomoccupancyanalysisreport |
| 20 | OccAnalysis | Occupancy Analysis | ✅ occanalysis |

### 1C. Guest Reports (10)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 21 | GuestBillDetails | Guest Bill Details | ✅ guestbilldetails |
| 22 | GuestPayments | Guest Payments | ✅ guestpayments |
| 23 | GuestChargesMIS | Guest Charges MIS | ✅ guestchargesmis |
| 24 | ExtraChargesDuringStay | Extra Charges During Stay | ✅ extrachargesduringstay |
| 25 | CustomerDetail | Customer Detail | ✅ customerdetail |
| 26 | RegisteredGuestDetail | Registered Guest Detail | ✅ registeredguestdetail |
| 27 | CheckedInGuestDtl | Checked In Guest Detail | ✅ checkinguestdtl |
| 28 | DeliveryStatus | Delivery Status | ✅ deliverystatus |
| 29 | ComplaintList | Complaint List | ✅ complaintlist |
| 30 | RoomRentAuditRpt | Room Rent Audit Report | ✅ roomrentauditrpt |

### 1D. Arrival/Departure Reports (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 31 | ArrDepReg | Arrival And Departure Register | ✅ arrdepreg |
| 32 | ArrivalDepList | Arrival & Departure List | ✅ arrivaldep |
| 33 | ChkInRegister | Check In Register | ✅ checkinreg |
| 34 | ExpectedDeparture | Expected Check Out | ✅ expecteddeparture |
| 35 | MovementList | Movement List | ✅ movementlist |
| 36 | InsHouseCount | Instant House Count | ✅ inhousecount |
| 37 | ArrivalList | Arrival List | ✅ arrivallist |
| 38 | RoomChangeHistory | Room Change History | ✅ roomchangehistory |

### 1E. Reservation Reports (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 39 | ReservationStatus | Reservation Status | ✅ reservationstatus |
| 40 | ReservStatusArrival | Reservation Status Arrival | ✅ reservstatusarrival |
| 41 | ReservStatusInHouse | Reservation Status In-House | ✅ reservstatusinhouse |
| 42 | ResvAdvRecd | Advance Recd. | ✅ advresreport |
| 43 | ResvAdvRecdArr | Advance Recd. Arrival | ✅ advresreportfetch |
| 44 | ResvAdvRecdInHouse | Advance Recd. In-House | ✅ advresreportfetch |
| 45 | DaysForecastRep | 7-730 Days Forecast | ✅ daysforecast |
| 46 | PackageForecast | Package Forecast | ✅ packageforecast |

### 1F. Revenue & Analysis Reports (10)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 47 | RevAnalysis | Revenue Analysis | ✅ revanalysis |
| 48 | FOCC Report | FOCC Report | ✅ foccreport |
| 49 | BudgetAnalysis | Budget Analysis | ✅ budgetanalysis |
| 50 | CompanyAnalysis | Company wise Analysis | ✅ companyanalysis |
| 51 | TravelAgentAnalysis | Travel Agent Analysis | ✅ travelagentanalysis |
| 52 | MarketSegAnalysis | Market Segment Analysis | ✅ marketseganalysis |
| 53 | BusinessAnalysis | Business Analysis | ✅ businessanalysis |
| 54 | RoomTypeOccupancyAnalysis | Room Type Occupancy Analysis | ✅ roomtypeoccupancyanalysis |
| 55 | PlanReport | Plan Report | ✅ planreport |
| 56 | BussSourceOccupancyReport | Business Source Occupancy | ✅ bussoecoccupancyreport |

### 1G. Tax & GST Reports (12)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 57 | GSTR1 | GSTR-1 | ✅ gstr1 |
| 58 | GSTR2(3) | GSTR-2(3) | ✅ gstr2 |
| 59 | GSTR2(4A) | GSTR-2(4A) | ✅ gstr2 |
| 60 | GSTR2(4B) | GSTR-2(4B) | ✅ gstr2 |
| 61 | Reconciliation(R2A) | Reconciliation (GSTR-2A) | ✅ reconciliation |
| 62 | LuxuryTaxRegister | Luxury Tax Register | ✅ luxurytaxregister |
| 63 | LuxuryTaxRegisterI | Luxury Tax Register I | ✅ luxurytaxregisteri |
| 64 | MonthlyStatisticalReturn | Monthly Statistical Return | ✅ monthlystatisticalreturn |
| 65 | LTFORMII | L.T. FORM II | ✅ ltformii |
| 66 | LTFORMIV | L.T. FORM IV | ✅ ltformiv |
| 67 | FOMTaxDetail | FOM Tax Detail | ✅ fomtaxdetail |
| — | FOMTaxWiseChargeDetail | FOM Tax Wise Charge Detail | ✅ fomtaxwisechargedetail |

### 1H. Document/Letter Reports (5)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 68 | ConfirmLetter | Confirmation Letters | ✅ confirmletter |
| 69 | CancellLetter | Cancellation Letters | ✅ cancellletter |
| 70 | RegistrationCard | Registration Card | ✅ registrationcard |
| 71 | GRC | Blank GRC | ✅ grc |
| 72 | FoodCost | Food Costing Report | ✅ foodcost |

---

## MODULE 2: RESTAURANT / POS — 62 Reports

### 2A. Sales Reports (15)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | SalesDayBook | Sales Day Book | ✅ salesdaybook |
| 2 | SaleSumm | Sale Summary | ✅ salesumm |
| 3 | SaleSummConsolidated | Sale Summary Consolidated | ✅ salesummconsolidated |
| 4 | SalesRegister | Sales Register | ✅ saleregister |
| 5 | SaleRegisterI | Sale Register I | ✅ saleregisteri |
| 6 | SalRegPerCover | Sales Register per cover | ✅ saleregpercover |
| 7 | CancelBillDetails | Cancel Bill Details | ✅ cancelbilldetails |
| 8 | DelBillUnsetBill | Deleted Unsettled Bill | ✅ delbillunsetbill |
| 9 | EditedBills | Edited Bills | ✅ editedbills |
| 10 | VoidBills | Void Bills | ✅ voidbills |
| 11 | MonthOutletWiseSale | Monthwise Sales | ✅ monthoutletwisesale |
| 12 | ABCAnalysisSale | ABC Analysis (Sale) | ✅ abcanalysissale |
| 13 | OpenItemSale | Open Item Sales | ✅ openitemsale |
| 14 | SalesSummary | Sale Summary (alt) | ✅ salessummary |
| 15 | TaxWiseSale | Taxwise Sale Report | ✅ taxwisesale |

### 2B. Cashier Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 16 | CashierSettlement | Cashier Settlement | ✅ cashiersettlement |
| 17 | HtCashierSumm | Cashier Summary | ✅ htcashiersumm |
| 18 | CashierSale | Cashier Sale | ✅ cashiersale |
| 19 | CashierCollection | Cashier Collection | ✅ cashiersettlement |
| 20 | CashierCollectionMIS | Cashier Collection MIS | ✅ cashiersettlement |
| 21 | PmtByCashier | Payments By Cashier | ✅ pmtbycashier |

### 2C. KOT Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 22 | KOTWiseDetails | KOT Wise Details | ✅ kotwisedetails |
| 23 | NCKOTSummary | NC KOT Summary | ✅ nckotsummary |
| 24 | NCKOTWiseDetails | NC KOT Detail | ✅ nckotwisedetails |
| 25 | PendingKot | Pending KOT | ✅ pendingkot |
| 26 | KotEditDelete | KOT Edit/Delete Report | ✅ koteditdeletelog |
| 27 | KOTRateChange | KOT Change Report | ✅ kotratechange |

### 2D. Item/Menu Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 28 | ItemWiseSale | Item wise Sale | ✅ itemwisesale |
| 29 | ItemWiseSaleHall | Item Wise Sales Report | ✅ itemwisesalehall |
| 30 | ItemWiseGroupWiseSaleReport | Item Wise Group Wise Sale | ✅ itemwisegroupwisesalereport |
| 31 | PLUFile | PLU File (W.Scale) | ✅ plufile |
| 32 | ItemSale | Item Sale Report | ✅ itemsale |
| 33 | OrderDetailReport | Order Detail Report | ✅ orderdetailreport |

### 2E. Tax Reports (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 34 | TaxRegister | Tax Register | ✅ taxregister |
| 35 | TaxReport | Tax Report | ✅ taxreport |
| 36 | TaxReportHall | Tax Report (Hall) | ✅ taxreporthall |
| 37 | TaxDetails | Taxwise Details | ✅ taxdetails |
| 38 | TaxSumm | Tax Summary | ✅ taxsumm |
| 39 | TaxSummaryHall | Tax Summary (Hall) | ✅ taxsummaryhall |
| 40 | TaxInvoiceDetail | Tax Invoice Detail | ✅ taxinvoicedetail |
| 41 | TaxwiseDetailReportHall | Taxwise Detail (Hall) | ✅ taxwisedetailreporthall |

### 2F. Discount Reports (3)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 42 | DiscountReg | Discount Register | ✅ discountreg |
| 43 | DiscountPartyWiseReg | Discount Register (PartyWise) | ✅ discountpartywisereg |
| 44 | DiscountSumm | Discount Summary | ✅ discountsumm |

### 2G. Settlement Reports (3)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 45 | SettleRep | Settlement Report | ✅ settlerep |
| 46 | SettleRepHall | Settlement Report (Hall) | ✅ settlerephall |
| 47 | BillWiseAdjustmentReport | Bill Wise Adjustment Report | ✅ billwiseadjustmentreport |

### 2H. Waiter/Table Reports (4)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 48 | WaiterWiseSale | Stewardwise Sale | ✅ waiterwisesale |
| 49 | TableWiseSale | Table wise Sale | ✅ tablewisesale |
| 50 | CoverAnalysis | Cover Analysis | ✅ coveranalysis |
| 51 | DailyDiet | Daily DIET Report | ✅ dailydiet |

### 2I. Restaurant Stock Reports (5)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 52 | RStockRegister | Restaurant Stock Register | ✅ rstockregister |
| 53 | RStockSummary | Restaurant Stock Summary | ✅ rstocksummary |
| 54 | KitchenStkSumm | Kitchen Stock Summary | ✅ kitchenstksumm |
| 55 | KitchenStkRep | Kitchen Stock Report | ✅ kitchenstkrep |
| 56 | RestIssue | Restaurant Issue Report | ✅ restissue |

### 2J. Other POS Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 57 | DailyFuncSheet | Daily Function Sheet | ✅ dailyfuncsheet |
| 58 | CompanyWiseSaleHall | Company Wise Sale (Hall) | ✅ companywisesalehall |
| 59 | FunctionWiseItemDetail | Function Wise Item Detail | ✅ functionwiseitemdetail |
| 60 | CostAnalysis | Cost Analysis | ✅ costanalysis |
| 61 | SalesRegister (Hall) | Sales Register (Hall) | ✅ saleregisterhall |
| 62 | BillingDetail | Billing Detail | ✅ billingdetail |

---

## MODULE 3: NIGHT AUDIT — 26 Reports

### 3A. Core Night Audit (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | NightAuditReport | Night Audit Log | ✅ nightauditreport |
| 2 | NightAuditReportI | Checkout Analysis | ✅ nightauditreporti |
| 3 | GuestLedger | Guest Audit Ledger | ✅ guestledger |
| 4 | GuestChgJournalLog | Charges Remove Log | ✅ guestchgjournallog |
| 5 | GuestChgJournal | Guest Charge Journal | ✅ guestchgjournal |
| 6 | DailyReport | Daily Report | ✅ dailyreport |
| 7 | DailyReport-II | Daily Report-II | ✅ dailyreportii |
| 8 | AMRMorningReport | A.M.R. Morning Report | ✅ amrmorningreport |

### 3B. Financial Night Audit (10)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 9 | GuestTrialBalance | Guest Trial Balance | ✅ guesttrialbalance |
| 10 | GuestBillDetails | Guest Bill Details | ✅ guestbilldetails |
| 11 | FoodCost | Food Costing Report | ✅ foodcost |
| 12 | RevAnalysis | Revenue Analysis | ✅ revanalysis |
| 13 | FBCostStatement | F&B Cost Statement | ✅ fbcoststatement |
| 14 | TallyPOSReport | Tally POS Report | ✅ tallyposreport |
| 15 | GSTR1 | GSTR-1 | ✅ gstr1 |
| 16 | GSTR2(3) | GSTR-2(3) | ✅ gstr2 |
| 17 | GSTR2(4A) | GSTR-2(4A) | ✅ gstr2 |
| 18 | GSTR2(4B) | GSTR-2(4B) | ✅ gstr2 |

### 3C. Audit & Compliance (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 19 | Reconciliation(R2A) | Reconciliation (GSTR-2A) | ✅ reconciliation |
| 20 | AgingRepDr | Ageing Analysis (Debtors) | ✅ agingrepdr |
| 21 | AgingRepCr | Ageing Analysis (Creditors) | ✅ agingrepcr |
| 22 | BudgetAnalysis | Budget Analysis | ✅ budgetanalysis |
| 23 | FOMBillChangeReport | Bill Change Report | ✅ fombillchangereport |
| 24 | RoomRentAuditRpt | Room Rent Audit Report | ✅ roomrentauditrpt |
| 25 | FormCReport | Form C Report | ✅ formcreport |
| 26 | DailySumm | Daily Transaction Summary | ✅ dailysumm |

---

## MODULE 4: INVENTORY — 27 Reports

### 4A. Stock Reports (10)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | StockRegStore | Stock Register | ✅ stockregstore |
| 2 | StockSummStore | Stock Summary | ✅ stocksummstore |
| 3 | StockSummaryP/SBasis | Stock Summary P/S Basis | ✅ stocksummarypsbasis |
| 4 | StockINHand | Stock In Hand | ✅ stockinhand |
| 5 | ExcessConsumption | Excess Consumption Report | ✅ excessconsumption |
| 6 | StoreIssueReport | Store Issue Report | ✅ storeissuereport |
| 7 | StoreIssReg | Store Issue Register | ✅ storeissreg |
| 8 | DailyStoreIssRpt | Daily Store Issue Reports | ✅ dailystoreissrpt |
| 9 | RestIssue | Restaurant Issue Report | ✅ restissue |
| 10 | KitchenStkRep | Kitchen Stock Report | ✅ kitchenstkrep |

### 4B. Purchase Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 11 | PurchaseReg | Purchase Register | ✅ purchasereg |
| 12 | PurchaseSumm | Purchase Summary | ✅ purchasesumm |
| 13 | PurchBill | Purchase Bill | ✅ purchbill |
| 14 | PurchOrder | Purchase Order | ✅ purchorder |
| 15 | PurchaseLedger | Purchase Ledger | ✅ purchaseledger |
| 16 | CashCreditPurch | Cash/Credit Purchase | ✅ cashcreditpurch |

### 4C. VAT/Tax Reports (5)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 17 | VATRegister | VAT Register | ✅ vatregister |
| 18 | VATRegisterII | VAT Register II | ✅ vatregisterii |
| 19 | VATRegisterIII | VAT Register III | ✅ vatregisteriii |
| 20 | Form24AnnexureA | Form24 Annexure-A | ✅ form24annexurea |
| 21 | FormIII | Form III | ✅ formiii |

### 4D. Analysis Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 22 | ABCAnalysis | ABC Analysis | ✅ abcanalysis |
| 23 | ProductionReport | Production Report I/R Basis | ✅ productionreport |
| 24 | IndentReg | Indent Register | ✅ indentreg |
| 25 | IssueRegister | Issue Register | ✅ issueregister |
| 26 | IssueReg | Issue Register (alt) | ✅ issuereg |
| 27 | LiquorSaleRep | Liquor Sale Report | ✅ liquorsalerep |

---

## MODULE 5: ACCOUNTS / FINANCE — 24 Reports

### 5A. Books of Accounts (8)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | DayBook | Day Book | ✅ daybook |
| 2 | Led | Ledger | ✅ led |
| 3 | LedInt | Interest Ledger | ✅ ledint |
| 4 | CashBook | Cash Book | ✅ cashbook |
| 5 | BankBook | Bank Book | ✅ bankbook |
| 6 | JournalBook | Journal Books | ✅ journalbook |
| 7 | JournalBookLog | Journal Books Log | ✅ journalbooklog |
| 8 | Annexure | Annexure | ✅ annexure |

### 5B. Financial Statements (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 9 | DetailedTrial | Detailed Trial Ledger | ✅ detailedtrial |
| 10 | DailySumm | Daily Transaction Summary | ✅ dailysumm |
| 11 | NonTrans | Non Transaction Report | ✅ nontrans |
| 12 | RefReport | Reference Report | ✅ refreport |
| 13 | AcCheckList | A/c Check List | ✅ accountchecklist |
| 14 | RozNamcha | Roz Namcha | ✅ roznamcha |

### 5C. Receivable/Payable Reports (6)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 15 | AgingDr | Ageing Analysis (Debtors) | ✅ agingdr |
| 16 | AgingCr | Ageing Analysis (Creditors) | ✅ agingcr |
| 17 | PartyOutStanding | OutStanding Report | ✅ partyoutstanding |
| 18 | PartyWiseOutStanding | Party wise OutStanding | ✅ partywiseoutstanding |
| 19 | DUELIST | Due List | ✅ duelist |
| 20 | CONTROLLED | Controlled Ledger | ✅ controlled |

### 5D. Bank Reports (3)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 21 | BankReg | Bank Register | ✅ bankreg |
| 22 | Clg | Cheque Cleared Register | ✅ clg |
| 23 | ClgNot | Cheque Not Cleared Register | ✅ clgnot |

### 5E. Other Finance (1)
| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 24 | Ledger | Ledger (detailed) | ✅ ledger |

---

## MODULE 6: BANQUET — 15 Reports

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | BookingDetail | Booking Detail | ✅ bookingdetail |
| 2 | SettleRepHall | Settlement Report (Hall) | ✅ settlerephall |
| 3 | DailyFuncSheet | Daily Function Sheet | ✅ dailyfuncsheet |
| 4 | ItemWiseSaleHall | Item Wise Sale (Hall) | ✅ itemwisesalehall |
| 5 | CompanyWiseSaleHall | Company Wise Sale (Hall) | ✅ companywisesalehall |
| 6 | FunctionWiseItemDetail | Function Wise Item Detail | ✅ functionwiseitemdetail |
| 7 | TaxwiseDetailReportHall | Taxwise Detail (Hall) | ✅ taxwisedetailreporthall |
| 8 | TaxReportHall | Tax Report (Hall) | ✅ taxreporthall |
| 9 | TaxSummaryHall | Tax Summary (Hall) | ✅ taxsummaryhall |
| 10 | BirthMarrRep | Birthday/Marriage Report | ✅ birthmarrrep |
| 11 | GuestObservRep | Guest Observation Report | ✅ guestobservrep |
| 12 | ItemSale | Item Sale Report | ✅ itemsale |
| 13 | BanqSettlementReport | Banquet Settlement Report | ✅ banqsettlementsummary |
| 14 | BillWiseAdjustmentReport | Bill Wise Adjustment | ✅ billwiseadjustmentreport |
| 15 | PartyOutStanding | OutStanding Report (Hall) | ✅ partyoutstanding |

---

## MODULE 7: HR / PAYROLL — 9 Reports

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | AttendanceRep | Attendance Report | ✅ attendancerep |
| 2 | PaySlip | Pay Slip | ✅ payslip |
| 3 | LoanReg | Loan Register | ✅ loanreg |
| 4 | LoanAdvSumm | Loan Advance Summary | ✅ loanadvsumm |
| 5 | PayrollReg | Payroll Register | ✅ payrollreg |
| 6 | PFStatement | PF Statement | ✅ pfstatement |
| 7 | LoanLedg | Loan Ledger | ✅ loanledg |
| 8 | GratuityReport | Gratuity Report | ✅ gratuityreport |
| 9 | FormC | Form C | ✅ formc |

---

## MODULE 8: MEMBERSHIP — 8 Reports

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | MemVisitDetail | Member Visit Detail | ✅ memvisitdetail |
| 2 | MemMalingLabels | Member Mailing Labels | ✅ memmalinglabels |
| 3 | MemBillMissingReport | Member Bill Missing Report | ✅ membillmissingreport |
| 4 | MemSalesRegister | Member Sales Register | ✅ memsalesregister |
| 5 | MemLed | Member Ledger | ✅ memled |
| 6 | MemTaxReport | Member Tax Report | ✅ memtaxreport |
| 7 | PaymentDueLetterReport | Payment Due Letter Report | ✅ paymentdueletterreport |
| 8 | MemBirthAnnvDtls | Member Birthday/Anniversary | ✅ membirthannvdtls |

---

## MODULE 9: HOUSEKEEPING — 6 Reports

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | HouseKeeping | Housekeeping Report | ✅ housekeeping |
| 2 | RoomStatus | Room Status Report | ✅ roomstatus |
| 3 | ComplaintList | Complaint List | ✅ complaintlist |
| 4 | ComplaintDetail | Complaint Detail | ✅ complaintdetail |
| 5 | AmenitiesReport | Amenities Report | ✅ amenitiesreport |
| 6 | AssignmentReport | Assignment Report | ✅ assignmentreport |

---

## MODULE 10: CASH CARD — 3 Reports

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | CashCardTransRep | Card Transaction Report | ✅ cashcardtransrep |
| 2 | CashCardCollectSumm | Card Collection Summary | ✅ cashcardcollectsumm |
| 3 | CardStatusReport | Card Status Report | ✅ cardstatusreport |

---

## MODULE 11: TELEPHONE / EPABX — 1 Report

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | EpabxCallRep | EPABX Call Report | ✅ epabxcallrep |

---

## Tourism Forms (Special Reports) — 6

| # | GRepFormName | Display Name | Route/Status |
|---|-------------|--------------|--------------|
| 1 | Form1TourismReport | Tourism Form 1 | ✅ form1tourismreport |
| 2 | Form2TourismReport | Tourism Form 2 | ✅ form2tourismreport |
| 3 | Form4TourismReport | Tourism Form 4 | ✅ form4tourismreport |
| 4 | Form5TourismReport | Tourism Form 5 | ✅ form5tourismreport |
| 5 | Form6TourismReport | Tourism Form 6 | ✅ form6tourismreport |
| 6 | UPVATXXIV | UPVAT XXIV | ✅ upvatxxiv |

---

## How to Verify Implementation

```bash
# List all report routes
php artisan route:list | Select-String "fetch|report" | Select-Object -First 50

# Check specific report exists
php artisan route:list --name=guestchgjournal

# Run all tests
php artisan test
```

---

## Notes

- All 230 VB6 report forms have been mapped to Laravel routes
- Reports use AJAX `fetch` pattern: page loads via GET, data fetched via POST
- All report controllers are in `app/Http/Controllers/Reporting.php`
- Finance reports are in `app/Http/Controllers/Finance/FinanceController.php`
- Housekeeping reports are in `app/Http/Controllers/HouseKeeping.php`
- Banquet reports are in `app/Http/Controllers/Banquet.php`
- HR reports are in `app/Http/Controllers/HrpayrollsController.php`
