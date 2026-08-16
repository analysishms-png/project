# Analysis HMS — DATABASE MAP

> Model → table → legacy equivalent → business meaning. MySQL `db_analysishms`; 412 migrations.
> Legacy schema reference: `.ai/visahl.sql` (SQL Server dump, VishalDataNew2627) — **UTF-16 encoded**, grep with `iconv` or open in an editor that detects UTF-16.

---

## Core financial/reservation tables

| Model (app/Models/) | Table | PK | Legacy (visahl.sql) | Business meaning |
|---------------------|-------|----|---------------------|------------------|
| Booking | booking | ? | ✅ | Reservation header |
| GrpBookingDetails | grpbookingdetails | ? | ✅ | Reservation room/date details |
| RoomOcc | roomocc | ? | ✅ | Room occupancy (in-house) |
| Guestfolio | guestfolio | ? | ✅ | Guest folio (bill) |
| Paycharge | paycharge | ? | ✅ | Charges/payments on folio |
| PaychargeLog | paychargelog | ? | ✅ | Financial change audit log |
| PaychargeH | paychargeh | ? | ✅ | Charge history |
| Suntran | suntran | DocId+Sno | ✅ `[dbo].[Suntran]` DocId, Sno, Vtype, VNo, Vdate, PartyCode, SunCode, Amount, BaseAmount, RevCode, RestCode, U_Name, U_EntDt | Accounting posting lines |
| Ledger | ledger | ? | ✅ | Party/group ledger |
| Sale1 / Sale2 | sale1 / sale2 | ? | ✅ | POS bill header / details |
| Kot | kot | ? | ✅ | Kitchen order tickets |
| HallSale1 / HallSale2 | hallsale1 / hallsale2 | ? | ✅ | Banquet sales |
| HallBook | hallbook | ? | ✅ | Banquet/venue booking |
| Stock | stock | ? | ✅ | Store stock |
| Purch1 / Purch2 | purch1 / purch2 | ? | ✅ | Purchase bill header / details |
| Indent | indent / indent1 | ? | ✅ | Indent request |
| Porder | porder / porder1 | ? | ✅ | Purchase order |
| Gin | gin | ? | ✅ | Goods inward |
| Expsheet | expsheet | ? | ✅ | Expense sheet |
| VoucherPrefix | voucher_prefix | ? | ✅ | Voucher number prefix/range |
| Revmast | revmast | ? | ✅ | Revenue code master |
| SubGroup | subgroup | ? | ✅ | Sub-group (ledger grouping) |
| ACGroup | acgroup (model exists) | ? | ✅ | Account group |
| TaxStru | taxstru | ? | ✅ | Tax structure/slabs |

## Master/configuration tables

| Model | Table | Business meaning |
|-------|-------|------------------|
| Companyreg | company | Property/company registration |
| User | users | App users (property-scoped) |
| MenuHelp | menuhelp | User menu permissions |
| UserPermission | userpermission | Fine-grained permissions |
| EnviroGeneral / EnviroPos / EnviroFom / EnviroFinance / EnviroBanquet / EnviroInventory / EnviroPayroll / EnviroWhatsapp / EnviroEinvoice / EnviroChannel | enviro_* | Module parameters |
| PlanMast / PlanPackMast | plan1 / plandetails | Rate plans / packages |
| RoomCat / RoomMast | roomcat / roommast | Room categories / rooms |
| ItemMast / ItemCatMast / ItemGroupMast / ItemRate / Items | itemmast / itemcatmast / itemgrp / itemrate / items | POS items |
| Depart | depart | Departments |
| VenueMast / VenueOcc | venuemast / venueocc | Banquet venues |
| MemberCategory | membercategory | Membership categories |
| Employee | employee | HR employees |
| States / Cities / Countries | states / cities / countries | Address masters |
| BookingSource | bookingsource | Reservation sources |
| SupportTicket | supporttickets | Support tickets |
| UserUpdate | userupdate | Tool-change audit log |
| ChannelEnviro / channelpushes | channelpushes | Channel manager push log |
| whatsapp_logs | whatsapp_logs | SMS/WhatsApp send log |
| EInvoiceBill | e_invoice_bills | GST e-invoice records |

## Reconciliation watchlist (mission §16 — diagnostic queries only, no auto-fix)

- Orphan `paycharge` rows (no matching folio/booking)
- `paychargelog` vs `paycharge` mismatches (deleted/edited charges)
- `suntran` rows with missing `DocId` / `ContraDocID` / `FolioNo`
- Reservation ↔ check-in ↔ folio mismatches (advance trace)
- Room occupancy vs room status mismatches
- POS payment vs bill totals mismatches

> Do NOT modify production data automatically — generate diagnostic queries first (next task candidate).

## Testing note

`phpunit.xml` uses SQLite `:memory:` — tests exercise helpers/routes only, not the MySQL schema. Schema-level tests would need a MySQL test DB (future work).
