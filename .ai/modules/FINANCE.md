# Finance Module

## Overview

The Finance module handles ledger, accounting, GST, and night audit operations.

---

## Components

### Controllers
- `FinanceController` - Finance operations
- `FinancialPush` - Financial push
- `CheckRegister` - Check register

### Models
- `Ledger` - Main ledger
- `LedgerLog` - Ledger log
- `Suntran` - Transactions
- `SuntranH` - Transaction header
- `Paycharge` - Payments/charges
- `TaxStructure` - Tax structure
- `SubGroup` - Account groups

### Services
- `AccountPosting` - Financial posting
- `LedgerLogService` - Ledger logging

---

## Workflows

### Revenue Posting Flow
1. Identify revenue type
2. Calculate amount
3. Post to ledger
4. Update accounts

### Night Audit Flow
1. Post room charges
2. Post taxes
3. Reconcile accounts
4. Generate daily report
5. Close day

---

## Database Tables

- `ledger` - Main ledger
- `ledgerlog` - Ledger log
- `suntran` - Transactions
- `suntranh` - Transaction header
- `paycharge` - Payments/charges
- `taxstru` - Tax structure
- `subgroup` - Account groups

---

## Routes

| Method | URI | Controller | Name |
|--------|-----|------------|------|
| GET | `/finance` | FinanceController@index | finance.index |
| GET | `/ledger` | FinanceController@ledger | finance.ledger |
| GET | `/nightaudit` | NightAuditlogController@index | nightaudit.index |

---

## Last Updated
- Date: August 7, 2026
- Version: 1.0
